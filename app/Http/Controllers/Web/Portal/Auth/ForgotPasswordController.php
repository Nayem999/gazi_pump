<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\ForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.forgot-password');
    }

    /**
     * Always shows the same generic message regardless of whether the email
     * matched an account — Password::broker()->sendResetLink() itself
     * differentiates ('passwords.sent' vs 'passwords.user'), but surfacing
     * that difference to the visitor would let them enumerate registered
     * customer emails.
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::broker('customers')->sendResetLink($request->only('email'));

        return redirect()->route('portal.password.request')
            ->with('success', "If an account exists for that email, we've sent a password reset link.");
    }
}
