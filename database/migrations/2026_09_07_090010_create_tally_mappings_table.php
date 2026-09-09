<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tally_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('sfa_id');
            $table->string('tally_guid')->nullable();
            $table->string('tally_name')->nullable();
            $table->string('tally_alter_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status')->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // A given SFA row maps to exactly one Tally record, and vice
            // versa — never resolved by name alone (spec: stable identifiers
            // only). A nullable tally_guid can repeat as NULL (not-yet-
            // synced rows) without violating the unique index.
            $table->unique(['entity_type', 'sfa_id']);
            $table->unique(['entity_type', 'tally_guid']);
            $table->index('sync_status');
            $table->index('tally_alter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_mappings');
    }
};
