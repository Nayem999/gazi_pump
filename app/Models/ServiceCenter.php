<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceCenterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class ServiceCenter extends BaseModel
{
    /** @use HasFactory<ServiceCenterFactory> */
    use HasFactory;

    /**
     * Cache key for the portal's public service-center list (Module 23
     * hardening) — see Faq::PORTAL_INDEX_CACHE_KEY for the rationale.
     */
    public const PORTAL_INDEX_CACHE_KEY = 'portal.service-centers.index';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'lat',
        'lng',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function hasGps(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
        static::restored(fn () => Cache::forget(self::PORTAL_INDEX_CACHE_KEY));
    }
}
