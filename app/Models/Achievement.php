<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PerformanceGrade;
use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A computed snapshot of one Target's actual performance — recalculated in
 * place (updateOrCreate keyed by target_id) by CalculateAchievementAction,
 * never edited directly. No soft deletes or audit columns of its own; the
 * target is the unit of audit/trash, not its derived achievement.
 */
class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected $fillable = [
        'target_id',
        'sales_achieved',
        'collection_achieved',
        'quantity_achieved',
        'sales_pct',
        'collection_pct',
        'quantity_pct',
        'overall_pct',
        'grade',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'sales_achieved' => 'decimal:2',
            'collection_achieved' => 'decimal:2',
            'quantity_achieved' => 'integer',
            'sales_pct' => 'decimal:2',
            'collection_pct' => 'decimal:2',
            'quantity_pct' => 'decimal:2',
            'overall_pct' => 'decimal:2',
            'grade' => PerformanceGrade::class,
            'calculated_at' => 'datetime',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }
}
