'use strict';

const { rowsFromEnvelope } = require('./stockSnapshot');

/**
 * Turns Tally's voucher export into one ledger row per (voucher, dealer)
 * pair — the shape SFA's ledger_entries table stores.
 *
 * Rewritten after seeing a real voucher for the first time (2026-09-01
 * Journal, captured live on 2026-09-07). Two things that capture disproved
 * about the original implementation:
 *
 *  1. **Filtering by PARTYLEDGERNAME misses vouchers.** A Journal has an
 *     EMPTY `PARTYLEDGERNAME`; the ledgers it moves live only inside
 *     `ALLLEDGERENTRIES.LIST`. The old design filtered the collection on
 *     `$PartyLedgerName = "<dealer>"` and would have silently returned
 *     nothing for exactly the vouchers an accountant enters by hand.
 *
 *  2. **Debit/credit is per ledger entry, not per voucher.** The original
 *     classified the whole voucher by its type (Sales/Debit Note = debit,
 *     else credit). The real voucher debited "Supplier 1" by 50 and
 *     credited "Dealer 02" by 50 in one Journal — a single voucher moving
 *     two ledgers in opposite directions, which a per-voucher rule cannot
 *     represent. Each entry carries its own signed AMOUNT plus
 *     ISDEEMEDPOSITIVE:
 *
 *         Supplier 1: ISDEEMEDPOSITIVE=Yes, AMOUNT=-50  -> debit  50
 *         Dealer 02 : ISDEEMEDPOSITIVE=No,  AMOUNT=+50  -> credit 50
 *
 *     i.e. a negative AMOUNT on a ledger's own line is a debit to it, a
 *     positive one is a credit. That matches the convention vouchers.js
 *     already uses when it *writes* vouchers.
 */

/**
 * Every ledger movement on a voucher, wherever Tally happens to put it.
 *
 * Tally uses a DIFFERENT container depending on voucher style, verified
 * live against three real vouchers (2026-09-07):
 *
 *   Journal            -> ALLLEDGERENTRIES.LIST   (Supplier 1 -50, Dealer 2 +50)
 *   Purchase (invoice) -> LEDGERENTRIES.LIST      (Supplier 1 +1000000)
 *   Sales    (invoice) -> LEDGERENTRIES.LIST      (Dealer 1   -125000)
 *
 * Reading only ALLLEDGERENTRIES.LIST — as the first version did — therefore
 * missed every invoice-style voucher, including Sales: the single most
 * important debit on a dealer's ledger. Both containers are read here.
 *
 * ACCOUNTINGALLOCATIONS.LIST, nested inside each inventory entry, holds the
 * income/expense side (the "Purchase"/"Sales" nominal ledgers). Those are
 * genuine ledger movements too, so they are included for completeness; a
 * dealer never appears there, so dealer queries are unaffected either way.
 */
function entriesOf(voucher) {
    const entries = [];

    for (const key of ['ALLLEDGERENTRIES.LIST', 'LEDGERENTRIES.LIST']) {
        const list = voucher?.[key];

        if (list && typeof list === 'object') {
            entries.push(...(Array.isArray(list) ? list : [list]));
        }
    }

    const inventory = voucher?.['ALLINVENTORYENTRIES.LIST'];

    for (const item of inventory ? (Array.isArray(inventory) ? inventory : [inventory]) : []) {
        const nested = item?.['ACCOUNTINGALLOCATIONS.LIST'];

        if (nested && typeof nested === 'object') {
            entries.push(...(Array.isArray(nested) ? nested : [nested]));
        }
    }

    return entries;
}

/**
 * @param  parsed        a parsed voucher-collection envelope
 * @param  ledgerName    the dealer's Tally ledger name; only its own
 *                       entries are returned. Omit to return every
 *                       ledger's entries (used for diagnostics).
 */
function toRows(parsed, ledgerName = null) {
    const wanted = ledgerName === null ? null : String(ledgerName).trim().toLowerCase();
    const rows = [];

    for (const voucher of rowsFromEnvelope(parsed, 'VOUCHER')) {
        // A voucher Tally has flagged deleted must never post to a ledger.
        if (String(textOf(voucher.ISDELETED) ?? '').toLowerCase() === 'yes') {
            continue;
        }

        const voucherDate = fromTallyDate(voucher.DATE);
        const voucherType = textOf(voucher.VOUCHERTYPENAME) ?? '';
        const voucherNumber = textOf(voucher.VOUCHERNUMBER);
        const narration = textOf(voucher.NARRATION);
        const voucherGuid = textOf(voucher.GUID);

        for (const [index, entry] of entriesOf(voucher).entries()) {
            const entryLedger = textOf(entry.LEDGERNAME);

            if (! entryLedger) {
                continue;
            }

            if (wanted !== null && entryLedger.trim().toLowerCase() !== wanted) {
                continue;
            }

            const amount = numberOf(entry.AMOUNT);

            rows.push({
                // One voucher can hit the same ledger on several lines, so
                // the GUID alone is not unique per row — SFA upserts on
                // tally_guid, and a bare GUID would collapse them into one.
                tally_guid: voucherGuid ? `${voucherGuid}-${index}` : null,
                ledger_name: entryLedger,
                voucher_date: voucherDate,
                voucher_type: voucherType,
                voucher_number: voucherNumber,
                narration,
                debit_amount: amount < 0 ? Math.abs(amount) : 0,
                credit_amount: amount > 0 ? amount : 0,
            });
        }
    }

    return rows;
}

function textOf(value) {
    if (value === undefined || value === null) {
        return null;
    }
    if (typeof value === 'object') {
        return value['#text'] !== undefined && value['#text'] !== null ? String(value['#text']) : null;
    }
    return String(value);
}

function numberOf(value) {
    if (value === undefined || value === null) {
        return 0;
    }
    const raw = typeof value === 'object' ? value['#text'] : value;
    const match = String(raw ?? '').match(/-?[\d.]+/);
    return match ? parseFloat(match[0]) : 0;
}

/**
 * Tally's DATE comes back as YYYYMMDD (and fast-xml-parser turns it into a
 * number, e.g. 20260901) — convert to ISO so the SFA side's `date` column
 * parses it without guessing.
 */
function fromTallyDate(value) {
    const raw = textOf(value);

    if (! raw || raw.length !== 8) {
        return raw;
    }

    return `${raw.slice(0, 4)}-${raw.slice(4, 6)}-${raw.slice(6, 8)}`;
}

module.exports = { toRows };
