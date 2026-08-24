<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A singleton record — one row, edit-only, no index/create/delete — so this
 * uses a direct permission check instead of a Policy class, the same shape
 * as Reports/Territory Map/Live GPS.
 */
class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->can('settings.view'), 403);

        return view('settings.edit', [
            'settings' => $this->settings->current(),
        ]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $this->settings->update(
            $request->safe()->except(['company_logo', 'company_favicon']),
            $request->file('company_logo'),
            $request->file('company_favicon'),
        );

        return redirect()->route('settings.edit')->with('success', 'Settings updated successfully.');
    }
}
