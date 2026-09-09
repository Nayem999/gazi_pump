<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

/**
 * A starting set of leave types, since the module cannot be used until at
 * least one exists.
 *
 * Quotas here are a common Bangladeshi private-sector shape and are meant
 * to be edited to match the customer's own policy - they are only the
 * default used when someone's entitlement is first set up, so changing
 * them later never rewrites an entitlement already granted.
 */
class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Casual Leave', 'code' => 'CL', 'annual_quota' => 10, 'is_paid' => true,
                'description' => 'Short, unplanned absences - personal errands, family matters.'],
            ['name' => 'Sick Leave', 'code' => 'SL', 'annual_quota' => 14, 'is_paid' => true,
                'description' => 'Illness. Often filed retroactively, which approval handles by replacing an auto-marked Absent day.'],
            ['name' => 'Annual Leave', 'code' => 'AL', 'annual_quota' => 20, 'is_paid' => true,
                'description' => 'Planned holiday, normally requested in advance.'],
            ['name' => 'Unpaid Leave', 'code' => 'UL', 'annual_quota' => 0, 'is_paid' => false,
                'description' => 'Approved time off with no entitlement behind it; the balance is expected to go negative.'],
        ];

        foreach ($types as $type) {
            // Keyed on code so re-seeding never duplicates a type or
            // overwrites a quota the customer has already adjusted.
            LeaveType::firstOrCreate(['code' => $type['code']], $type + ['status' => true]);
        }
    }
}
