<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Dealer;
use App\Repositories\Contracts\VisitPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VisitPlanService extends BaseCrudService
{
    public function __construct(private readonly VisitPlanRepositoryInterface $visitPlans)
    {
        parent::__construct($visitPlans);
    }

    /**
     * @param  array{search?: string, user_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->visitPlans->paginateWithFilters($filters, $perPage);
    }

    /**
     * Creates one Visit Plan per dealer id, all sharing the same executive/date/status/notes.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $dealerIds
     */
    public function createMany(array $data, array $dealerIds): int
    {
        return DB::transaction(function () use ($data, $dealerIds) {
            $count = 0;
            $providedTerritoryId = $this->toNullableInt($data['territory_id'] ?? null);

            foreach ($dealerIds as $dealerId) {
                $this->visitPlans->create([
                    ...$data,
                    'dealer_id' => (int) $dealerId,
                    'territory_id' => $this->resolveTerritoryId($providedTerritoryId, (int) $dealerId),
                ]);
                $count++;
            }

            return $count;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        if (array_key_exists('dealer_id', $data)) {
            $data['territory_id'] = $this->resolveTerritoryId(
                $this->toNullableInt($data['territory_id'] ?? null),
                (int) $data['dealer_id']
            );
        }

        return parent::update($model, $data);
    }

    /**
     * A territory picked explicitly on the form always wins; otherwise fall
     * back to whichever territory the dealer itself belongs to (Dealer also
     * has a nullable territory_id, so this can still end up null).
     */
    private function resolveTerritoryId(?int $providedTerritoryId, int $dealerId): ?int
    {
        return $providedTerritoryId ?? Dealer::find($dealerId)?->territory_id;
    }

    /**
     * Request-validated input arrives as strings (or an empty string for a
     * blank optional field) regardless of the "integer" validation rule —
     * that rule checks the value looks numeric, it doesn't cast it.
     */
    private function toNullableInt(mixed $value): ?int
    {
        return $value !== null && $value !== '' ? (int) $value : null;
    }
}
