<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Restricts a `user_id`-owned model (no dealer of its own — Target,
 * Attendance, Visit, GpsLog) to what the viewer is allowed to see: their own
 * records only when Sales Executive is their sole role, their own
 * territories' executives when they have territories assigned, or
 * everything otherwise (Super Admin, General Manager, and Sales/Area
 * Manager — a `sales_team_id` doesn't gate this: it's a product-catalog
 * dimension in this app, per Product::scopeVisibleTo(), not a "which
 * executives does this manager oversee" one, so a General Manager who
 * happens to have a team assigned for that purpose still sees every
 * executive's records here). Mirrors Order/CollectionEntry's own
 * scopeVisibleTo(), which uses the same shape but via `dealer.territory_id`
 * instead of `user.territories` since those models have a dealer.
 */
trait HasVisibilityScope
{
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->isSalesExecutiveOnly()) {
            return $query->where('user_id', $viewer->id);
        }

        $territoryIds = $viewer->territories->pluck('id')->all();

        return $territoryIds === []
            ? $query
            : $query->whereHas('user.territories', fn ($q) => $q->whereIn('territories.id', $territoryIds));
    }
}
