<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\RecalculateAchievementsJob;
use App\Models\Target;
use App\Repositories\Contracts\TargetRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TargetService extends BaseCrudService
{
    public function __construct(private readonly TargetRepositoryInterface $targets)
    {
        parent::__construct($targets);
    }

    /**
     * @param  array{search?: string, user_id?: string, month?: string, year?: string, grade?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->targets->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $items = $data['product_targets'] ?? null;
        unset($data['product_targets'], $data['mode']);
        $lines = $items ? $this->buildLines($items) : [];

        return DB::transaction(function () use ($data, $lines) {
            if ($lines) {
                $data = [...$data, ...$this->summarize($lines)];
            }

            /** @var Target $target */
            $target = parent::create($data);

            if ($lines) {
                $target->items()->createMany($lines);
            }

            return $this->recalculate($target);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        $items = $data['product_targets'] ?? null;
        unset($data['product_targets'], $data['mode']);
        $lines = $items ? $this->buildLines($items) : [];

        return DB::transaction(function () use ($model, $data, $lines) {
            if ($lines) {
                $data = [...$data, ...$this->summarize($lines)];
            }

            /** @var Target $target */
            $target = parent::update($model, $data);

            // Always replace, not just insert: switching a target back from
            // product-wise to a single overall figure must clear its old
            // breakdown rows, not leave them stale alongside the new totals.
            $target->items()->delete();

            if ($lines) {
                $target->items()->createMany($lines);
            }

            return $this->recalculate($target);
        });
    }

    /**
     * Runs synchronously (::dispatchSync) so an admin creating/editing a
     * target — or clicking "Recalculate" — sees the computed achievement
     * immediately, without needing a queue worker running. The same job
     * class is genuinely queueable (::dispatch) for batch/scheduled
     * recalculation of many targets at once.
     */
    public function recalculate(Target $target): Target
    {
        RecalculateAchievementsJob::dispatchSync($target);

        return $target->load(['achievement', 'items.product']);
    }

    /**
     * The parent Target's own order_value_target/collection_target/
     * quantity_target columns stay the single source of truth achievement
     * calculation reads (CalculateAchievementAction never looks at
     * TargetItem rows) — so a product-wise target's three totals are always
     * the sum of its product rows, kept in lockstep here.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{order_value_target: float, collection_target: float, quantity_target: int}
     */
    private function summarize(array $lines): array
    {
        return [
            'order_value_target' => array_sum(array_column($lines, 'order_target')),
            'collection_target' => array_sum(array_column($lines, 'collection_target')),
            'quantity_target' => array_sum(array_column($lines, 'quantity_target')),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function buildLines(array $items): array
    {
        return collect($items)->values()->map(fn ($item) => [
            'product_id' => (int) $item['product_id'],
            'order_target' => (float) ($item['order_target'] ?? 0),
            'collection_target' => (float) ($item['collection_target'] ?? 0),
            'quantity_target' => (int) ($item['quantity_target'] ?? 0),
        ])->all();
    }
}
