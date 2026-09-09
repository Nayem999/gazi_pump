<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\SyncStatus;
use App\Models\SyncQueue;
use App\Services\TallyRetryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TallyRetryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TallyRetryService
    {
        return app(TallyRetryService::class);
    }

    public function test_backoff_delays_match_the_spec_table(): void
    {
        $service = $this->service();
        $now = Carbon::now();

        $this->assertTrue($service->nextAttemptAt(1)->between($now->copy()->subSecond(), $now->copy()->addSecond()));
        $this->assertTrue($service->nextAttemptAt(2)->between($now->copy()->addMinutes(1)->subSecond(), $now->copy()->addMinutes(1)->addSecond()));
        $this->assertTrue($service->nextAttemptAt(3)->between($now->copy()->addMinutes(5)->subSecond(), $now->copy()->addMinutes(5)->addSecond()));
        $this->assertTrue($service->nextAttemptAt(4)->between($now->copy()->addMinutes(15)->subSecond(), $now->copy()->addMinutes(15)->addSecond()));
        $this->assertTrue($service->nextAttemptAt(5)->between($now->copy()->addMinutes(30)->subSecond(), $now->copy()->addMinutes(30)->addSecond()));
        $this->assertNull($service->nextAttemptAt(6));
    }

    public function test_has_attempts_remaining_is_false_once_five_attempts_have_been_made(): void
    {
        $service = $this->service();

        $this->assertTrue($service->hasAttemptsRemaining(4));
        $this->assertFalse($service->hasAttemptsRemaining(5));
    }

    public function test_process_due_releases_only_retry_rows_whose_next_attempt_has_arrived(): void
    {
        $due = SyncQueue::factory()->create(['status' => SyncStatus::Retry, 'next_attempt_at' => Carbon::now()->subMinute()]);
        $notYetDue = SyncQueue::factory()->create(['status' => SyncStatus::Retry, 'next_attempt_at' => Carbon::now()->addMinutes(10)]);
        $unrelated = SyncQueue::factory()->create(['status' => SyncStatus::Pending]);

        $count = $this->service()->processDue();

        $this->assertSame(1, $count);
        $this->assertSame(SyncStatus::Pending, $due->fresh()->status);
        $this->assertSame(SyncStatus::Retry, $notYetDue->fresh()->status);
        $this->assertSame(SyncStatus::Pending, $unrelated->fresh()->status);
    }

    public function test_manual_retry_resets_the_attempt_counter_and_clears_the_error(): void
    {
        $item = SyncQueue::factory()->create([
            'status' => SyncStatus::Failed,
            'attempt_count' => 5,
            'error_message' => 'No mapping for product 42',
        ]);

        $this->service()->manualRetry($item);

        $item->refresh();
        $this->assertSame(SyncStatus::Pending, $item->status);
        $this->assertSame(0, $item->attempt_count);
        $this->assertNull($item->error_message);
    }
}
