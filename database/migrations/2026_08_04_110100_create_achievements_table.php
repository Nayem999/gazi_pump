<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            // One computed achievement per target — recalculated in place
            // (updateOrCreate) rather than kept as a growing history, so
            // it's a strict 1:1, not soft-deleted/audited on its own; the
            // target is the unit of audit/trash.
            $table->foreignId('target_id')->unique()->constrained('targets')->cascadeOnDelete();

            $table->decimal('sales_achieved', 12, 2);
            $table->decimal('collection_achieved', 12, 2);
            $table->unsignedInteger('quantity_achieved');
            // Wide enough to tolerate large overachievement without
            // overflowing — a rep can blow well past 100% of target.
            $table->decimal('sales_pct', 10, 2);
            $table->decimal('collection_pct', 10, 2);
            $table->decimal('quantity_pct', 10, 2);
            $table->decimal('overall_pct', 10, 2);
            $table->string('grade'); // App\Enums\PerformanceGrade
            $table->timestamp('calculated_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
