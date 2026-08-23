<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitPlanStatus;
use Database\Factories\VisitPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitPlan extends BaseModel
{
    /** @use HasFactory<VisitPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dealer_id',
        'territory_id',
        'planned_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_date' => 'date:Y-m-d',
            'status' => VisitPlanStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * A plan whose date has passed without ever being fulfilled or
     * cancelled. Computed rather than stored so no scheduled job is needed
     * to keep it accurate.
     */
    public function isMissed(): bool
    {
        return $this->status === VisitPlanStatus::Planned && $this->planned_date->isPast();
    }
}
