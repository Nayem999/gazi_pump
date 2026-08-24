<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * One-off (but safely rerunnable) wipe of demo/seed content ahead of
 * go-live. Preserves Settings, District/Division/Thana, Roles/Permissions,
 * Sales Teams, and every Super Admin account — everything else listed here
 * is seed/demo data with no real-world meaning worth keeping.
 *
 * TRUNCATE is DDL in MySQL/InnoDB and auto-commits, so this cannot be
 * wrapped in a rolling-back transaction — a full mysqldump backup should
 * always be taken immediately before running this.
 */
class PurgeDemoDataCommand extends Command
{
    protected $signature = 'demo:purge {--force : Skip the confirmation prompt}';

    protected $description = 'Permanently delete all demo data, keeping Settings, District/Division/Thana, Roles/Permissions, Sales Teams, and Super Admin accounts';

    /**
     * @var list<string>
     */
    private const TABLES_TO_TRUNCATE = [
        'achievements',
        'activity_log',
        'announcements',
        'attendances',
        'brochures',
        'collection_entries',
        'customer_accounts',
        'dealers',
        'faqs',
        'gps_logs',
        'inquiries',
        'news',
        'notifications',
        'orders',
        'order_items',
        'products',
        'product_categories',
        'promotions',
        'service_centers',
        'targets',
        'territories',
        'territory_user',
        'visits',
        'visit_plans',
        'visit_requests',
    ];

    public function handle(): int
    {
        $database = config('database.connections.mysql.database');

        if (! $this->option('force') && ! $this->confirm(
            "This will PERMANENTLY delete all demo data (dealers, orders, visits, products, non-admin users, etc.) from the '{$database}' database. A backup should already exist. Continue?"
        )) {
            $this->warn('Aborted — no changes made.');

            return self::FAILURE;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (self::TABLES_TO_TRUNCATE as $table) {
            DB::table($table)->truncate();
            $this->line("Truncated {$table}");
        }

        $keptUserIds = User::role('Super Admin')->pluck('id');

        DB::table('model_has_roles')->where('model_type', User::class)->whereNotIn('model_id', $keptUserIds)->delete();
        DB::table('model_has_permissions')->where('model_type', User::class)->whereNotIn('model_id', $keptUserIds)->delete();
        DB::table('personal_access_tokens')->where('tokenable_type', User::class)->whereNotIn('tokenable_id', $keptUserIds)->delete();
        $deletedUsers = DB::table('users')->whereNotIn('id', $keptUserIds)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Cache::forget('settings.current');

        $this->info("Demo data purged. Deleted {$deletedUsers} non-admin user(s). Kept: settings, districts, divisions, thanas, roles, permissions, sales_teams, and ".$keptUserIds->count().' Super Admin account(s).');

        return self::SUCCESS;
    }
}
