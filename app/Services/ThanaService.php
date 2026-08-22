<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\ThanaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ThanaService extends BaseCrudService
{
    public function __construct(private readonly ThanaRepositoryInterface $thanas)
    {
        parent::__construct($thanas);
    }

    /**
     * @param  array{search?: string, division_id?: string, district_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->thanas->paginateWithFilters($filters, $perPage);
    }
}
