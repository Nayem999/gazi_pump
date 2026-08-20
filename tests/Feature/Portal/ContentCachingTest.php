<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Brochure;
use App\Models\Faq;
use App\Models\ServiceCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Module 23 hardening: Faq/ServiceCenter/Brochure's public portal index pages
 * are cached forever and busted on save/delete/restore (see each model's
 * booted() method). These tests exist specifically to catch a mis-wired
 * cache-bust hook — the failure mode (stale content surviving an edit)
 * wouldn't be caught by any existing CRUD test, since those never render the
 * portal-facing page afterward.
 */
class ContentCachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_list_reflects_updates_after_cache_was_primed(): void
    {
        $faq = Faq::factory()->create(['question' => 'Original question?', 'is_published' => true]);

        $this->get(route('portal.faq.index'))->assertSee('Original question?');

        $faq->update(['question' => 'Updated question?']);

        $this->get(route('portal.faq.index'))
            ->assertSee('Updated question?')
            ->assertDontSee('Original question?');
    }

    public function test_service_center_list_reflects_deletion_after_cache_was_primed(): void
    {
        $serviceCenter = ServiceCenter::factory()->create(['name' => 'Soon Deleted Center', 'is_active' => true]);

        $this->get(route('portal.service-centers.index'))->assertSee('Soon Deleted Center');

        $serviceCenter->delete();

        $this->get(route('portal.service-centers.index'))->assertDontSee('Soon Deleted Center');
    }

    public function test_brochure_list_reflects_a_newly_created_brochure_after_cache_was_primed(): void
    {
        Brochure::factory()->create(['is_published' => true]);
        $this->get(route('portal.brochures.index'))->assertOk();

        $newBrochure = Brochure::factory()->create(['title' => 'Brand New Brochure', 'is_published' => true]);

        $this->get(route('portal.brochures.index'))->assertSee('Brand New Brochure');

        $newBrochure->delete();

        $this->get(route('portal.brochures.index'))->assertDontSee('Brand New Brochure');
    }
}
