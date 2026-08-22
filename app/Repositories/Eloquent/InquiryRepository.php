<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Inquiry;
use App\Repositories\Contracts\InquiryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class InquiryRepository extends BaseRepository implements InquiryRepositoryInterface
{
    public function __construct(Inquiry $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['customerAccount', 'product'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
