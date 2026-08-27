<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProductCategory extends BaseModel
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    /**
     * All categories ordered by name, with each sub-category placed
     * immediately after its parent — for select dropdowns where the
     * hierarchy should read naturally instead of an alphabetically
     * shuffled flat list.
     *
     * @return Collection<int, ProductCategory>
     */
    public static function orderedForSelect(): Collection
    {
        $all = static::orderBy('name')->get();

        return $all->whereNull('parent_id')->flatMap(
            fn (self $parent) => collect([$parent])->concat($all->where('parent_id', $parent->id))
        )->values();
    }
}
