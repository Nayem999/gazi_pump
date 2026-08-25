<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface HolidayRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, year?: string, status?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * True when an active holiday is recorded for this calendar date.
     */
    public function existsOnDate(string $date): bool;
}
