<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\CollectionEntry;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\CollectionEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CollectionEntryService extends BaseCrudService
{
    public function __construct(private readonly CollectionEntryRepositoryInterface $collectionEntries)
    {
        parent::__construct($collectionEntries);
    }

    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, payment_method?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->collectionEntries->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $this->validateAmount((int) $data['dealer_id'], (float) $data['amount']);

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        $this->validateAmount((int) $data['dealer_id'], (float) $data['amount'], excludeCollectionId: $model->id);

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
        ]);

        return $entry;
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
