<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per spec §7's Dealer field list. Tally's voucher-import XML addresses
     * ledgers by name, not GUID — the GUID is what proves identity/prevents
     * duplicate ledger creation (spec §6), but the Sync Agent still needs a
     * real name to put in a voucher's PARTYLEDGERNAME. Falls back to the
     * dealer's own `name` when a Tally ledger uses a different name (e.g.
     * "M/S Rahman Enterprise" vs SFA's "Rahman Enterprise" — spec §45).
     */
    public function up(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->string('tally_ledger_name')->nullable()->after('tally_guid');
        });
    }

    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn('tally_ledger_name');
        });
    }
};
