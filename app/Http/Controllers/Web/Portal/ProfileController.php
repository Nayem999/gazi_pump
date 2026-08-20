<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('portal.profile.edit', [
            'account' => $request->user('customer'),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $account = $request->user('customer');
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $account->update($data);

        return redirect()->route('portal.profile.edit')->with('success', 'Profile updated successfully.');
    }
}
