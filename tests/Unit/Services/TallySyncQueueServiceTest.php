<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Enums\TallySyncErrorCode;
use App\Models\SyncQueue;
use App\Services\TallySyncQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TallySyncQueueService
    {
        return app(TallySyncQueueService::class);
    }

    public function test_enqueue_is_idempotent_on_external_reference(): void
    {
        $service = $this->service();
        $service->enqueue(TallyEntityType::Dealer, 1, SyncDirection::PullFromTally, 'SFA-DEALER-1', ['name' => 'A']);
        $service->enqueue(TallyEntityType::Dealer, 1, SyncDirection::PullFromTally, 'SFA-DEALER-1', ['name' => 'B — a retried request']);

        $this->assertDatabaseCount('sync_queues', 1);
        $this->assertDatabaseHas('sync_queues', ['external_reference' => 'SFA-DEALER-1', 'status' => SyncStatus::Pending->value]);
    }

    public function test_claim_next_marks_rows_processing_and_increments_attempt_count(): void
    {
        SyncQueue::factory()->count(3)->create(['status' => SyncStatus::Pending]);

        $claimed = $this->service()->claimNext(2);

        $this->assertCount(2, $claimed);
        $claimed->each(function (SyncQueue $item) {
            $this->assertSame(SyncStatus::Processing, $item->fresh()->status);
            $this->assertSame(1, $item->fresh()->attempt_count);
        });
        $this->assertSame(1, SyncQueue::where('status', SyncStatus::Pending)->count());
    }

    public function test_claim_next_never_returns_a_row_already_claimed(): void
    {
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Pending]);

        $first = $this->service()->claimNext(10);
        $second = $this->service()->claimNext(10);

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
        $this->assertSame($item->id, $first->first()->id);
    }

    public function test_mark_success_completes_the_row_and_writes_a_sync_log(): void
    {
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Processing, 'attempt_count' => 1]);

        $this->service()->markSuccess($item, ['ok' => true], 'TALLY-GUID-1', 'SV-100');

        $item->refresh();
        $this->assertSame(SyncStatus::Success, $item->status);
        $this->assertNotNull($item->completed_at);
        $this->assertDatabaseHas('sync_logs', [
            'external_reference' => $item->external_reference,
            'status' => 'success',
            'tally_guid' => 'TALLY-GUID-1',
            'tally_voucher_number' => 'SV-100',
        ]);
    }

    public function test_mark_failed_with_a_retryable_error_schedules_a_retry_instead_of_failing(): void
    {
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Processing, 'attempt_count' => 1]);

        $this->service()->markFailed($item, TallySyncErrorCode::TallyOffline, 'Connection refused');

        $item->refresh();
        $this->assertSame(SyncStatus::Retry, $item->status);
        $this->assertNotNull($item->next_attempt_at);
        $this->assertNull($item->completed_at);
    }

    public function test_mark_failed_with_a_non_retryable_error_fails_immediately_even_on_the_first_attempt(): void
    {
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Processing, 'attempt_count' => 1]);

        $this->service()->markFailed($item, TallySyncErrorCode::ProductMappingMissing, 'No mapping for product 42');

        $item->refresh();
        $this->assertSame(SyncStatus::Failed, $item->status);
        $this->assertNotNull($item->completed_at);
    }

    public function test_mark_failed_stops_retrying_once_max_attempts_are_reached(): void
    {
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Processing, 'attempt_count' => 5]);

        $this->service()->markFailed($item, TallySyncErrorCode::TallyOffline, 'Still offline');

        $this->assertSame(SyncStatus::Failed, $item->fresh()->status);
    }
}
