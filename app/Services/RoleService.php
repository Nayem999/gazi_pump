<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private readonly RoleRepositoryInterface $roles) {}

    /**
     * @param  array{search?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->roles->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $role = $this->roles->create($data);
            $role->syncPermissions($permissions);

            return $role;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $permissions = $data['permissions'] ?? null;
            unset($data['permissions']);

            $role = $this->roles->update($role, $data);

            if ($permissions !== null) {
                $role->syncPermissions($permissions);
            }

            return $role;
        });
    }

    public function delete(Role $role): bool
    {
        return $this->roles->delete($role);
    }
}
