<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('sms_gateway_enabled')->default(false);
            $table->string('sms_gateway_provider')->nullable();
            $table->string('sms_gateway_api_url')->nullable();
            $table->string('sms_gateway_api_key')->nullable();
            $table->string('sms_gateway_sender_id')->nullable();
            // 'sms' | 'whatsapp' — a hint forwarded to the gateway payload,
            // for providers whose single API sends either depending on this
            // field (App\Enums\SmsChannel).
            $table->string('sms_channel')->default('sms');
            $table->unsignedSmallInteger('collection_otp_expiry_minutes')->default(10);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'sms_gateway_enabled',
                'sms_gateway_provider',
                'sms_gateway_api_url',
                'sms_gateway_api_key',
                'sms_gateway_sender_id',
                'sms_channel',
                'collection_otp_expiry_minutes',
            ]);
        });
    }
};
