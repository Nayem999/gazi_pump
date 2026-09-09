<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TallyApiFormat;
use Database\Factories\TallyConnectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class TallyConnection extends BaseModel
{
    /** @use HasFactory<TallyConnectionFactory> */
    use HasFactory;

    protected $fillable = [
        'connection_name',
        'tally_company_name',
        'tally_company_guid',
        'host',
        'port',
        'protocol',
        'api_format',
        'sync_agent_id',
        'sync_agent_token',
        'is_active',
        'last_heartbeat_at',
        'last_successful_sync_at',
    ];

    /**
     * The Sync Agent's bearer credential is a secret hashed at rest — it
     * must never leave the server in an admin response or a model-to-array
     * dump, only ever returned once at generation time (mirrors how a
     * Sanctum plainTextToken is only visible at issuance).
     */
    protected $hidden = [
        'sync_agent_token',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'api_format' => TallyApiFormat::class,
            'is_active' => 'boolean',
            'last_heartbeat_at' => 'datetime',
            'last_successful_sync_at' => 'datetime',
        ];
    }

    public function baseUrl(): string
    {
        return "{$this->protocol}://{$this->host}:{$this->port}";
    }

    /**
     * A connection is only considered "connected" while its Sync Agent has
     * heartbeated recently — computed rather than stored, same principle as
     * VisitPlan::isMissed(), so there's no scheduled job required just to
     * keep this accurate.
     */
    public function isOnline(): bool
    {
        if (! $this->last_heartbeat_at) {
            return false;
        }

        $staleAfterMinutes = (int) config('sfa.tally.heartbeat_stale_after_minutes', 5);

        return $this->last_heartbeat_at->greaterThan(Carbon::now()->subMinutes($staleAfterMinutes));
    }
}
