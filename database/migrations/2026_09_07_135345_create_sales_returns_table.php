<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Full Sales Return workflow (spec's Phase 6): request -> approve/
     * reject -> dispatch -> depot receiving -> Tally accounting (a Credit
     * Note voucher). A real business record like Order (soft deletes/
     * audit), not an append-only log like Delivery, since a request can
     * still be corrected before approval.
     */
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('dealer_id')->constrained('dealers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('requested'); // App\Enums\SalesReturnStatus
            $table->text('reason')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();

            $table->foreignId('receiving_depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();

            $table->string('external_reference')->nullable()->unique();
            $table->string('tally_guid')->nullable()->index();
            $table->string('tally_credit_note_number')->nullable();
            $table->string('sync_status')->default('not_synced'); // App\Enums\TallyRecordSyncStatus
            $table->text('sync_error')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
