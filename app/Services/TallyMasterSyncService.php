<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TallyEntityType;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Retailer;
use App\Models\TallyMapping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Turns a pulled Tally master list into real SFA mappings — the step that
 * was missing, and the reason "Sync Now" appeared to succeed while leaving
 * every dealer and product unmapped: the agent's data was recorded in
 * `sync_queues.response` and nothing ever read it.
 *
 * **Name is used only to bootstrap a mapping, never to resolve identity.**
 * Once a record has a `tally_guid`, every later sync matches on that GUID
 * alone (spec §44) — which is why this will not re-point an existing
 * mapping. On the first pull, though, the name is the only bridge between
 * the two systems, so an exact (trimmed, case-insensitive) name match
 * against a record that has no GUID yet is treated as the same thing.
 * Anything less certain is left for a human:
 *
 *   linked       — matched by name, GUID written, mapping row created
 *   already      — already carried this same GUID, nothing to do
 *   conflicts    — name matches a record that holds a DIFFERENT GUID
 *   imported     — existed only in Tally, so created here (see below)
 *   unmatched    — exists only in Tally and could not be created
 *   deleted_here — deleted in SFA but still live in Tally, so deliberately
 *                  not re-imported (that would mint a fresh duplicate on
 *                  every sync, forever)
 *
 * Records that exist only in Tally are imported, because otherwise a
 * sync leaves the operator exactly where they started. But a Tally
 * master carries a name and a GUID and nothing else, while SFA requires
 * a dealer phone, and a product price and category. Those are filled
 * with placeholders, so every imported record is created **inactive**:
 * it reaches no rep's device and no report until a human opens it, puts
 * the real values in and activates it. Importing them as live data
 * would put invented prices in front of the field team.
 */
class TallyMasterSyncService
{
    /** Obvious-on-sight filler for a NOT NULL column Tally cannot supply. */
    private const PLACEHOLDER_PHONE = '0000000000';

    /**
     * @param  array<int, array{name?: string, guid?: string}>  $rows
     * @return array{linked: int, already: int, imported: array<int, string>, conflicts: array<int, string>, unmatched: array<int, string>, deleted_here: array<int, string>}
     */
    public function applyPulledMasters(TallyEntityType $entityType, array $rows): array
    {
        $modelClass = $this->modelFor($entityType);

        if ($modelClass === null) {
            return ['linked' => 0, 'already' => 0, 'imported' => [], 'conflicts' => [], 'unmatched' => [], 'deleted_here' => []];
        }

        return DB::transaction(function () use ($entityType, $modelClass, $rows) {
            $linked = 0;
            $already = 0;
            $imported = [];
            $conflicts = [];
            $unmatched = [];
            $deletedHere = [];

            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                $guid = trim((string) ($row['guid'] ?? ''));

                if ($name === '' || $guid === '') {
                    continue;
                }

                // Already mapped by GUID: nothing to do, and nothing to
                // second-guess. Deliberately searched withTrashed() — a
                // record deleted here but still present in Tally would
                // otherwise fall through to "exists only in Tally" and be
                // re-imported as a fresh stub on every single sync.
                $holder = $modelClass::withTrashed()->where('tally_guid', $guid)->first();

                if ($holder) {
                    if ($holder->trashed()) {
                        $deletedHere[] = $name;
                    } else {
                        $already++;
                    }

                    continue;
                }

                /** @var Model|null $record */
                $record = $modelClass::query()->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();

                if (! $record) {
                    if ($created = $this->createFromTally($entityType, $name, $guid)) {
                        $this->writeMapping($entityType, (int) $created->id, $guid, $name);

                        $imported[] = $name;

                        continue;
                    }

                    $unmatched[] = $name;

                    continue;
                }

                if (filled($record->tally_guid)) {
                    // Same name, different GUID — could be a renamed Tally
                    // record or two genuinely different things. Not ours to
                    // decide silently.
                    $conflicts[] = $name;

                    continue;
                }

                $record->update(['tally_guid' => $guid]);

                $this->writeMapping($entityType, (int) $record->id, $guid, $name);

                $linked++;
            }

            return [
                'linked' => $linked,
                'already' => $already,
                'imported' => $imported,
                'conflicts' => $conflicts,
                'unmatched' => $unmatched,
                'deleted_here' => $deletedHere,
            ];
        });
    }


    /**
     * Records the mapping, surviving rows left behind by earlier syncs.
     *
     * tally_mappings is unique on BOTH (entity_type, sfa_id) and
     * (entity_type, tally_guid), and TallyMapping soft-deletes — so a
     * trashed row still occupies both keys while being invisible to a
     * default-scoped updateOrCreate. That combination turned a re-sync
     * after any mapping deletion into a 1062 duplicate-key 500, seen live.
     * So look on both keys withTrashed(), restore whatever is there, and
     * only insert when neither key is taken.
     */
    private function writeMapping(TallyEntityType $entityType, int $sfaId, string $guid, string $name): void
    {
        $existing = TallyMapping::withTrashed()
            ->where('entity_type', $entityType)
            ->where(fn ($query) => $query->where('sfa_id', $sfaId)->orWhere('tally_guid', $guid))
            ->get();

        // A GUID and an SFA id that currently sit on two different rows
        // cannot both be kept: this pull is re-pointing one of them, and
        // the caller has already established no live record holds the
        // GUID. Drop the stale rows and write one clean mapping.
        if ($existing->count() > 1) {
            TallyMapping::withTrashed()->whereIn('id', $existing->pluck('id'))->forceDelete();
            $existing = collect();
        }

        $attributes = [
            'entity_type' => $entityType,
            'sfa_id' => $sfaId,
            'tally_guid' => $guid,
            'tally_name' => $name,
            'last_synced_at' => now(),
            'deleted_at' => null,
        ];

        if ($mapping = $existing->first()) {
            $mapping->forceFill($attributes)->save();

            return;
        }

        TallyMapping::create($attributes);
    }

    /**
     * Creates the SFA side of a Tally master that has no counterpart here.
     *
     * Everything SFA requires but Tally's master list does not carry is a
     * placeholder, and the record is inactive, so it is unmistakably a stub
     * awaiting a human rather than usable business data. Returns null when
     * a stub cannot be made honestly - a Product with no product category
     * to file it under, for instance.
     */
    private function createFromTally(TallyEntityType $entityType, string $name, string $guid): ?Model
    {
        return match ($entityType) {
            TallyEntityType::Dealer => Dealer::create([
                'dealer_code' => $this->stubCode('TLY-D', Dealer::class, 'dealer_code'),
                'name' => $name,
                'phone' => self::PLACEHOLDER_PHONE,
                'tally_guid' => $guid,
                'status' => false,
            ]),
            TallyEntityType::Retailer => Retailer::create([
                'name' => $name,
                'phone' => self::PLACEHOLDER_PHONE,
                'dealer_id' => Dealer::query()->orderBy('id')->value('id'),
                'tally_guid' => $guid,
                'status' => false,
            ]),
            TallyEntityType::Product => $this->createProductStub($name, $guid),
            TallyEntityType::Depot => Depot::create([
                'code' => $this->stubCode('TLY-DEP', Depot::class, 'code'),
                'name' => $name,
                'tally_guid' => $guid,
                'status' => false,
            ]),
            default => null,
        };
    }

    private function createProductStub(string $name, string $guid): ?Product
    {
        $categoryId = ProductCategory::query()->orderBy('id')->value('id');

        // products.category_id is NOT NULL, so with no category on file
        // there is no honest stub to create. Reported as unmatched instead.
        if ($categoryId === null) {
            return null;
        }

        return Product::create([
            'category_id' => $categoryId,
            'sku' => $this->stubCode('TLY-P', Product::class, 'sku'),
            'name' => $name,
            'price' => 0,
            'tally_guid' => $guid,
            'status' => false,
        ]);
    }

    /**
     * A unique placeholder identifier, since SFA's own codes/SKUs are
     * required and unique but have no Tally equivalent.
     *
     * @param  class-string<Model>  $modelClass
     */
    private function stubCode(string $prefix, string $modelClass, string $column): string
    {
        do {
            $candidate = $prefix.'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($modelClass::query()->where($column, $candidate)->exists());

        return $candidate;
    }

    /**
     * @return class-string<Model>|null
     */
    private function modelFor(TallyEntityType $entityType): ?string
    {
        return match ($entityType) {
            TallyEntityType::Dealer => Dealer::class,
            TallyEntityType::Retailer => Retailer::class,
            TallyEntityType::Product => Product::class,
            TallyEntityType::Depot => Depot::class,
            default => null,
        };
    }
}
