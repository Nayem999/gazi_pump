<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission names are renamed in place (UPDATE, never delete+insert) so
     * that existing role_has_permissions / model_has_permissions pivot rows
     * keep pointing at the same permission ids. activity_log.subject_type
     * rows are rewritten the same way so historical entries logged before
     * the model rename don't reference a now-deleted class.
     */
    private function map(): array
    {
        return [
            'customers' => 'dealers',
            'sales-entries' => 'orders',
        ];
    }

    /**
     * Report permissions use their own free-form key (PermissionName::report())
     * rather than the module-name convention, so they're renamed as exact
     * matches alongside the report views/routes being renamed in code.
     */
    private function reportMap(): array
    {
        return [
            'report.customer-coverage' => 'report.dealer-coverage',
            'report.sales' => 'report.order-performance',
        ];
    }

    public function up(): void
    {
        $this->renamePermissions($this->map());
        $this->renameExactPermissions($this->reportMap());
        $this->renameActivityLogSubjects([
            'App\\Models\\Customer' => 'App\\Models\\Dealer',
            'App\\Models\\SalesEntry' => 'App\\Models\\Order',
        ]);
    }

    public function down(): void
    {
        $this->renamePermissions(array_flip($this->map()));
        $this->renameExactPermissions(array_flip($this->reportMap()));
        $this->renameActivityLogSubjects([
            'App\\Models\\Dealer' => 'App\\Models\\Customer',
            'App\\Models\\Order' => 'App\\Models\\SalesEntry',
        ]);
    }

    private function renameExactPermissions(array $nameMap): void
    {
        foreach ($nameMap as $from => $to) {
            DB::table('permissions')->where('name', $from)->update(['name' => $to]);
        }
    }

    private function renamePermissions(array $moduleMap): void
    {
        foreach ($moduleMap as $from => $to) {
            DB::table('permissions')
                ->where('name', $from)
                ->orWhere('name', 'like', "{$from}.%")
                ->orWhere('name', 'like', "%.{$from}")
                ->orWhere('name', 'like', "%.{$from}.%")
                ->get(['id', 'name'])
                ->each(function ($permission) use ($from, $to): void {
                    DB::table('permissions')
                        ->where('id', $permission->id)
                        ->update(['name' => str_replace($from, $to, $permission->name)]);
                });
        }
    }

    private function renameActivityLogSubjects(array $classMap): void
    {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return;
        }

        foreach ($classMap as $from => $to) {
            DB::table('activity_log')->where('subject_type', $from)->update(['subject_type' => $to]);
        }
    }
};
