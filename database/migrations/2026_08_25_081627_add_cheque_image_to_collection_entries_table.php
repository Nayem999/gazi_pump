<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->string('cheque_image')->nullable()->after('reference_no');
        });
    }

    public function down(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->dropColumn('cheque_image');
        });
    }
};
