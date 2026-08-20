<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_entry_items', function (Blueprint $table) {
            $table->id();
            // A line item is wholly owned by its sale — deleting the sale
            // (a hard delete only, since sales_entries itself is soft-deleted)
            // deletes its lines with it, so no separate audit/soft-delete
            // columns are needed here.
            $table->foreignId('sales_entry_id')->constrained('sales_entries')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2); // snapshot of the product's price at the time of sale
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); // quantity * unit_price - discount_amount

            $table->timestamps();

            $table->index('sales_entry_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_entry_items');
    }
};
