<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single-row table (see App\Models\Setting::current()) — there is
 * deliberately no route/UI to create a second row. Business-rule columns
 * mirror config/sfa.php's structure; ApplySettingsToConfig merges this row's
 * values into config() on every request so existing services keep reading
 * config('sfa.*') unchanged while becoming admin-editable at runtime.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('company_name');
            $table->string('company_logo')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();

            $table->string('attendance_office_start_time');
            $table->unsignedInteger('attendance_late_grace_minutes');
            $table->unsignedInteger('visit_gps_radius_meters');
            $table->decimal('sales_max_discount_percent', 5, 2);
            $table->decimal('collection_overpayment_tolerance_percent', 5, 2);
            $table->unsignedTinyInteger('target_grade_a_min');
            $table->unsignedTinyInteger('target_grade_b_min');
            $table->unsignedTinyInteger('target_grade_c_min');
            $table->unsignedTinyInteger('target_grade_d_min');
            $table->json('low_performance_grades');
            $table->unsignedInteger('target_reminder_days_before_month_end');
            $table->decimal('target_reminder_min_pct', 5, 2);
            $table->unsignedInteger('live_gps_stale_after_minutes');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
