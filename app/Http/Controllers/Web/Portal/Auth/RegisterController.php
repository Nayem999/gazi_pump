<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\CustomerRegisterRequest;
use App\Models\CustomerAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.register');
    }

    public function store(CustomerRegisterRequest $request): RedirectResponse
    {
        $account = CustomerAccount::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => Hash::make($request->validated('password')),
        ]);

        Auth::guard('customer')->login($account);

        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('success', 'Welcome! Your account has been created.');
    }
}
