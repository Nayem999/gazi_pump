'use strict';

/**
 * Parser tests built from vouchers captured verbatim from the real
 * "GDN Tally" instance on 2026-09-07. Each fixture is trimmed to the fields
 * that matter, but the STRUCTURE — which container holds the ledger
 * entries, how amounts are signed, how quantities are formatted — is
 * exactly what Tally sent.
 *
 * These exist because three separate assumptions about that structure were
 * wrong, and none of them were detectable without real data:
 *   - invoice vouchers use LEDGERENTRIES.LIST, not ALLLEDGERENTRIES.LIST
 *   - debit/credit is per ledger entry, not per voucher type
 *   - a Journal carries no PARTYLEDGERNAME at all
 */

const assert = require('node:assert/strict');
const test = require('node:test');
const { toRows } = require('../src/ledgerSnapshot');

const str = (v) => ({ '#text': v, '@_TYPE': 'String' });
const amt = (v) => ({ '#text': v, '@_TYPE': 'Amount' });

/** Journal: two ledgers moved in opposite directions by one voucher. */
const journal = {
    DATE: { '#text': 20260901, '@_TYPE': 'Date' },
    GUID: 'guid-journal',
    VOUCHERTYPENAME: 'Journal',
    VOUCHERNUMBER: 1,
    PARTYLEDGERNAME: { '@_TYPE': 'String' }, // deliberately empty, as Tally sends it
    ISDELETED: 'No',
    'ALLLEDGERENTRIES.LIST': [
        { LEDGERNAME: str('Supplier 1'), AMOUNT: amt(-50) },
        { LEDGERNAME: str('Dealer 2'), AMOUNT: amt(50) },
    ],
};

/** Sales invoice: the dealer's line lives in LEDGERENTRIES.LIST. */
const sales = {
    DATE: { '#text': 20260901, '@_TYPE': 'Date' },
    GUID: 'guid-sales',
    VOUCHERTYPENAME: 'Sales',
    VOUCHERNUMBER: 1,
    PARTYLEDGERNAME: str('Dealer 1'),
    ISDELETED: 'No',
    'LEDGERENTRIES.LIST': { LEDGERNAME: str('Dealer 1'), AMOUNT: amt(-125000) },
    'ALLINVENTORYENTRIES.LIST': {
        STOCKITEMNAME: str('Pump Model 1'),
        ACTUALQTY: { '#text': '5 PCS', '@_TYPE': 'Quantity' },
        'ACCOUNTINGALLOCATIONS.LIST': { LEDGERNAME: str('Sales'), AMOUNT: amt(125000) },
    },
};

function envelope(vouchers) {
    return { ENVELOPE: { BODY: { DATA: { COLLECTION: { VOUCHER: vouchers } } } } };
}

test('a Journal splits into one row per ledger, signed per entry', () => {
    const rows = toRows(envelope([journal]));

    assert.equal(rows.length, 2);

    const supplier = rows.find((r) => r.ledger_name === 'Supplier 1');
    const dealer = rows.find((r) => r.ledger_name === 'Dealer 2');

    // negative AMOUNT on a ledger's own line = debit to it; positive = credit
    assert.deepEqual(
        { debit: supplier.debit_amount, credit: supplier.credit_amount },
        { debit: 50, credit: 0 },
    );
    assert.deepEqual(
        { debit: dealer.debit_amount, credit: dealer.credit_amount },
        { debit: 0, credit: 50 },
    );

    // One voucher, two rows -> their tally_guids must differ or an upsert
    // keyed on tally_guid collapses both sides of the double entry into one.
    assert.notEqual(rows[0].tally_guid, rows[1].tally_guid);
});

test('a Sales invoice is found via LEDGERENTRIES.LIST and debits the dealer', () => {
    const rows = toRows(envelope([sales]), 'Dealer 1');

    assert.equal(rows.length, 1, 'reading only ALLLEDGERENTRIES.LIST would miss this entirely');
    assert.equal(rows[0].debit_amount, 125000);
    assert.equal(rows[0].credit_amount, 0);
    assert.equal(rows[0].voucher_date, '2026-09-01');
    assert.equal(rows[0].voucher_type, 'Sales');
});

test('the nominal side of an invoice is captured too, and each voucher balances', () => {
    const rows = toRows(envelope([journal, sales]));

    for (const [type, expected] of [['Journal', 0], ['Sales', 0]]) {
        const net = rows
            .filter((r) => r.voucher_type === type)
            .reduce((sum, r) => sum + r.debit_amount - r.credit_amount, 0);

        assert.equal(net, expected, `${type} does not balance — a ledger entry is being missed or double-counted`);
    }
});

test('filtering by ledger name is case- and whitespace-insensitive', () => {
    assert.equal(toRows(envelope([sales]), '  dealer 1  ').length, 1);
    assert.equal(toRows(envelope([sales]), 'Dealer 2').length, 0);
});

test('a voucher Tally has flagged deleted never posts to a ledger', () => {
    const deleted = { ...journal, ISDELETED: 'Yes' };

    assert.equal(toRows(envelope([deleted])).length, 0);
});

test('quantities and dates survive Tally\'s wrapped-value encoding', () => {
    // Tally sends 20260901 as a NUMBER and "5 PCS" as a typed object; both
    // must come out usable rather than as "[object Object]".
    const rows = toRows(envelope([sales]), 'Dealer 1');

    assert.equal(rows[0].voucher_date, '2026-09-01');
    assert.equal(typeof rows[0].voucher_number, 'string');
});
