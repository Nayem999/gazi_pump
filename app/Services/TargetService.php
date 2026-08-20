<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\RecalculateAchievementsJob;
use App\Models\Target;
use App\Repositories\Contracts\TargetRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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
        /** @var Target $target */
        $target = parent::create($data);

        return $this->recalculate($target);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        /** @var Target $target */
        $target = parent::update($model, $data);

        return $this->recalculate($target);
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

        return $target->load('achievement');
    }
}
