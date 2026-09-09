<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();

            $table->date('from_date');
            $table->date('to_date');

            // Working days only - weekends and recorded holidays are
            // excluded when this is calculated (see LeaveRequestService).
            // Stored rather than recomputed on read, because the holiday
            // calendar can change afterwards and an approved request must
            // keep the day count it was actually approved for.
            $table->decimal('days', 5, 1);

            // Half days are expressed through `days`, so a single-date
            // request can consume 0.5. Kept as a flag too, because the
            // request form and the mobile app both need to show which half
            // a person asked for without inferring it from a decimal.
            $table->boolean('is_half_day')->default(false);

            $table->text('reason');
            $table->string('status')->default('pending');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('decision_remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            // Serves both "this user's leave history" and the overlap check
            // every new request runs against the same user's dates.
            $table->index(['user_id', 'from_date', 'to_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
