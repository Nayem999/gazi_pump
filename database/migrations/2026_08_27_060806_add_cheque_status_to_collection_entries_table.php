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
            // Only meaningful when payment_method = cheque; null for every
            // other method (App\Enums\ChequeStatus).
            $table->string('cheque_status')->nullable()->after('cheque_image');
        });
    }

    public function down(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->dropColumn('cheque_status');
        });
    }
};
