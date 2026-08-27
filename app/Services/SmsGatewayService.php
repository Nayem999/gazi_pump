<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * A thin, provider-agnostic HTTP client for whatever SMS/WhatsApp gateway
 * is configured in Settings — every gateway's actual request/response
 * contract differs, so the payload shape here (api_key/sender_id/to/message)
 * is a reasonable common default, not a specific provider's real API. When
 * no gateway is configured (or a send fails), callers fall back to "demo
 * mode": the message is only logged, never actually delivered.
 */
class SmsGatewayService
{
    public function send(string $phone, string $message): bool
    {
        $settings = Setting::current();

        if (! $settings->sms_gateway_enabled || ! $settings->sms_gateway_api_url) {
            Log::info('SMS gateway not configured — demo mode.', ['to' => $phone]);

            return false;
        }

        try {
            $response = Http::timeout(10)->asForm()->post($settings->sms_gateway_api_url, [
                'api_key' => $settings->sms_gateway_api_key,
                'sender_id' => $settings->sms_gateway_sender_id,
                'channel' => $settings->sms_channel->value,
                'to' => $phone,
                'message' => $message,
            ]);

            Log::info('SMS gateway send attempted.', ['to' => $phone, 'status' => $response->status()]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('SMS gateway send failed: '.$e->getMessage(), ['to' => $phone]);

            return false;
        }
    }
}
