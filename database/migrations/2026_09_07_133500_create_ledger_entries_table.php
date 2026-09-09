<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per Tally voucher affecting a dealer's ledger — Sales,
     * Receipt, Credit Note, Debit Note, Journal, whatever voucher_type
     * Tally itself uses. Tally is the sole source of truth (pulled and
     * upserted by tally_guid — see TallyLedgerSyncService, Phase 5), so
     * this is a synced cache, not a business record: no soft
     * deletes/created_by/updated_by, same reasoning as ProductStock.
     */
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->string('tally_guid')->unique();
            $table->date('voucher_date');
            $table->string('voucher_type');
            $table->string('voucher_number')->nullable();
            $table->decimal('debit_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->text('narration')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['dealer_id', 'voucher_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
