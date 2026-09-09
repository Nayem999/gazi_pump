<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Enums\TallySyncErrorCode;
use Database\Factories\SyncQueueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One working-set row per pending/in-flight Tally sync job. No soft
 * deletes/audit columns of its own — it's a queue entry, not a business
 * record; its permanent history (including terminal outcomes) is written to
 * SyncLog instead, matching how OrderItem/TargetItem stay wholly-owned
 * plain models.
 */
class SyncQueue extends Model
{
    /** @use HasFactory<SyncQueueFactory> */
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'direction',
        'external_reference',
        'payload',
        'status',
        'attempt_count',
        'last_attempt_at',
        'next_attempt_at',
        'response',
        'error_code',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => TallyEntityType::class,
            'entity_id' => 'integer',
            'direction' => SyncDirection::class,
            'payload' => 'array',
            'status' => SyncStatus::class,
            'attempt_count' => 'integer',
            'last_attempt_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'response' => 'array',
            'error_code' => TallySyncErrorCode::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * What a completed master pull actually changed, phrased for the Sync
     * Queue screen.
     *
     * "Success" on its own is misleading for a master pull: it can mean
     * everything mapped, or that Tally answered with rows nothing could be
     * done with. The counts come from TallyMasterSyncService, folded into
     * `response['applied']` when the result was recorded.
     *
     * @return array<int, array{label: string, tone: string}>
     */
    public function outcomeChips(): array
    {
        // Checked before the counts: a deliberately skipped pull still runs
        // through the master sync and so still carries an all-zero
        // `applied`, which would otherwise render as "nothing to map" —
        // true, but hiding the one thing the operator needs to know, which
        // is that a setting is missing.
        if (isset($this->response['skipped'])) {
            return [['label' => 'Skipped', 'tone' => 'warning']];
        }

        // The other direction: a master created inside Tally. Worth its own
        // chip because it is the only outcome in this system that changed
        // the customer's accounting data rather than reading it.
        if (array_key_exists('mapped', $this->response ?? [])) {
            return $this->response['mapped']
                ? [['label' => 'created in Tally', 'tone' => 'primary']]
                : [['label' => 'created in Tally, not mapped here', 'tone' => 'danger']];
        }

        $applied = $this->response['applied'] ?? null;

        if (! is_array($applied)) {
            // Not a master pull, or one recorded before outcomes were
            // being captured at all.
            return [];
        }

        $counts = [
            ['label' => 'linked', 'tone' => 'success', 'value' => (int) ($applied['linked'] ?? 0)],
            ['label' => 'imported', 'tone' => 'info', 'value' => count($applied['imported'] ?? [])],
            ['label' => 'conflicts', 'tone' => 'warning', 'value' => count($applied['conflicts'] ?? [])],
            ['label' => 'unmatched', 'tone' => 'danger', 'value' => count($applied['unmatched'] ?? [])],
            ['label' => 'deleted here', 'tone' => 'danger', 'value' => count($applied['deleted_here'] ?? [])],
            ['label' => 'already mapped', 'tone' => 'secondary', 'value' => (int) ($applied['already'] ?? 0)],
        ];

        $chips = [];

        foreach ($counts as $count) {
            if ($count['value'] > 0) {
                $chips[] = ['label' => $count['value'].' '.$count['label'], 'tone' => $count['tone']];
            }
        }

        // A pull that matched nothing at all is itself the finding — say so
        // rather than leaving the column blank next to a green "Success".
        return $chips === [] ? [['label' => 'nothing to map', 'tone' => 'secondary']] : $chips;
    }

    /**
     * The names behind the counts above, for the row's tooltip — a
     * conflict or an unmatched record is only actionable if you know which
     * one it was.
     */
    public function outcomeDetail(): string
    {
        if (isset($this->response['skipped'])) {
            return (string) $this->response['skipped'];
        }

        $applied = $this->response['applied'] ?? null;

        if (! is_array($applied)) {
            return '';
        }

        $parts = [];

        foreach (['imported', 'conflicts', 'unmatched', 'deleted_here'] as $key) {
            $names = array_filter((array) ($applied[$key] ?? []), 'is_string');

            if ($names !== []) {
                $parts[] = ucfirst(str_replace('_', ' ', $key)).': '.implode(', ', $names);
            }
        }

        return implode(' | ', $parts);
    }
}
