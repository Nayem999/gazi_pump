<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_items', function (Blueprint $table): void {
            $table->id();
            // Wholly owned by its achievement entry — mirrors target_items:
            // no separate audit/soft-delete columns needed here.
            $table->foreignId('achievement_entry_id')->constrained('achievement_entries')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->decimal('order_achieved', 12, 2)->default(0);
            $table->decimal('collection_achieved', 12, 2)->default(0);
            $table->unsignedInteger('quantity_achieved')->default(0);

            $table->timestamps();

            $table->index('achievement_entry_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_items');
    }
};
