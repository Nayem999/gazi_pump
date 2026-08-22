<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('customers', 'dealers');

        Schema::table('dealers', function (Blueprint $table): void {
            $table->renameColumn('customer_code', 'dealer_code');
        });
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table): void {
            $table->renameColumn('dealer_code', 'customer_code');
        });

        Schema::rename('dealers', 'customers');
    }
};
