<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * activity_log.log_name is set at log-time from the model's table name
 * (BaseModel::useLogName($this->getTable())) and drives the human-readable
 * label shown on the Activity Log / Dashboard "Recent Activity" widget.
 * The earlier rename migration fixed subject_type (needed so historical
 * entries can still resolve their subject model) but missed this cosmetic
 * column, so pre-rename rows still displayed as "Customer"/"Sales Entry".
 */
return new class extends Migration
{
    private function map(): array
    {
        return [
            'customers' => 'dealers',
            'sales_entries' => 'orders',
        ];
    }

    public function up(): void
    {
        $this->rename($this->map());
    }

    public function down(): void
    {
        $this->rename(array_flip($this->map()));
    }

    private function rename(array $map): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        foreach ($map as $from => $to) {
            DB::table('activity_log')->where('log_name', $from)->update(['log_name' => $to]);
        }
    }
};
