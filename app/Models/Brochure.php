<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BrochureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Brochure extends BaseModel
{
    /** @use HasFactory<BrochureFactory> */
    use HasFactory;

    /**
     * Cache key for the portal's public brochure list (Module 23 hardening)
     * — see Faq::PORTAL_INDEX_CACHE_KEY for the rationale.
     */
    public const PORTAL_INDEX_CACHE_KEY = 'portal.brochures.index';

    protected $fillable = [
        'title',
        'file',
        'cover_image',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function fileUrl(): string
    {
        return asset('storage/'.$this->file);
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
        static::restored(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
    }
}
