<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A separate token table for the 'customers' password broker, mirroring the
 * default password_reset_tokens table exactly — kept separate (rather than
 * sharing password_reset_tokens with the staff 'users' broker) because
 * Laravel's DatabaseTokenRepository looks tokens up by email alone with no
 * broker column, and customer_accounts/users are different tables that
 * could otherwise collide on a shared email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
    }
};
