<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Jobs\RecalculateAchievementsJob;
use App\Models\AchievementEntry;
use App\Models\Target;
use App\Models\User;
use App\Repositories\Contracts\AchievementEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AchievementEntryService extends BaseCrudService
{
    public function __construct(private readonly AchievementEntryRepositoryInterface $achievementEntries)
    {
        parent::__construct($achievementEntries);
    }

    public function paginate(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->achievementEntries->paginateWithFilters($filters, $perPage, $viewer);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $items = $data['achievement_items'] ?? null;
        unset($data['achievement_items'], $data['mode']);
        $data['status'] ??= ApprovalStatus::Pending->value;
        $lines = $items ? $this->buildLines($items) : [];

        return DB::transaction(function () use ($data, $lines) {
            if ($lines) {
                $data = [...$data, ...$this->summarize($lines)];
            }

            /** @var AchievementEntry $entry */
            $entry = parent::create($data);

            if ($lines) {
                $entry->items()->createMany($lines);
            }

            $this->recalculateLinkedTarget($entry);

            return $entry->load(['items.product', 'user']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        $items = $data['achievement_items'] ?? null;
        unset($data['achievement_items'], $data['mode']);
        $lines = $items ? $this->buildLines($items) : [];

        return DB::transaction(function () use ($model, $data, $lines) {
            if ($lines) {
                $data = [...$data, ...$this->summarize($lines)];
            }

            /** @var AchievementEntry $entry */
            $entry = parent::update($model, $data);

            // Always replace, not just insert — switching an entry back from
            // product-wise to a single overall figure must clear its old
            // breakdown rows, not leave them stale alongside the new totals.
            $entry->items()->delete();

            if ($lines) {
                $entry->items()->createMany($lines);
            }

            $this->recalculateLinkedTarget($entry);

            return $entry->load(['items.product', 'user']);
        });
    }

    public function delete(Model $model): bool
    {
        /** @var AchievementEntry $entry */
        $entry = $model;
        $result = parent::delete($model);
        $this->recalculateLinkedTarget($entry);

        return $result;
    }

    public function restore(int $id): Model
    {
        $entry = parent::restore($id);
        $this->recalculateLinkedTarget($entry);

        return $entry;
    }

    /**
     * Self-service field entry: create-or-update the caller's own entry for
     * a given date, but only while it's still Pending — once a manager has
     * approved or rejected it, no further edits are accepted (mirrors
     * Order/CollectionEntryService's forward-only rule, applied here to
     * edits rather than a separate approve/reject action).
     *
     * @param  array<string, mixed>  $data
     */
    public function recordAchievement(User $user, ?string $date, array $data): AchievementEntry
    {
        $entryDate = $date ?: Carbon::today()->toDateString();

        $existing = AchievementEntry::where('user_id', $user->id)->where('entry_date', $entryDate)->first();

        if ($existing && $existing->status !== ApprovalStatus::Pending) {
            throw ValidationException::withMessages([
                'entry_date' => 'This achievement entry has already been '.$existing->status->label().' and can no longer be edited.',
            ]);
        }

        $payload = [...$data, 'user_id' => $user->id, 'entry_date' => $entryDate];

        /** @var AchievementEntry $entry */
        $entry = $existing ? $this->update($existing, $payload) : $this->create($payload);

        return $entry;
    }

    /**
     * Forward-only, mirroring OrderService/CollectionEntryService: only a
     * Pending entry can be approved or rejected, and both are terminal.
     * Approving is the only transition that actually changes a linked
     * Target's achievement (rejecting one that was never approved is a
     * no-op for the sum), but recalculating after both keeps the rule
     * simple and correct even if a later edit re-opens the question.
     */
    public function approve(AchievementEntry $entry, int $approverId): AchievementEntry
    {
        $this->assertPending($entry);

        $entry->update(['status' => ApprovalStatus::Approved->value, 'approved_by' => $approverId, 'approved_at' => now()]);
        $this->recalculateLinkedTarget($entry);

        return $entry->fresh();
    }

    public function reject(AchievementEntry $entry, int $approverId): AchievementEntry
    {
        $this->assertPending($entry);

        $entry->update(['status' => ApprovalStatus::Rejected->value, 'approved_by' => $approverId, 'approved_at' => now()]);
        $this->recalculateLinkedTarget($entry);

        return $entry->fresh();
    }

    private function assertPending(AchievementEntry $entry): void
    {
        if ($entry->status !== ApprovalStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This achievement entry has already been '.$entry->status->label().' and cannot be changed.',
            ]);
        }
    }

    /**
     * Recomputes the Achievement snapshot of whichever Target covers this
     * entry's user/month/year, if one exists — a no-op when the executive
     * has no Target assigned for that period.
     */
    private function recalculateLinkedTarget(AchievementEntry $entry): void
    {
        $target = Target::where('user_id', $entry->user_id)
            ->where('month', $entry->entry_date->month)
            ->where('year', $entry->entry_date->year)
            ->first();

        if ($target) {
            RecalculateAchievementsJob::dispatchSync($target);
        }
    }

    /**
     * The parent AchievementEntry's own order_value_achieved/
     * collection_achieved/quantity_achieved columns stay the single source
     * of truth CalculateAchievementAction reads — so a product-wise entry's
     * three totals are always the sum of its product rows, kept in lockstep
     * here (mirrors TargetService::summarize()).
     */
    private function summarize(array $lines): array
    {
        return [
            'order_value_achieved' => array_sum(array_column($lines, 'order_achieved')),
            'collection_achieved' => array_sum(array_column($lines, 'collection_achieved')),
            'quantity_achieved' => array_sum(array_column($lines, 'quantity_achieved')),
        ];
    }

    private function buildLines(array $items): array
    {
        return collect($items)->values()->map(fn ($item) => [
            'product_id' => (int) $item['product_id'],
            'order_achieved' => (float) ($item['order_achieved'] ?? 0),
            'collection_achieved' => (float) ($item['collection_achieved'] ?? 0),
            'quantity_achieved' => (int) ($item['quantity_achieved'] ?? 0),
        ])->all();
    }
}
