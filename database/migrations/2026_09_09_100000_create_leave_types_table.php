<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            // The default yearly entitlement for this type. Copied onto a
            // leave_balances row when a user's entitlement is first set up,
            // rather than read live: changing the policy next year must not
            // silently rewrite what people were already granted this year.
            $table->unsignedSmallInteger('annual_quota')->default(0);

            // Unpaid types still consume nothing from a balance but are
            // recorded and approved the same way, so payroll can see them.
            $table->boolean('is_paid')->default(true);

            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
