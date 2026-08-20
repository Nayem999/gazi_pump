<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    /**
     * @param  array{search?: string, role?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $photo = null): User
    {
        return DB::transaction(function () use ($data, $photo) {
            $data['password'] = Hash::make($data['password']);
            $data['status'] = $data['status'] ?? true;

            if ($photo) {
                $data['photo'] = $photo->store('users', 'public');
            }

            $roles = $data['roles'] ?? [];
            unset($data['roles']);

            $user = $this->users->create($data);
            $user->syncRoles($roles);

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?UploadedFile $photo = null): User
    {
        return DB::transaction(function () use ($user, $data, $photo) {
            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if ($photo) {
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }
                $data['photo'] = $photo->store('users', 'public');
            }

            $roles = $data['roles'] ?? null;
            unset($data['roles']);

            $user = $this->users->update($user, $data);

            if ($roles !== null) {
                $user->syncRoles($roles);
            }

            return $user;
        });
    }

    public function delete(User $user): bool
    {
        return $this->users->delete($user);
    }

    public function restore(int $id): User
    {
        return $this->users->restore($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->users->forceDelete($id);
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = 0;
            foreach ($ids as $id) {
                if ($user = $this->users->find($id)) {
                    $this->users->delete($user);
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
            $this->users->restore($id);
            $count++;
        }

        return $count;
    }
}
