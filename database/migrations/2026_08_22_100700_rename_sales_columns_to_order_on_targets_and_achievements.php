<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('targets', function (Blueprint $table): void {
            $table->renameColumn('sales_value_target', 'order_value_target');
        });

        Schema::table('achievements', function (Blueprint $table): void {
            $table->renameColumn('sales_achieved', 'order_achieved');
            $table->renameColumn('sales_pct', 'order_pct');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table): void {
            $table->renameColumn('order_achieved', 'sales_achieved');
            $table->renameColumn('order_pct', 'sales_pct');
        });

        Schema::table('targets', function (Blueprint $table): void {
            $table->renameColumn('order_value_target', 'sales_value_target');
        });
    }
};
