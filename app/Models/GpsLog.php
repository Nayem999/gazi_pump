<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasAudit;
use Database\Factories\GpsLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Raw GPS pings streamed from the mobile app while a Sales Executive is in
 * the field. Deliberately does NOT extend App\Models\BaseModel: this table
 * is high-frequency append-only telemetry (a handful of pings per user every
 * few minutes, all day), and wiring Spatie Activitylog (which BaseModel
 * bundles in) would double the write volume and bloat activity_log with
 * entries no one ever reads. Soft deletes + audit stamping are still useful
 * (an admin can clean up bad pings and the record shows who removed it), so
 * those two are composed directly instead.
 */
class GpsLog extends Model
{
    use HasAudit;

    /** @use HasFactory<GpsLogFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'lat',
        'lng',
        'recorded_at',
        'accuracy',
        'speed',
        'battery_level',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'recorded_at' => 'datetime',
            'accuracy' => 'decimal:2',
            'speed' => 'decimal:2',
            'battery_level' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
