<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // The amount/method a collection would be recorded with —
            // locked in at send-time so the eventual submission can be
            // checked against exactly what the dealer was told over SMS,
            // not whatever the form happens to hold at submit time.
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');

            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamps();

            $table->index(['dealer_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_otps');
    }
};
