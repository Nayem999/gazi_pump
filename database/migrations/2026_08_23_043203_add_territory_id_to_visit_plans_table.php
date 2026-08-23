<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional metadata, not a replacement for dealer_id: lets a visit plan
     * be tagged with a territory (auto-derived from the dealer's own
     * territory when left blank — see VisitPlanService) for filtering and
     * reporting, without changing how many rows a create submits.
     */
    public function up(): void
    {
        Schema::table('visit_plans', function (Blueprint $table) {
            $table->foreignId('territory_id')->nullable()->after('dealer_id')->constrained('territories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visit_plans', function (Blueprint $table) {
            $table->dropForeign(['territory_id']);
            $table->dropColumn('territory_id');
        });
    }
};
