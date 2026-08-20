<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with('user')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('user', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUserAndDate(int $userId, string $date): ?Attendance
    {
        return $this->query()->where('user_id', $userId)->whereDate('date', $date)->first();
    }
}
