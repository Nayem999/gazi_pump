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
            $table->string('attendance_office_end_time')->after('attendance_office_start_time');
            $table->json('attendance_weekend_days')->after('attendance_late_grace_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['attendance_office_end_time', 'attendance_weekend_days']);
        });
    }
};
