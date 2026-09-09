<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Delivery/Challan row is created already dispatched (see
     * DeliveryService::dispatch()) — there is no draft state, so this is a
     * plain append-only operational record (same reasoning as Achievement/
     * ProductStock): dispatched_by + timestamps are audit enough, no
     * soft-deletes/created_by/updated_by ceremony for a record nothing ever
     * edits or removes.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->date('delivery_date');
            $table->string('status')->default('dispatched'); // App\Enums\DeliveryStatus
            $table->string('external_reference')->nullable()->unique();
            $table->string('tally_guid')->nullable()->index();
            $table->string('tally_delivery_number')->nullable();
            $table->string('sync_status')->default('not_synced'); // App\Enums\TallyRecordSyncStatus
            $table->text('sync_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
