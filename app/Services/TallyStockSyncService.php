<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;

/**
 * Applies a stock snapshot pushed by the Sync Agent (see
 * tally-sync-agent/src/stockSnapshot.js). Tally-owned columns
 * (opening/in/out/closing/available_qty) are only ever written here — this
 * is their single writer in the app. reserved_qty/allocated_qty (SFA's own
 * overlay, see DepotAllocationService) are never touched.
 *
 * Two modes, decided by whether a row carries a godown:
 *
 *  - **Per-godown** — a row with `godown_tally_guid` is matched to its Depot
 *    by GUID (spec §44's stable-identifier rule; never by name). This is the
 *    intended long-term shape and needs a Tally that can serve item×godown.
 *
 *  - **Single-depot** — a row with no godown is attributed to the depot named
 *    by `sfa.tally.default_depot_code`. This is what the live customer runs
 *    on: their TallyPrime 7.1 does not expose godown in voucher or
 *    collection exports at all, and its two built-in reports give per-godown
 *    and per-item totals separately, which cannot be combined into per-cell
 *    quantities (see docs/tally-sfa-integration.md). Attributing everything
 *    to one depot is an explicit, configured business decision rather than a
 *    guess — and because that mode carries no movement data, it writes only
 *    the closing position and leaves opening/in/out alone instead of
 *    zeroing whatever was there.
 *
 * A row whose product (or depot) can't be resolved is skipped and counted,
 * never guessed at.
 */
class TallyStockSyncService
{
    /**
     * @param  array<int, array{stock_item_name?: string, godown_name?: ?string, stock_item_tally_guid?: ?string, godown_tally_guid?: ?string, opening_qty?: float, in_qty?: float, out_qty?: float, closing_qty: float}>  $rows
     * @return array{applied: int, skipped: int}
     */
    public function applySnapshot(array $rows): array
    {
        return DB::transaction(function () use ($rows) {
            $applied = 0;
            $skipped = 0;
            $defaultDepot = $this->defaultDepot();

            foreach ($rows as $row) {
                $singleDepotRow = empty($row['godown_tally_guid']);

                $depot = $singleDepotRow
                    ? $defaultDepot
                    : Depot::where('tally_guid', $row['godown_tally_guid'])->first();

                $product = ! empty($row['stock_item_tally_guid'])
                    ? Product::where('tally_guid', $row['stock_item_tally_guid'])->first()
                    : null;

                if (! $depot || ! $product) {
                    $skipped++;

                    continue;
                }

                $closing = (float) $row['closing_qty'];

                // Single-depot mode has no movement figures to report, so it
                // updates the closing position only — writing 0s for
                // opening/in/out would present "unknown" as "none" and
                // destroy any real values a per-godown sync had stored.
                $attributes = $singleDepotRow
                    ? [
                        'closing_qty' => $closing,
                        'available_qty' => $closing,
                        'last_synced_at' => now(),
                    ]
                    : [
                        'opening_qty' => $row['opening_qty'] ?? 0,
                        'in_qty' => $row['in_qty'] ?? 0,
                        'out_qty' => $row['out_qty'] ?? 0,
                        'closing_qty' => $closing,
                        'available_qty' => $closing,
                        'last_synced_at' => now(),
                    ];

                ProductStock::updateOrCreate(
                    ['depot_id' => $depot->id, 'product_id' => $product->id],
                    $attributes,
                );

                $applied++;
            }

            return ['applied' => $applied, 'skipped' => $skipped];
        });
    }

    /**
     * The depot that receives company-wide stock in single-depot mode.
     * Null when unconfigured or the code doesn't match a depot — in which
     * case godown-less rows are skipped and counted rather than silently
     * landing against an arbitrary depot.
     */
    private function defaultDepot(): ?Depot
    {
        $code = config('sfa.tally.default_depot_code');

        if (blank($code)) {
            return null;
        }

        return Depot::where('code', $code)->first();
    }
}
