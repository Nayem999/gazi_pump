<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per (order line, depot) pair that contributes stock to it —
     * an order_item normally has exactly one allocation row, but Split
     * Depot Fulfillment (spec §13) means the same line can have several,
     * one per depot, when no single depot can cover the full requested_qty
     * alone.
     */
    public function up(): void
    {
        Schema::create('depot_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('depot_id')->constrained('depots')->restrictOnDelete();
            $table->decimal('requested_qty', 12, 2);
            $table->decimal('allocated_qty', 12, 2)->default(0);
            $table->string('allocation_status')->default('pending');
            $table->boolean('is_alternative_depot')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('allocation_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depot_allocations');
    }
};
