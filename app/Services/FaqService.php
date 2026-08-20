<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\FaqRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FaqService extends BaseCrudService
{
    public function __construct(private readonly FaqRepositoryInterface $faqs)
    {
        parent::__construct($faqs);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->faqs->paginateWithFilters($filters, $perPage);
    }
}
