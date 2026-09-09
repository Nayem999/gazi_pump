'use strict';

// stockReports.js parses Tally's built-in display reports; it does not
// require this module back, so there is no cycle.
const { rowsMatching } = require('./stockReports');

/**
 * Depot-wise stock quantities are a bulk snapshot, not a per-record
 * transaction — unlike Sales Orders/Collections, there is no single
 * "entity_id" a stock quantity belongs to (it's a godown+item pair), so
 * this bypasses the sync_queues job-claim mechanism entirely and pushes a
 * full snapshot straight to SFA's dedicated stock-sync endpoint on its own
 * interval. SFA resolves each row via tally_guid, never by name, and never
 * touches its own reserved_qty/allocated_qty overlay when applying it.
 */

/**
 * Pulls the row list for a given TDL object tag (e.g. "LEDGER", "GODOWN",
 * "STOCKITEM") out of a parsed Tally collection-export envelope.
 *
 * Confirmed live against the real "GDN Tally" instance (2026-09-07): rows
 * nest at ENVELOPE.BODY.DATA.COLLECTION.<TAG>, e.g.
 * `<ENVELOPE><BODY><DATA><COLLECTION><LEDGER NAME="Cash">...`. The two
 * shallower shapes below (rows directly under ENVELOPE, or under a bare
 * ENVELOPE.COLLECTION with no BODY/DATA) were this file's original guesses
 * before that verification — kept as a fallback in case a different Tally
 * version/report ever nests differently, but the confirmed shape is tried
 * first.
 */
function rowsFromEnvelope(parsed, rowTag) {
    const candidates = [
        parsed?.ENVELOPE?.BODY?.DATA?.COLLECTION,
        parsed?.ENVELOPE?.STOCKITEMGODOWN || parsed?.ENVELOPE?.[rowTag] ? parsed.ENVELOPE : null,
        parsed?.ENVELOPE?.COLLECTION,
    ];

    for (const container of candidates) {
        if (!container || container[rowTag] === undefined) {
            continue;
        }

        return Array.isArray(container[rowTag]) ? container[rowTag] : [container[rowTag]];
    }

    return [];
}

/**
 * Flattens Tally's Stock Item -> BATCHALLOCATIONS.LIST shape into one flat
 * row per (item, godown) pair, which is what SFA's product_stocks is keyed
 * on.
 *
 * Confirmed live (2026-09-07): the outer STOCKITEM rows and their
 * BATCHALLOCATIONS.LIST element are real and reachable. The child field
 * names inside a *populated* allocation are not yet confirmed — that
 * company has no stock, so every list came back empty — hence the
 * several accepted spellings per field below. Tally's own batch-allocation
 * objects conventionally use GODOWNNAME + OPENINGBALANCE/CLOSINGBALANCE
 * with INWARDSQTY/OUTWARDSQTY for movement, so those are tried first.
 *
 * An item with no allocations at all is skipped rather than emitted as a
 * godown-less row: product_stocks needs a real depot to key on, and
 * guessing one would corrupt Tally-owned figures.
 */
function toRows(parsed) {
    const rows = [];

    for (const item of rowsFromEnvelope(parsed, 'STOCKITEM')) {
        const itemName = item?.['@_NAME'] ?? textOf(item?.NAME);

        for (const allocation of allocationsOf(item)) {
            const godownName = firstTextOf(allocation, ['GODOWNNAME', 'GODOWN', 'DESTINATIONGODOWNNAME']);

            if (! godownName) {
                continue;
            }

            rows.push({
                stock_item_name: itemName,
                godown_name: godownName,
                opening_qty: firstNumberOf(allocation, ['OPENINGBALANCE', 'OPENINGQTY']),
                in_qty: firstNumberOf(allocation, ['INWARDSQTY', 'INWARDQTY', 'INQTY']),
                out_qty: firstNumberOf(allocation, ['OUTWARDSQTY', 'OUTWARDQTY', 'OUTQTY']),
                closing_qty: firstNumberOf(allocation, ['CLOSINGBALANCE', 'CLOSINGQTY', 'ACTUALQTY']),
            });
        }
    }

    return rows;
}

/**
 * BATCHALLOCATIONS.LIST is an empty string when Tally has nothing to put
 * in it (verified live), a single object for one entry, or an array — all
 * three normalize to a list here.
 */
function allocationsOf(item) {
    const list = item?.['BATCHALLOCATIONS.LIST'];

    if (! list || typeof list !== 'object') {
        return [];
    }

    return Array.isArray(list) ? list : [list];
}

function firstTextOf(row, keys) {
    for (const key of keys) {
        const value = textOf(row?.[key]);
        if (value !== '') {
            return value;
        }
    }

    return '';
}

function firstNumberOf(row, keys) {
    for (const key of keys) {
        if (row?.[key] !== undefined && row[key] !== null) {
            return numberOf(row[key]);
        }
    }

    return 0;
}

/**
 * Tally quantity fields come back as "123 Nos" / "13 PCS" — and, because
 * they carry a TYPE attribute, as { '@_TYPE': 'Quantity', '#text': '13 PCS' }
 * rather than a bare string. Unwrap first (a missed step that made every
 * quantity parse as 0 via String(object) === "[object Object]"), then strip
 * the unit suffix and keep the numeric part.
 */
function numberOf(value) {
    if (value === undefined || value === null) {
        return 0;
    }

    const raw = typeof value === 'object' ? value['#text'] : value;
    const match = String(raw ?? '').match(/-?[\d.]+/);

    return match ? parseFloat(match[0]) : 0;
}

/**
 * A field's text content — confirmed live: most FETCH fields come back as
 * `<GUID TYPE="String">the-value</GUID>`, which fast-xml-parser (with
 * ignoreAttributes:false) turns into `{ '@_TYPE': 'String', '#text':
 * 'the-value' }`, not a plain string.
 */
function textOf(value) {
    if (value === undefined || value === null) {
        return '';
    }
    return typeof value === 'object' ? String(value['#text'] ?? '') : String(value);
}

/**
 * Builds name->GUID lookup maps from Tally's own master lists. The
 * "Stock Item Godown" pair object only carries the two name fields (no
 * GUID of its own), but SFA's stable-identifier rule (never match by name
 * alone) still has to hold for the rows this pushes — so each row is
 * enriched with its master records' real GUIDs here, in the agent, using
 * the same two collections fetchStockItems()/fetchGodowns() already pull
 * for Product/Depot mapping.
 *
 * Confirmed live: a row's own name is the `NAME` XML ATTRIBUTE on the row
 * tag itself (e.g. `<LEDGER NAME="Cash">`), i.e. `row['@_NAME']` — not a
 * `<NAME>` child element (that child only appears nested inside
 * LANGUAGENAME.LIST for display-language purposes, and is not this).
 */
function nameToGuidMap(parsed, rowTag) {
    const map = new Map();

    for (const row of rowsFromEnvelope(parsed, rowTag)) {
        const name = row?.['@_NAME'];
        const guid = textOf(row?.GUID);

        if (name && guid) {
            map.set(name, guid);
        }
    }

    return map;
}

/**
 * Per-item stock totals, for **single-depot mode** — the mode this
 * integration runs in today.
 *
 * Godown-wise quantities are not obtainable from this TallyPrime
 * (see docs/tally-sfa-integration.md: godown is absent from voucher and
 * collection exports, and the two built-in reports give per-godown and
 * per-item totals separately, which cannot be combined into the per-cell
 * pairs `product_stocks` is keyed on). So SFA attributes the whole
 * company's stock to one configured depot, and that choice lives
 * server-side (`sfa.tally.default_depot_code`) because it is an SFA
 * business decision, not a Tally fact — these rows deliberately carry no
 * godown at all rather than inventing one.
 *
 * `in_qty`/`out_qty`/`opening_qty` are not reported in this mode: Stock
 * Summary gives a closing position, not movement. They stay 0 rather than
 * being guessed at, and TallyStockSyncService only overwrites the columns
 * it is actually given.
 */
async function buildSingleDepotSnapshot(tally, { fromDate, toDate }) {
    // Sequential, deliberately: Tally's gateway serves one request at a
    // time (a Promise.all of these timed it out and left it wedged).
    const itemsParsed = await tally.fetchStockItems();
    const summaryXml = await tally.fetchStockSummary(fromDate, toDate);

    const itemGuids = nameToGuidMap(itemsParsed, 'STOCKITEM');

    // Classified against the real item names: the exploded report also
    // carries stock-GROUP rows, and its flat format offers no way to tell
    // the levels apart.
    return rowsMatching(summaryXml, itemGuids.keys()).map((row) => ({
        stock_item_name: row.name,
        stock_item_tally_guid: itemGuids.get(row.name) ?? null,
        godown_name: null,
        godown_tally_guid: null,
        opening_qty: 0,
        in_qty: 0,
        out_qty: 0,
        closing_qty: row.quantity,
    }));
}

/**
 * Per-godown snapshot. Retained for when a customer's Tally can actually
 * serve item x godown (via consultant-provided TDL) — it is NOT usable
 * against the current instance, where BATCHALLOCATIONS.LIST carries no
 * godown key. buildSingleDepotSnapshot() is what the agent runs today.
 */
async function buildSnapshot(tally, { fromDate, toDate }) {
    const stockParsed = await tally.fetchStockItemGodownStock(fromDate, toDate);
    const godownParsed = await tally.fetchGodowns();
    const itemsParsed = await tally.fetchStockItems();

    const godownGuids = nameToGuidMap(godownParsed, 'GODOWN');
    const itemGuids = nameToGuidMap(itemsParsed, 'STOCKITEM');

    return toRows(stockParsed).map((row) => ({
        ...row,
        godown_tally_guid: godownGuids.get(row.godown_name) ?? null,
        stock_item_tally_guid: itemGuids.get(row.stock_item_name) ?? null,
    }));
}

module.exports = { buildSnapshot, buildSingleDepotSnapshot, toRows, nameToGuidMap, rowsFromEnvelope };
