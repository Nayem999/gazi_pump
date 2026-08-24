<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Self-service "My Profile" for any logged-in staff user, regardless of
 * role — distinct from the Users module (app/Http/Controllers/Web/Admin/
 * UserController.php), which is permission-gated user management and lets
 * an admin edit someone else's roles/territories/status. This never
 * touches those, only the account owner's own name/email/phone/photo/
 * password.
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['photo', 'password']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        if ($photo = $request->file('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $photo->store('users', 'public');
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
