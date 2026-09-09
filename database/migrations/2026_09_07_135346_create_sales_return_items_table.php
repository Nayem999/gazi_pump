<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wholly owned by its SalesReturn, same shape as OrderItem — plain
     * timestamps only, no soft delete of its own. received_qty starts
     * null (not yet confirmed at the depot) and is filled in by the
     * receiving step; it can be less than requested_qty (e.g. some units
     * arrived damaged/missing), which is exactly what the Credit Note
     * push amounts are based on, not the original request.
     */
    public function up(): void
    {
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('requested_qty', 12, 2);
            $table->decimal('received_qty', 12, 2)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
