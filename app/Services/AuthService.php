<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    /**
     * Verify credentials and issue a Sanctum token for the given device.
     *
     * Mirrors the same email+IP lockout the web (`App\Http\Requests\Auth\LoginRequest`)
     * and portal (`CustomerLoginRequest`) logins already had — the mobile API
     * previously relied only on the route's flat IP-based `throttle:6,1`,
     * which doesn't back off per-account the way the other two guards do.
     *
     * @return array{user: User, token: NewAccessToken}
     */
    public function login(string $email, string $password, string $deviceName, string $throttleKey): array
    {
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout(request()));

            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (! $user->status) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $token = $user->createToken($deviceName);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
