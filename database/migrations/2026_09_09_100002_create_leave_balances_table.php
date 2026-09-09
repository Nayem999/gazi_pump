<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entitlement only - deliberately NOT a running balance.
 *
 * The days someone has *used* are derived from their approved leave
 * requests whenever a balance is read, never stored here. A stored counter
 * has to be adjusted on approve, on cancel, on a request being deleted or
 * restored, and on any correction to an approved request; miss one path
 * and the number silently drifts from the requests it claims to summarise,
 * with no way to tell which is right. Deriving it cannot drift.
 *
 * What IS stored is what cannot be derived: how many days this person is
 * entitled to for this type this year, including any carry-forward or
 * individual adjustment a manager has granted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');

            // Seeded from leave_types.annual_quota, then editable per
            // person - two people can hold different entitlements for the
            // same type in the same year.
            $table->decimal('entitled_days', 5, 1)->default(0);

            // Days brought in from last year, tracked separately so the
            // entitlement and the carry-forward stay legible rather than
            // being silently folded into one number.
            $table->decimal('carried_forward_days', 5, 1)->default(0);

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // One entitlement row per person, type and year.
            $table->unique(['user_id', 'leave_type_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
