<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SettingsService
    {
        return app(SettingsService::class);
    }

    public function test_current_creates_a_singleton_row_matching_config_defaults(): void
    {
        $this->assertDatabaseCount('settings', 0);

        $settings = $this->service()->current();

        $this->assertDatabaseCount('settings', 1);
        $this->assertSame(config('app.name'), $settings->company_name);
        $this->assertSame(config('sfa.attendance.late_grace_minutes'), $settings->attendance_late_grace_minutes);
        $this->assertSame(config('sfa.notifications.low_performance_grades'), $settings->low_performance_grades);
    }

    public function test_current_is_idempotent(): void
    {
        $first = $this->service()->current();
        $second = $this->service()->current();

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('settings', 1);
    }

    public function test_update_persists_changes_to_the_singleton_row(): void
    {
        $this->service()->update(['attendance_late_grace_minutes' => 45]);

        $this->assertSame(45, Setting::current()->attendance_late_grace_minutes);
        $this->assertDatabaseCount('settings', 1);
    }

    public function test_update_replaces_the_logo_and_deletes_the_previous_file(): void
    {
        Storage::fake('public');

        $first = UploadedFile::fake()->image('logo1.png');
        $this->service()->update([], $first);
        $firstPath = Setting::current()->company_logo;
        Storage::disk('public')->assertExists($firstPath);

        $second = UploadedFile::fake()->image('logo2.png');
        $this->service()->update([], $second);
        $secondPath = Setting::current()->company_logo;

        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }
}
