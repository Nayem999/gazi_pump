'use strict';

/**
 * Regression guard for live-verified Tally requests.
 *
 * Context: an unsupported element anywhere in a Tally request — a wrong
 * <TYPE>, but also a merely-unsupported <FETCH> field — pops a modal on
 * the customer's Tally desktop that blocks its HTTP gateway until a human
 * dismisses it. That happened twice during development, the second time
 * because a request that had been verified live was then edited (two
 * unused FETCH fields added) and shipped without re-verifying.
 *
 * These tests pin the exact request strings that were verified against the
 * real "GDN Tally" instance (TallyPrime 7.1) on 2026-09-07. If you need to
 * change one, verify the NEW string live first, then update the expectation
 * here — the point is that the change is deliberate and re-verified, never
 * incidental.
 *
 * Run: npm test
 */

const assert = require('node:assert/strict');
const test = require('node:test');
const { TallyClient } = require('../src/tally');

/**
 * Captures the XML a method would send, without any network call, by
 * stubbing the single choke point every request goes through.
 */
function capture(method, ...args) {
    const client = new TallyClient({ host: 'localhost', port: 9000 });
    let sent = null;

    client.post = async (xml) => {
        sent = xml;

        return {};
    };

    return method.apply(client, args).then(() => sent);
}

test('godown-wise stock request keeps its live-verified TYPE and FETCH', async () => {
    const xml = await capture(TallyClient.prototype.fetchStockItemGodownStock, '2026-09-01', '2027-08-31');

    // Verified-working pair. `Stock Item Godown` as a TYPE hung the gateway;
    // adding BASEUNITS/OPENINGBALANCE to this FETCH hung it too, and neither
    // is read by stockSnapshot.js (quantities come from the per-godown
    // allocation children).
    assert.match(xml, /<TYPE>Stock Item<\/TYPE>/);
    assert.match(xml, /<FETCH>NAME, GUID, CLOSINGBALANCE, BATCHALLOCATIONS\.LIST<\/FETCH>/);
    assert.doesNotMatch(xml, /Stock Item Godown<\/TYPE>/, 'invalid TYPE that hung the gateway');
    assert.doesNotMatch(xml, /BASEUNITS/, 'unused FETCH field that hung the gateway');
    assert.doesNotMatch(xml, /OPENINGBALANCE/, 'unused FETCH field that hung the gateway');

    // Dates must reach Tally as YYYYMMDD, not ISO.
    assert.match(xml, /<SVFROMDATE>20260901<\/SVFROMDATE>/);
    assert.match(xml, /<SVTODATE>20270831<\/SVTODATE>/);
});

test('every collection request uses a name Tally has not seen before', async () => {
    // Tally caches collection results by NAME and, on a repeat request for
    // the same name, omits unchanged fields — GUID included. Since GUID is
    // what SFA matches on, a fixed name means the agent works once and then
    // silently resolves nothing forever after. Verified live.
    const requests = [
        [TallyClient.prototype.fetchLedgers, ['Sundry Debtors']],
        [TallyClient.prototype.fetchStockItems, []],
        [TallyClient.prototype.fetchGodowns, []],
        [TallyClient.prototype.fetchStockItemGodownStock, ['2026-09-01', '2027-08-31']],
        [TallyClient.prototype.fetchVouchers, ['2026-09-01', '2027-08-31']],
    ];

    for (const [method, args] of requests) {
        const first = await capture(method, ...args);
        const second = await capture(method, ...args);

        const nameOf = (xml) => xml.match(/<COLLECTION NAME="([^"]+)"/)[1];
        const idOf = (xml) => xml.match(/<ID>([^<]+)<\/ID>/)[1];

        assert.notEqual(
            nameOf(first),
            nameOf(second),
            `${method.name}() reuses its COLLECTION NAME — Tally would drop GUIDs on the repeat call`,
        );

        // <ID> and COLLECTION NAME must agree, or Tally cannot resolve the
        // collection the request is asking for.
        assert.equal(idOf(first), nameOf(first), `${method.name}() has mismatched <ID> and COLLECTION NAME`);
    }
});

test('ledger request filters via System Formulae, never a bare PARENT', async () => {
    const xml = await capture(TallyClient.prototype.fetchLedgers, 'Sundry Debtors');

    // A bare <PARENT> child is silently IGNORED by Tally (it returned every
    // ledger regardless) — the <FILTER> form is what actually filters.
    assert.match(xml, /<FILTER>SFALedgerParentFilter<\/FILTER>/);
    assert.match(xml, /<SYSTEM TYPE="Formulae" NAME="SFALedgerParentFilter">\$Parent = "Sundry Debtors"<\/SYSTEM>/);
    assert.doesNotMatch(xml, /<PARENT>Sundry Debtors<\/PARENT>/, 'bare PARENT does not filter');
});

test('a quote in a ledger name cannot break out of the TDL string literal', async () => {
    const xml = await capture(TallyClient.prototype.fetchLedgers, 'Ali"s Traders & Co <Ltd>');

    assert.match(xml, /\$Parent = "Ali\\"s Traders &amp; Co &lt;Ltd&gt;"/);
});

test('voucher request must NOT filter by PartyLedgerName', async () => {
    const xml = await capture(TallyClient.prototype.fetchVouchers, '2026-09-01', '2027-08-31');

    assert.match(xml, /<TYPE>Voucher<\/TYPE>/);
    assert.match(xml, /<SVFROMDATE>20260901<\/SVFROMDATE>/);
    assert.match(xml, /<SVTODATE>20270831<\/SVTODATE>/);

    // A real Journal (captured live 2026-09-01) has an EMPTY PARTYLEDGERNAME
    // and names its ledgers only inside ALLLEDGERENTRIES.LIST. Filtering the
    // collection on $PartyLedgerName silently returned zero vouchers for it —
    // exactly the hand-entered vouchers that matter most. Selecting the
    // dealer's lines is ledgerSnapshot.toRows(parsed, ledgerName)'s job.
    assert.doesNotMatch(xml, /PartyLedgerName =/, 'filtering by PartyLedgerName misses Journal vouchers');
    assert.doesNotMatch(xml, /<FILTER>/, 'the voucher collection must stay unfiltered');

    // ALLLEDGERENTRIES.LIST arrives with Tally's default object view; naming
    // it in FETCH risks the unsupported-field hang.
    assert.doesNotMatch(xml, /ALLLEDGERENTRIES/, 'do not add ALLLEDGERENTRIES.LIST to FETCH');
});

test('fetchLedgerVouchers stays a thin alias of the unfiltered request', async () => {
    const viaAlias = await capture(TallyClient.prototype.fetchLedgerVouchers, 'Cash', '2026-09-01', '2027-08-31');

    assert.doesNotMatch(viaAlias, /PartyLedgerName =/);
    assert.match(viaAlias, /<TYPE>Voucher<\/TYPE>/);
});

test('every request routes through the circuit breaker', async () => {
    const client = new TallyClient({ host: 'localhost', port: 9000, breakerCooldownMs: 60000 });
    client.wedgedAt = Date.now();

    const methods = [
        ['ping', []],
        ['fetchLedgers', ['Sundry Debtors']],
        ['fetchStockItems', []],
        ['fetchGodowns', []],
        ['fetchStockItemGodownStock', ['2026-09-01', '2027-08-31']],
        ['fetchLedgerVouchers', ['Cash', '2026-09-01', '2027-08-31']],
        ['createVoucher', ['<ENVELOPE/>']],
        ['createMaster', ['<ENVELOPE/>']],
    ];

    for (const [name, args] of methods) {
        await assert.rejects(
            () => client[name](...args),
            /looks wedged/,
            `${name}() bypasses the breaker — it must go through post()`,
        );
    }
});
