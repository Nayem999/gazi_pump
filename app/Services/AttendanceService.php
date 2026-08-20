<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService extends BaseCrudService
{
    public function __construct(private readonly AttendanceRepositoryInterface $attendances)
    {
        parent::__construct($attendances);
    }

    /**
     * @param  array{search?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendances->paginateWithFilters($filters, $perPage);
    }

    public function checkIn(User $user, float $lat, float $lng, UploadedFile $photo): Attendance
    {
        $today = Carbon::today();

        if ($this->attendances->findForUserAndDate($user->id, $today->toDateString())) {
            throw ValidationException::withMessages(['check_in' => 'You have already checked in today.']);
        }

        $checkInAt = Carbon::now();
        [$status, $lateMinutes] = $this->determineStatus($checkInAt);

        return $this->attendances->create([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'check_in_at' => $checkInAt,
            'check_in_lat' => $lat,
            'check_in_lng' => $lng,
            'check_in_photo' => $photo->store('attendance', 'public'),
            'status' => $status->value,
            'late_minutes' => $lateMinutes,
        ]);
    }

    public function checkOut(User $user, float $lat, float $lng, UploadedFile $photo): Attendance
    {
        $attendance = $this->attendances->findForUserAndDate($user->id, Carbon::today()->toDateString());

        if (! $attendance) {
            throw ValidationException::withMessages(['check_out' => 'You have not checked in today.']);
        }

        if ($attendance->isCheckedOut()) {
            throw ValidationException::withMessages(['check_out' => 'You have already checked out today.']);
        }

        return $this->update($attendance, [
            'check_out_at' => Carbon::now(),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'check_out_photo' => $photo->store('attendance', 'public'),
        ]);
    }

    /**
     * Late detection: compares the check-in clock time against the
     * configured office start time + grace period. Only the time-of-day
     * matters, not the date, so this is safe to unit test with any date.
     *
     * @return array{0: AttendanceStatus, 1: int}
     */
    public function determineStatus(Carbon $checkInAt): array
    {
        $officeStart = Carbon::createFromTimeString((string) config('sfa.attendance.office_start_time'));
        $graceMinutes = (int) config('sfa.attendance.late_grace_minutes');
        $cutoff = $officeStart->copy()->addMinutes($graceMinutes);

        $checkInTimeOnly = Carbon::createFromTimeString($checkInAt->format('H:i:s'));

        if ($checkInTimeOnly->greaterThan($cutoff)) {
            return [AttendanceStatus::Late, (int) $checkInTimeOnly->diffInMinutes($officeStart, absolute: true)];
        }

        return [AttendanceStatus::Present, 0];
    }
}
