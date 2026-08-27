<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CashHandoverStatus;
use App\Enums\PaymentMethod;
use App\Models\CashHandover;
use App\Models\CollectionEntry;
use App\Models\Setting;
use App\Repositories\Contracts\CashHandoverRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * "Secure Dealer Collection" panel 4: a Sales Executive accumulates cash in
 * hand as they record cash collections, then periodically hands it to their
 * manager. Cash in hand isn't tracked entry-by-entry — it's a running
 * balance (total cash collected minus total *confirmed* handovers), the
 * same style as CollectionEntryService::outstandingBalance().
 */
class CashHandoverService extends BaseCrudService
{
    public function __construct(private readonly CashHandoverRepositoryInterface $cashHandovers)
    {
        parent::__construct($cashHandovers);
    }

    /**
     * @param  array{user_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->cashHandovers->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $data['status'] ??= CashHandoverStatus::Pending->value;
        $data['handover_date'] ??= Carbon::today()->toDateString();

        $cashInHand = $this->cashInHand((int) $data['user_id']);
        if ((float) $data['amount'] > $cashInHand) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds this executive\'s current cash in hand ('.number_format($cashInHand, 2).').',
            ]);
        }

        return parent::create($data);
    }

    /**
     * Cash isn't off an executive's hands until a manager has actually
     * confirmed receiving it — a Pending or Rejected handover doesn't
     * reduce their balance.
     */
    public function confirm(CashHandover $handover, int $managerId): CashHandover
    {
        $this->assertPending($handover);

        // Re-check at confirm time, not just at creation: two pending
        // handovers can each individually pass the create-time check
        // against the same not-yet-confirmed cash, so only confirming one
        // of them actually commits it — the other must be re-validated
        // against what's left before it can be confirmed too.
        $cashInHand = $this->cashInHand($handover->user_id);
        if ((float) $handover->amount > $cashInHand) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds this executive\'s current cash in hand ('.number_format($cashInHand, 2).'). Reject this handover or reconcile the other pending ones first.',
            ]);
        }

        $handover->update([
            'status' => CashHandoverStatus::Confirmed->value,
            'confirmed_by' => $managerId,
            'confirmed_at' => now(),
        ]);

        return $handover->fresh();
    }

    public function reject(CashHandover $handover, int $managerId): CashHandover
    {
        $this->assertPending($handover);

        $handover->update([
            'status' => CashHandoverStatus::Rejected->value,
            'confirmed_by' => $managerId,
            'confirmed_at' => now(),
        ]);

        return $handover->fresh();
    }

    public function cashInHand(int $userId): float
    {
        $collected = (float) CollectionEntry::where('user_id', $userId)
            ->where('payment_method', PaymentMethod::Cash->value)
            ->sum('amount');

        $handedOver = (float) CashHandover::where('user_id', $userId)
            ->where('status', CashHandoverStatus::Confirmed->value)
            ->sum('amount');

        return $collected - $handedOver;
    }

    public function dailyLimit(): ?float
    {
        $limit = Setting::current()->cash_daily_limit_amount;

        return $limit !== null ? (float) $limit : null;
    }

    private function assertPending(CashHandover $handover): void
    {
        if ($handover->status !== CashHandoverStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This handover has already been '.$handover->status->label().' and cannot be changed.',
            ]);
        }
    }
}
