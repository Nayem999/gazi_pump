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
            $table->string('external_reference')->nullable()->unique()->after('status');
            $table->string('tally_guid')->nullable()->after('external_reference');
            $table->string('tally_voucher_number')->nullable()->after('tally_guid');
            $table->string('sync_status')->default('not_synced')->after('tally_voucher_number');
            $table->text('sync_error')->nullable()->after('sync_status');
            $table->timestamp('synced_at')->nullable()->after('sync_error');

            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->dropColumn(['external_reference', 'tally_guid', 'tally_voucher_number', 'sync_status', 'sync_error', 'synced_at']);
        });
    }
};
