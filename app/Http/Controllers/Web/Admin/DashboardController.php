<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'activeUserCount' => User::where('status', true)->count(),
            'salesExecutiveCount' => User::role('Sales Executive')->count(),
            'territoryCount' => Territory::where('status', true)->count(),
            'customerCount' => Customer::where('status', true)->count(),
            'productCount' => Product::where('status', true)->count(),
            'presentTodayCount' => Attendance::whereDate('date', Carbon::today())->whereNotNull('check_in_at')->count(),
            'attendanceTrend' => $this->attendanceTrend(),
            'recentActivity' => Activity::with('causer')->latest()->limit(10)->get(),
        ]);
    }

    /**
     * Last 14 days of Present/Late/Absent counts, shaped for the ApexCharts
     * area chart on the dashboard.
     *
     * @return array<int, array{date: string, present: int, late: int, absent: int}>
     */
    private function attendanceTrend(): array
    {
        $dateFrom = Carbon::today()->subDays(13);

        $rows = Attendance::selectRaw('date, status, COUNT(*) as total')
            ->where('date', '>=', $dateFrom->toDateString())
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn (Attendance $row) => $row->date->toDateString());

        return collect(range(0, 13))->map(function (int $offset) use ($dateFrom, $rows) {
            $day = $dateFrom->copy()->addDays($offset);
            $dayRows = $rows->get($day->toDateString(), collect());

            return [
                'date' => $day->format('M d'),
                'present' => (int) ($dayRows->firstWhere('status', AttendanceStatus::Present)->total ?? 0),
                'late' => (int) ($dayRows->firstWhere('status', AttendanceStatus::Late)->total ?? 0),
                'absent' => (int) ($dayRows->firstWhere('status', AttendanceStatus::Absent)->total ?? 0),
            ];
        })->values()->all();
    }
}
