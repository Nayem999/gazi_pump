<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            // Null unless this collection was confirmed via the dealer OTP
            // flow — recording still works without it (e.g. back-office
            // corrections, imports), this only marks entries that went
            // through the extra verification step.
            $table->timestamp('otp_verified_at')->nullable()->after('cheque_status');
        });
    }

    public function down(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->dropColumn('otp_verified_at');
        });
    }
};
