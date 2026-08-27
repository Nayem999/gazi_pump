<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_items', function (Blueprint $table) {
            $table->id();
            // Wholly owned by its target — deleting the target (a hard
            // delete only, since targets itself is soft-deleted) deletes its
            // product-wise rows with it, so no separate audit/soft-delete
            // columns are needed here (mirrors order_items).
            $table->foreignId('target_id')->constrained('targets')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->decimal('order_target', 12, 2)->default(0);
            $table->decimal('collection_target', 12, 2)->default(0);
            $table->unsignedInteger('quantity_target')->default(0);

            $table->timestamps();

            $table->index('target_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_items');
    }
};
