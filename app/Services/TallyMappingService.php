<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TallyEntityType;
use App\Enums\TallyMappingSyncStatus;
use App\Models\TallyMapping;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The single place that resolves/creates a tally_mappings row for a given
 * SFA record. Every caller must go through here rather than querying
 * tally_mappings directly, so "match by stable identifier, never by name
 * alone" (spec §6) is enforced in exactly one place.
 */
class TallyMappingService
{
    public function resolveBySfaId(TallyEntityType $entityType, int $sfaId): ?TallyMapping
    {
        return TallyMapping::query()
            ->where('entity_type', $entityType)
            ->where('sfa_id', $sfaId)
            ->first();
    }

    public function resolveByTallyGuid(TallyEntityType $entityType, string $tallyGuid): ?TallyMapping
    {
        return TallyMapping::query()
            ->where('entity_type', $entityType)
            ->where('tally_guid', $tallyGuid)
            ->first();
    }

    /**
     * Idempotent: an existing mapping for this (entity_type, sfa_id) pair
     * is updated in place (its Tally-side details refreshed) rather than
     * duplicated. A GUID clash against a *different* sfa_id is left as a
     * Conflict for an admin to resolve rather than silently reassigned.
     */
    public function findOrCreateMapping(
        TallyEntityType $entityType,
        int $sfaId,
        ?string $tallyGuid = null,
        ?string $tallyName = null,
        ?string $tallyAlterId = null,
    ): TallyMapping {
        $existing = $this->resolveBySfaId($entityType, $sfaId);

        if ($tallyGuid !== null) {
            $guidOwner = $this->resolveByTallyGuid($entityType, $tallyGuid);

            if ($guidOwner && $existing && $guidOwner->id !== $existing->id) {
                $existing->update(['sync_status' => TallyMappingSyncStatus::Conflict]);

                return $existing;
            }
        }

        if ($existing) {
            $existing->update([
                'tally_guid' => $tallyGuid ?? $existing->tally_guid,
                'tally_name' => $tallyName ?? $existing->tally_name,
                'tally_alter_id' => $tallyAlterId ?? $existing->tally_alter_id,
                'last_synced_at' => now(),
                'sync_status' => TallyMappingSyncStatus::Synced,
            ]);

            return $existing->refresh();
        }

        return TallyMapping::create([
            'entity_type' => $entityType,
            'sfa_id' => $sfaId,
            'tally_guid' => $tallyGuid,
            'tally_name' => $tallyName,
            'tally_alter_id' => $tallyAlterId,
            'last_synced_at' => now(),
            'sync_status' => TallyMappingSyncStatus::Synced,
        ]);
    }

    /**
     * @param  array{entity_type?: string, sync_status?: string, search?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return TallyMapping::query()
            ->when($filters['entity_type'] ?? null, fn ($query, $type) => $query->where('entity_type', $type))
            ->when($filters['sync_status'] ?? null, fn ($query, $status) => $query->where('sync_status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('tally_name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
