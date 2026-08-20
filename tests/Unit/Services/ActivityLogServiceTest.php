<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ActivityLogService
    {
        return app(ActivityLogService::class);
    }

    public function test_it_logs_and_lists_activity_from_model_changes(): void
    {
        $user = User::factory()->create();

        $rows = $this->service()->paginate([]);

        // Created + any subsequent updates on User (e.g. factory hooks) are
        // logged automatically via LogsActivity/HasAudit — at least the
        // creation itself must appear.
        $this->assertGreaterThanOrEqual(1, $rows->total());
        $this->assertTrue($rows->getCollection()->contains(fn ($activity) => $activity->subject_id === $user->id));
    }

    public function test_search_filters_by_description(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'Renamed User']);

        $matching = $this->service()->paginate(['search' => 'updated']);
        $nonMatching = $this->service()->paginate(['search' => 'a string that will never appear']);

        $this->assertGreaterThanOrEqual(1, $matching->total());
        $this->assertSame(0, $nonMatching->total());
    }

    public function test_event_filter_only_returns_matching_events(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'Changed Name']);

        $created = $this->service()->paginate(['event' => 'created']);
        $updated = $this->service()->paginate(['event' => 'updated']);

        $this->assertTrue($created->getCollection()->every(fn ($activity) => $activity->event === 'created'));
        $this->assertTrue($updated->getCollection()->every(fn ($activity) => $activity->event === 'updated'));
    }

    public function test_causer_filter_only_returns_activity_by_that_causer(): void
    {
        $actor = User::factory()->create();
        Auth::login($actor);

        $subject = User::factory()->create();
        $subject->update(['name' => 'Updated By Actor']);

        Auth::logout();

        $rows = $this->service()->paginate(['causer_id' => (string) $actor->id]);

        $this->assertTrue($rows->getCollection()->every(fn ($activity) => $activity->causer_id === $actor->id));
        $this->assertGreaterThanOrEqual(1, $rows->total());
    }

    public function test_log_names_and_events_return_distinct_values(): void
    {
        User::factory()->create();

        $this->assertContains('users', $this->service()->logNames()->all());
        $this->assertContains('created', $this->service()->events()->all());
    }
}
