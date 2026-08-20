<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\GpsLog;
use Illuminate\Support\Collection;

interface GpsLogRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return Collection<int, GpsLog>
     */
    public function historyForUserOnDate(int $userId, string $date, ?string $trashed = null): Collection;

    public function latestForUser(int $userId): ?GpsLog;
}
