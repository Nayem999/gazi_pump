<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\HolidayRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class HolidayService extends BaseCrudService
{
    public function __construct(private readonly HolidayRepositoryInterface $holidays)
    {
        parent::__construct($holidays);
    }

    /**
     * @param  array{search?: string, year?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->holidays->paginateWithFilters($filters, $perPage);
    }

    /**
     * True when $date is a recorded (active) government holiday — used to
     * skip absence backfilling the same way AttendanceService::isWeekendDay()
     * skips weekends.
     */
    public function isHoliday(Carbon $date): bool
    {
        return $this->holidays->existsOnDate($date->toDateString());
    }
}
