<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    public function current(): Setting
    {
        return Setting::current();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, ?UploadedFile $logo = null, ?UploadedFile $favicon = null): Setting
    {
        $settings = Setting::current();

        if ($logo) {
            if ($settings->company_logo) {
                Storage::disk('public')->delete($settings->company_logo);
            }
            $data['company_logo'] = $logo->store('settings', 'public');
        }

        if ($favicon) {
            if ($settings->company_favicon) {
                Storage::disk('public')->delete($settings->company_favicon);
            }
            $data['company_favicon'] = $favicon->store('settings', 'public');
        }

        $settings->update($data);

        return $settings->refresh();
    }
}
