<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends BaseModel
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sales_team_id',
        'name',
        'sku',
        'price',
        'description',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function salesTeam(): BelongsTo
    {
        return $this->belongsTo(SalesTeam::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Restricts the list to products belonging to the viewer's own sales
     * team, plus any team-less (company-wide) product. A viewer with no
     * team assigned (e.g. Super Admin, General Manager) sees everything
     * unfiltered — the filtering only kicks in once a user is actually
     * under a team, per how sales teams are used across the app.
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        if (! $viewer?->sales_team_id) {
            return $query;
        }

        return $query->where(
            fn (Builder $q) => $q->where('sales_team_id', $viewer->sales_team_id)->orWhereNull('sales_team_id')
        );
    }
}
