<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically stamps created_by/updated_by/deleted_by on every write,
 * using whichever guard is currently authenticated (web, customer, or sanctum).
 */
trait HasAudit
{
    public static function bootHasAudit(): void
    {
        // created_by/updated_by/deleted_by carry a foreign key to `users`,
        // but Auth::user() can also resolve to a different guard's model
        // (e.g. CustomerAccount on the customer portal) — stamping that
        // model's id would violate the FK, so only a genuine User counts.
        static::creating(function ($model): void {
            if ((Auth::user() instanceof User) && ! $model->isDirty('created_by')) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model): void {
            if (Auth::user() instanceof User) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model): void {
            if ((Auth::user() instanceof User) && method_exists($model, 'trashed')) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
