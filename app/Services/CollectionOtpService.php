<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\CollectionOtp;
use App\Models\Dealer;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * The "Secure Dealer Collection" OTP flow: an executive requests an OTP for
 * a dealer/amount/payment-method combination, the dealer is told the code
 * (by SMS when a gateway is configured, otherwise the code is handed back
 * directly for on-screen "demo mode" display), and the executive re-enters
 * whatever the dealer read back to them before the collection can actually
 * be submitted.
 */
class CollectionOtpService
{
    public function __construct(private readonly SmsGatewayService $sms) {}

    /**
     * @return array{otp_id: int, sent: bool, demo_code: string|null, expires_at: string}
     */
    public function send(User $user, int $dealerId, float $amount, PaymentMethod $paymentMethod): array
    {
        $dealer = Dealer::findOrFail($dealerId);
        $code = (string) random_int(100000, 999999);
        $expiryMinutes = (int) Setting::current()->collection_otp_expiry_minutes;

        $otp = CollectionOtp::create([
            'dealer_id' => $dealer->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod->value,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        $message = sprintf(
            '%s: A collection of %s is being recorded by %s. Your OTP is %s. Valid for %d minutes.',
            config('app.name'),
            number_format($amount, 2),
            $user->name,
            $code,
            $expiryMinutes,
        );

        $sent = $dealer->phone ? $this->sms->send($dealer->phone, $message) : false;

        return [
            'otp_id' => $otp->id,
            'sent' => $sent,
            // Only surfaced when a real SMS wasn't actually sent — never
            // alongside a genuine send, so the code isn't needlessly
            // exposed in the API/HTTP response once real delivery works.
            'demo_code' => $sent ? null : $code,
            'expires_at' => $otp->expires_at->toIso8601String(),
        ];
    }

    /**
     * Verifies the code against a specific pending OTP, scoped to the
     * requesting user and to the exact dealer/amount/method it was issued
     * for — an executive can't send an OTP for one amount and then use it
     * to confirm a different one.
     */
    public function verify(int $otpId, User $user, string $code, int $dealerId, float $amount, PaymentMethod $paymentMethod): CollectionOtp
    {
        $otp = CollectionOtp::where('id', $otpId)
            ->where('user_id', $user->id)
            ->where('dealer_id', $dealerId)
            ->first();

        if (! $otp || (float) $otp->amount !== $amount || $otp->payment_method !== $paymentMethod) {
            throw ValidationException::withMessages(['otp' => 'This OTP does not match the collection being submitted. Please request a new one.']);
        }

        if ($otp->isVerified()) {
            throw ValidationException::withMessages(['otp' => 'This OTP has already been used.']);
        }

        if ($otp->isExpired()) {
            throw ValidationException::withMessages(['otp' => 'This OTP has expired. Please request a new one.']);
        }

        if ($otp->attempts >= 5) {
            throw ValidationException::withMessages(['otp' => 'Too many incorrect attempts. Please request a new OTP.']);
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            throw ValidationException::withMessages(['otp' => 'Incorrect OTP.']);
        }

        $otp->update(['verified_at' => now()]);

        return $otp;
    }
}
