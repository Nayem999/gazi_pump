<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\SettingsNavigation;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The pinned sidebar footer, the Settings hub, and the Guide.
 *
 * The rule under test throughout: Settings is shown only to someone who
 * can actually open something inside it, while the Guide is shown to
 * everyone - it is the page that explains why a sidebar looks the way it
 * does, so the people seeing fewest menus need it most.
 */
class SidebarSettingsHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_an_admin_sees_settings_and_guide_pinned_in_the_sidebar(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('sidebar-footer', false)
            ->assertSee(route('settings.index'), false)
            ->assertSee(route('guide.index'), false);
    }

    public function test_an_executive_gets_the_guide_but_no_settings_link(): void
    {
        // A link that answers 403 is worse than no link.
        $this->actingAs($this->executive())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('guide.index'), false)
            ->assertDontSee(route('settings.index'), false);
    }

    public function test_the_settings_hub_lists_the_configuration_screens(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Company Profile')
            ->assertSee('Leave Entitlements')
            ->assertSee('Tally Integration')
            ->assertSee('Holidays');
    }

    public function test_an_executive_cannot_open_the_settings_hub(): void
    {
        $this->actingAs($this->executive())
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    public function test_the_hub_hides_sections_the_viewer_cannot_reach(): void
    {
        // A General Manager holds most, but not all, setup permissions -
        // whatever is missing must be absent rather than shown and refused.
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');

        $sections = SettingsNavigation::for($manager);
        $labels = array_column($sections, 'label');

        $this->assertNotEmpty($sections);

        foreach ($sections as $section) {
            foreach ($section['items'] as $item) {
                $this->assertTrue(
                    $manager->can($item['permission']),
                    "{$item['label']} was listed but {$manager->getRoleNames()->first()} cannot open it",
                );
            }
        }

        // Company Profile is Super Admin only, so it must not be offered.
        $this->assertNotContains('Company Profile', array_merge(
            ...array_map(fn ($s) => array_column($s['items'], 'label'), $sections)
        ));
        $this->assertNotEmpty($labels);
    }

    public function test_every_navigation_route_exists(): void
    {
        // Guards against a rename leaving a dead tile behind.
        foreach (SettingsNavigation::sections() as $section) {
            foreach ($section['items'] as $item) {
                $this->assertTrue(
                    app('router')->has($item['route']),
                    "Settings navigation points at a route that does not exist: {$item['route']}",
                );
            }
        }
    }

    public function test_the_guide_is_open_to_any_signed_in_user(): void
    {
        $this->actingAs($this->executive())
            ->get(route('guide.index'))
            ->assertOk()
            ->assertSee('guide')
            ->assertSee('User Guide');
    }

    public function test_the_guide_requires_signing_in(): void
    {
        $this->get(route('guide.index'))->assertRedirect(route('login'));
    }

    public function test_the_guide_tells_an_executive_they_have_no_setup_access(): void
    {
        // Rather than silently omitting the section and leaving them to
        // wonder where Settings went.
        $this->actingAs($this->executive())
            ->get(route('guide.index'))
            ->assertOk()
            ->assertSee('No configuration access');
    }

    public function test_the_guide_shows_an_admin_what_they_can_configure(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('guide.index'))
            ->assertOk()
            ->assertSee('You can configure')
            ->assertSee('Open Settings');
    }

    public function test_the_guide_shows_the_workflow_and_both_manuals(): void
    {
        $this->actingAs($this->executive())
            ->get(route('guide.index'))
            ->assertOk()
            ->assertSee('How the work flows')
            ->assertSee('User Guide')
            ->assertSee('Administrator Guide');
    }

    public function test_the_guide_renders_the_shipped_markdown(): void
    {
        // The page reads docs/*.md at request time, so there is one copy of
        // the documentation and the page cannot drift from the files.
        $response = $this->actingAs($this->superAdmin())->get(route('guide.index'))->assertOk();

        // Headings from each file, rendered rather than shown as raw markdown.
        $response->assertSee('Applying for leave')
            ->assertSee('Roles and permissions')
            ->assertDontSee('## Applying for leave', false);
    }

    public function test_every_contents_link_points_at_a_heading_that_exists(): void
    {
        // A contents list pointing at a missing anchor is the classic way
        // this kind of page rots.
        $html = $this->actingAs($this->superAdmin())
            ->get(route('guide.index'))
            ->assertOk()
            ->getContent();

        preg_match_all('/href="#([a-z0-9-]+)"/i', $html, $hrefs);
        preg_match_all('/id="([a-z0-9-]+)"/i', $html, $ids);

        $missing = array_values(array_diff(array_unique($hrefs[1]), $ids[1]));

        $this->assertSame([], $missing, 'Contents links with no matching heading: '.implode(', ', $missing));
        $this->assertNotEmpty($hrefs[1], 'the contents list rendered no links at all');
    }

    public function test_heading_ids_are_namespaced_per_guide(): void
    {
        // Both files use the same heading levels, so unprefixed ids would
        // collide and every contents link would land on whichever came
        // first.
        $html = $this->actingAs($this->superAdmin())->get(route('guide.index'))->getContent();

        $this->assertStringContainsString('id="user-attendance"', $html);
        $this->assertStringContainsString('id="admin-leave"', $html);
    }

    public function test_markdown_tables_get_their_own_scroller(): void
    {
        // Markdown tables are the first thing to push a phone sideways.
        $html = $this->actingAs($this->superAdmin())->get(route('guide.index'))->getContent();

        $this->assertStringContainsString('guide-table-scroll', $html);
        $this->assertSame(
            substr_count($html, '<div class="guide-table-scroll">'),
            substr_count($html, '</table></div>'),
            'every rendered table should be wrapped exactly once',
        );
    }

    public function test_embedded_html_in_the_markdown_is_stripped(): void
    {
        // The files are read off disk at request time; a page that renders
        // whatever HTML is in a file is one bad deployment from being an
        // injection point.
        $html = $this->actingAs($this->superAdmin())->get(route('guide.index'))->getContent();

        $this->assertStringNotContainsString('<script>alert', $html);
    }

    public function test_the_workflow_diagram_is_described_for_screen_readers(): void
    {
        $this->actingAs($this->executive())
            ->get(route('guide.index'))
            ->assertOk()
            ->assertSee('wf-title', false)
            ->assertSee('The Gazi Pump SFA workflow', false);
    }

    public function test_the_workflow_covers_all_four_tracks(): void
    {
        $response = $this->actingAs($this->executive())->get(route('guide.index'))->assertOk();

        foreach (['THE WORKING DAY', 'THE SALE', 'THE MONEY', 'PERFORMANCE'] as $lane) {
            $response->assertSee($lane, false);
        }
    }

    public function test_the_workflow_names_every_module_it_claims_to_cover(): void
    {
        // The diagram is the one place the whole system is shown at once,
        // so a module quietly missing from it is a real gap rather than a
        // cosmetic one.
        $response = $this->actingAs($this->executive())->get(route('guide.index'))->assertOk();

        $expected = [
            'Approved leave',      // Leave
            'Attendance',
            'Visit plan',          // the route for the day
            'GPS tracking',
            'Dealer visit',
            'Order',
            'Depot allocation',
            'Delivery',
            'Sales return',
            'Collection',
            'Cash handover',
            'Sync queue',
            'TallyPrime',
            'Daily achievement',
            'Monthly target',
        ];

        foreach ($expected as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_the_diagram_description_matches_what_it_draws(): void
    {
        // The <desc> is what a screen-reader user gets instead of the
        // picture; it drifting from the boxes would leave them with a
        // description of a diagram that no longer exists.
        $response = $this->actingAs($this->executive())->get(route('guide.index'))->assertOk();

        foreach (['visit plan', 'GPS', 'daily achievement', 'monthly target', 'activity log'] as $mentioned) {
            $response->assertSee($mentioned, false);
        }
    }

    public function test_the_company_profile_form_kept_its_route_name(): void
    {
        // Its URL moved to /settings/company so the hub could take
        // /settings; every existing link routes by name and must still work.
        $this->actingAs($this->superAdmin())
            ->get(route('settings.edit'))
            ->assertOk();

        // Asserted on the path, not the full URL - the host comes from
        // APP_URL and differs between environments.
        $this->assertSame('/settings/company', parse_url(route('settings.edit'), PHP_URL_PATH));
    }
}
