<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

/**
 * Spatie's Role model isn't soft-deletable, so this repository is a
 * standalone contract rather than extending BaseRepositoryInterface
 * (which assumes soft-delete/restore semantics).
 */
interface RoleRepositoryInterface
{
    public function all(): Collection;

    /**
     * @param  array{search?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Role;

    public function findOrFail(int $id): Role;

    public function create(array $attributes): Role;

    public function update(Role $role, array $attributes): Role;

    public function delete(Role $role): bool;
}
