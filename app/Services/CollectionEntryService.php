<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\ChequeStatus;
use App\Enums\PaymentMethod;
use App\Models\CollectionEntry;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\CollectionEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CollectionEntryService extends BaseCrudService
{
    public function __construct(
        private readonly CollectionEntryRepositoryInterface $collectionEntries,
        private readonly CollectionOtpService $otps,
    ) {
        parent::__construct($collectionEntries);
    }

    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, territory_id?: string, payment_method?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->collectionEntries->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, territory_id?: string, payment_method?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function total(array $filters): float
    {
        return $this->collectionEntries->sumWithFilters($filters);
    }

    /**
     * When `otp_id`/`otp_code` are both present, the collection is only
     * created after that OTP verifies against this exact dealer/amount/
     * payment_method — otherwise those two keys are simply absent and
     * every existing (non-OTP) call site keeps working unchanged.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $chequeImage = null): Model
    {
        $this->validateAmount((int) $data['dealer_id'], (float) $data['amount']);

        $otpId = $data['otp_id'] ?? null;
        $otpCode = $data['otp_code'] ?? null;
        unset($data['otp_id'], $data['otp_code']);

        if ($otpId && $otpCode) {
            // Scoped to whoever is actually authenticated (and so actually
            // stood in front of the dealer when the OTP was sent) — not
            // necessarily $data['user_id'], which on the web admin form is
            // just an attribution dropdown an operator can set to any
            // Sales Executive.
            $this->otps->verify(
                (int) $otpId,
                auth()->user(),
                (string) $otpCode,
                (int) $data['dealer_id'],
                (float) $data['amount'],
                PaymentMethod::from($data['payment_method']),
            );
            $data['otp_verified_at'] = now();
        }

        if ($chequeImage) {
            $data['cheque_image'] = $chequeImage->store('collection-entries', 'public');
        }

        if (($data['payment_method'] ?? null) === PaymentMethod::Cheque->value) {
            $data['cheque_status'] = ChequeStatus::Collected->value;
        }

        $data['status'] ??= ApprovalStatus::Pending->value;

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data, ?UploadedFile $chequeImage = null): Model
    {
        $this->validateAmount((int) $data['dealer_id'], (float) $data['amount'], excludeCollectionId: $model->id);

        if ($chequeImage) {
            if ($model->cheque_image) {
                Storage::disk('public')->delete($model->cheque_image);
            }
            $data['cheque_image'] = $chequeImage->store('collection-entries', 'public');
        }

        if (($data['payment_method'] ?? null) === PaymentMethod::Cheque->value) {
            // Switching an existing (non-cheque) entry to cheque starts its
            // lifecycle fresh; an entry that was already cheque keeps
            // whatever status it had reached.
            $data['cheque_status'] ??= $model->cheque_status?->value ?? ChequeStatus::Collected->value;
        } else {
            // Switching away from cheque clears the lifecycle entirely —
            // it's not meaningful for any other payment method.
            $data['cheque_status'] = null;
        }

        return parent::update($model, $data);
    }

    /**
     * Self-service field entry: the sale date defaults to today when the
     * rep doesn't backdate it.
     */
    public function recordCollection(
        User $user,
        int $dealerId,
        float $amount,
        PaymentMethod $paymentMethod,
        ?string $referenceNo,
        ?string $remarks,
        ?string $collectionDate,
        ?UploadedFile $chequeImage = null,
        ?int $otpId = null,
        ?string $otpCode = null,
    ): CollectionEntry {
        /** @var CollectionEntry $entry */
        $entry = $this->create([
            'user_id' => $user->id,
            'dealer_id' => $dealerId,
            'collection_date' => $collectionDate ?? Carbon::today()->toDateString(),
            'amount' => $amount,
            'payment_method' => $paymentMethod->value,
            'reference_no' => $referenceNo,
            'remarks' => $remarks,
            'otp_id' => $otpId,
            'otp_code' => $otpCode,
        ], $chequeImage);

        return $entry;
    }

    /**
     * Advances a cheque's status one step along its lifecycle
     * (Collected -> Submitted -> Deposited -> Cleared|Bounced). Rejects any
     * jump that isn't one of the current status's allowed next steps —
     * enforced here, not just in the UI, so the transition can't be forced
     * via a raw request either.
     */
    public function updateChequeStatus(CollectionEntry $entry, ChequeStatus $newStatus): CollectionEntry
    {
        if ($entry->payment_method !== PaymentMethod::Cheque || $entry->cheque_status === null) {
            throw ValidationException::withMessages([
                'cheque_status' => 'This collection is not a cheque payment.',
            ]);
        }

        if (! in_array($newStatus, $entry->cheque_status->nextOptions(), true)) {
            throw ValidationException::withMessages([
                'cheque_status' => "Cannot move from {$entry->cheque_status->label()} to {$newStatus->label()}.",
            ]);
        }

        $entry->update(['cheque_status' => $newStatus->value]);

        return $entry->fresh();
    }

    /**
     * Forward-only, mirroring updateChequeStatus()/CashHandoverService: only
     * a Pending collection can be approved or rejected, and both are
     * terminal — a rejected collection is corrected and resubmitted, not
     * reopened here.
     */
    public function approve(CollectionEntry $entry, int $approverId): CollectionEntry
    {
        $this->assertPendingApproval($entry);

        $entry->update(['status' => ApprovalStatus::Approved->value, 'approved_by' => $approverId, 'approved_at' => now()]);

        return $entry->fresh();
    }

    public function reject(CollectionEntry $entry, int $approverId): CollectionEntry
    {
        $this->assertPendingApproval($entry);

        $entry->update(['status' => ApprovalStatus::Rejected->value, 'approved_by' => $approverId, 'approved_at' => now()]);

        return $entry->fresh();
    }

    private function assertPendingApproval(CollectionEntry $entry): void
    {
        if ($entry->status !== ApprovalStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This collection has already been '.$entry->status->label().' and cannot be changed.',
            ]);
        }
    }

    /**
     * Total sold to the dealer minus total already collected from them —
     * excluding a given collection's own amount, so editing an existing
     * entry doesn't double-count it against itself.
     */
    public function outstandingBalance(int $dealerId, ?int $excludeCollectionId = null): float
    {
        $ordersTotal = (float) Order::where('dealer_id', $dealerId)->sum('total_amount');

        $collectedTotal = (float) CollectionEntry::where('dealer_id', $dealerId)
            ->when($excludeCollectionId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->sum('amount');

        return $ordersTotal - $collectedTotal;
    }

    /**
     * A collection may exceed the outstanding balance by up to the
     * configured tolerance — field collections sometimes round up — but
     * anything further is rejected outright rather than silently accepted,
     * since it almost always means the wrong dealer or amount was entered.
     */
    private function validateAmount(int $dealerId, float $amount, ?int $excludeCollectionId = null): void
    {
        $balance = $this->outstandingBalance($dealerId, $excludeCollectionId);
        $tolerancePercent = (float) config('sfa.collections.overpayment_tolerance_percent');
        $maxAllowed = round(max($balance, 0) * (1 + $tolerancePercent / 100), 2);

        if ($amount > $maxAllowed) {
            throw ValidationException::withMessages([
                'amount' => "Amount exceeds the dealer's outstanding balance (".number_format(max($balance, 0), 2).") by more than the {$tolerancePercent}% tolerance (max ".number_format($maxAllowed, 2).').',
            ]);
        }
    }
}
