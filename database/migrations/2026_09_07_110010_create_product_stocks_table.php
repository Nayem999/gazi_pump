<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tally is the source of truth for opening/in/out/closing/available —
     * these five columns are only ever overwritten wholesale by a Tally
     * stock-sync job, never computed locally (spec §14). reserved_qty/
     * allocated_qty are SFA's own operational overlay for order management
     * and are never sent back to Tally.
     */
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('opening_qty', 12, 2)->default(0);
            $table->decimal('in_qty', 12, 2)->default(0);
            $table->decimal('out_qty', 12, 2)->default(0);
            $table->decimal('closing_qty', 12, 2)->default(0);
            $table->decimal('available_qty', 12, 2)->default(0);
            $table->decimal('reserved_qty', 12, 2)->default(0);
            $table->decimal('allocated_qty', 12, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['depot_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
