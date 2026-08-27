<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Null means no limit is configured — cash-in-hand is still
            // tracked and shown, just without a warning threshold.
            $table->decimal('cash_daily_limit_amount', 12, 2)->nullable()->after('collection_otp_expiry_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('cash_daily_limit_amount');
        });
    }
};
