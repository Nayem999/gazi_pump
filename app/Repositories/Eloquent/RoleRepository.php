<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function __construct(private readonly Role $model) {}

    public function all(): Collection
    {
        return $this->model->newQuery()->with('permissions')->get();
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->withCount(['permissions', 'users'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Role
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(int $id): Role
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function create(array $attributes): Role
    {
        return $this->model->create($attributes + ['guard_name' => 'web']);
    }

    public function update(Role $role, array $attributes): Role
    {
        $role->update($attributes);

        return $role->refresh();
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }
}
