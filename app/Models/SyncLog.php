<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use Database\Factories\SyncLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit trail of every sync attempt. Never updated or deleted
 * once written (spec: "do not delete audit records") — only created_at
 * exists, no updated_at.
 */
class SyncLog extends Model
{
    /** @use HasFactory<SyncLogFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'direction',
        'request_time',
        'response_time',
        'status',
        'external_reference',
        'tally_guid',
        'tally_voucher_number',
        'error_message',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => TallyEntityType::class,
            'entity_id' => 'integer',
            'direction' => SyncDirection::class,
            'request_time' => 'datetime',
            'response_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
