<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_entries', function (Blueprint $table): void {
            $table->renameColumn('customer_id', 'dealer_id');
        });

        Schema::table('collection_entries', function (Blueprint $table): void {
            $table->renameColumn('customer_id', 'dealer_id');
        });

        Schema::table('visit_plans', function (Blueprint $table): void {
            $table->renameColumn('customer_id', 'dealer_id');
        });

        Schema::table('visits', function (Blueprint $table): void {
            $table->renameColumn('customer_id', 'dealer_id');
            $table->renameColumn('distance_from_customer_meters', 'distance_from_dealer_meters');
        });

        Schema::table('customer_accounts', function (Blueprint $table): void {
            $table->renameColumn('customer_id', 'dealer_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_accounts', function (Blueprint $table): void {
            $table->renameColumn('dealer_id', 'customer_id');
        });

        Schema::table('visits', function (Blueprint $table): void {
            $table->renameColumn('dealer_id', 'customer_id');
            $table->renameColumn('distance_from_dealer_meters', 'distance_from_customer_meters');
        });

        Schema::table('visit_plans', function (Blueprint $table): void {
            $table->renameColumn('dealer_id', 'customer_id');
        });

        Schema::table('collection_entries', function (Blueprint $table): void {
            $table->renameColumn('dealer_id', 'customer_id');
        });

        Schema::table('sales_entries', function (Blueprint $table): void {
            $table->renameColumn('dealer_id', 'customer_id');
        });
    }
};
