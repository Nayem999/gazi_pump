<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Generic create/update/delete/restore/bulk operations shared by every
 * simple CRUD module. Concrete services extend this, redeclare the
 * constructor with their specific repository interface, and add their own
 * paginate()/filter logic on top.
 */
abstract class BaseCrudService
{
    public function __construct(protected readonly BaseRepositoryInterface $repository) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        return $this->repository->update($model, $data);
    }

    public function delete(Model $model): bool
    {
        return $this->repository->delete($model);
    }

    public function restore(int $id): Model
    {
        return $this->repository->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->repository->forceDelete($id);
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = 0;
            foreach ($ids as $id) {
                if ($model = $this->repository->find($id)) {
                    $this->repository->delete($model);
                    $count++;
                }
            }

            return $count;
        });
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkRestore(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $this->repository->restore($id);
            $count++;
        }

        return $count;
    }
}
