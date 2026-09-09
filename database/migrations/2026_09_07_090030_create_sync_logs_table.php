<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only history of every sync attempt (success or failure), kept
     * separate from sync_queues' working set so the queue table can stay
     * small while the audit trail never gets pruned (spec: "do not delete
     * audit records").
     */
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('direction');
            $table->timestamp('request_time');
            $table->timestamp('response_time')->nullable();
            $table->string('status');
            $table->string('external_reference')->nullable();
            $table->string('tally_guid')->nullable();
            $table->string('tally_voucher_number')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('external_reference');
            $table->index('tally_guid');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
