<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('territory_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('territory_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'territory_id']);
        });

        // Preserve every existing single-territory assignment as the user's
        // first (and, at this point, only) row in the new pivot table.
        DB::table('users')
            ->whereNotNull('territory_id')
            ->orderBy('id')
            ->select('id', 'territory_id')
            ->chunkById(200, function ($rows): void {
                $now = now();

                DB::table('territory_user')->insert(
                    $rows->map(fn ($row) => [
                        'user_id' => $row->id,
                        'territory_id' => $row->territory_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('territory_user');
    }
};
