<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('sales_entry_items', 'order_items');

        Schema::table('order_items', function (Blueprint $table): void {
            $table->renameColumn('sales_entry_id', 'order_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->renameColumn('order_id', 'sales_entry_id');
        });

        Schema::rename('order_items', 'sales_entry_items');
    }
};
