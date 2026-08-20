<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sales_team_id')->nullable()->after('designation')->constrained('sales_teams')->nullOnDelete();
            $table->foreignId('territory_id')->nullable()->after('sales_team_id')->constrained('territories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sales_team_id']);
            $table->dropForeign(['territory_id']);

            $table->dropColumn(['sales_team_id', 'territory_id']);
        });
    }
};
