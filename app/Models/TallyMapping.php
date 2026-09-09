<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TallyEntityType;
use App\Enums\TallyMappingSyncStatus;
use Database\Factories\TallyMappingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TallyMapping extends BaseModel
{
    /** @use HasFactory<TallyMappingFactory> */
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'sfa_id',
        'tally_guid',
        'tally_name',
        'tally_alter_id',
        'last_synced_at',
        'sync_status',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => TallyEntityType::class,
            'sfa_id' => 'integer',
            'last_synced_at' => 'datetime',
            'sync_status' => TallyMappingSyncStatus::class,
        ];
    }
}
