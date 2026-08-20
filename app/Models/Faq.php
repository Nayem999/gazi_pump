<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Faq extends BaseModel
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    /**
     * Cache key for the portal's public FAQ list (Module 23 hardening) —
     * that page has zero per-visitor variance (no search/filter/pagination),
     * so it's cached forever and busted here on any write, the same pattern
     * Setting::current() already uses.
     */
    public const PORTAL_INDEX_CACHE_KEY = 'portal.faqs.index';

    protected $fillable = [
        'question',
        'answer',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
        static::restored(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
    }
}
