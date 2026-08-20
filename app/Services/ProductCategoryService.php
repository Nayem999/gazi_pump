<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCategoryService extends BaseCrudService
{
    public function __construct(private readonly ProductCategoryRepositoryInterface $categories)
    {
        parent::__construct($categories);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->categories->paginateWithFilters($filters, $perPage);
    }
}
