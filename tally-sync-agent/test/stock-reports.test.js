'use strict';

/**
 * Fixtures are the verbatim responses from "GDN Tally" on 2026-09-07, for a
 * company holding one purchase (50 PCS into Warehouse) and one sale (5 PCS
 * out of Main Location).
 *
 * Display reports are the ONLY route to godown-wise quantities found to
 * work on TallyPrime 7.1 — godown is absent from voucher exports entirely.
 * Their format is a flat, alternating token stream with no nesting and no
 * depth markers, so these tests also guard the parsing approach itself.
 */

const assert = require('node:assert/strict');
const test = require('node:test');
const { parseDisplayReport, rowsMatching, quantityOf } = require('../src/stockReports');

// Verbatim: TYPE=Data, ID=Godown Summary
const godownSummary = `<ENVELOPE>
 <DSPACCNAME>
  <DSPDISPNAME>Main Location</DSPDISPNAME>
</DSPACCNAME>
 <DSPSTKINFO>
  <DSPSTKCL>
   <DSPCLQTY>-5 PCS</DSPCLQTY>
   <DSPCLRATE></DSPCLRATE>
   <DSPCLAMTA></DSPCLAMTA>
</DSPSTKCL>
</DSPSTKINFO>
 <DSPACCNAME>
  <DSPDISPNAME>Warehouse</DSPDISPNAME>
</DSPACCNAME>
 <DSPSTKINFO>
  <DSPSTKCL>
   <DSPCLQTY>50 PCS</DSPCLQTY>
   <DSPCLRATE>20000.00</DSPCLRATE>
   <DSPCLAMTA>-1000000.00</DSPCLAMTA>
</DSPSTKCL>
</DSPSTKINFO>
</ENVELOPE>`;

// Verbatim: TYPE=Data, ID=Stock Summary, EXPLODEFLAG=Yes.
// Note "Pump" is the stock GROUP and carries no quantity — the exploded
// stream mixes hierarchy levels with nothing to distinguish them.
const stockSummaryExploded = `<ENVELOPE>
 <DSPACCNAME><DSPDISPNAME>Pump</DSPDISPNAME></DSPACCNAME>
 <DSPSTKINFO><DSPSTKCL><DSPCLQTY></DSPCLQTY><DSPCLRATE></DSPCLRATE><DSPCLAMTA>-1000000.00</DSPCLAMTA></DSPSTKCL></DSPSTKINFO>
 <DSPACCNAME><DSPDISPNAME>Pump Model 1</DSPDISPNAME></DSPACCNAME>
 <DSPSTKINFO><DSPSTKCL><DSPCLQTY>45 PCS</DSPCLQTY><DSPCLRATE>22222.22</DSPCLRATE><DSPCLAMTA>-1000000.00</DSPCLAMTA></DSPSTKCL></DSPSTKINFO>
</ENVELOPE>`;

test('a name is paired with the quantity block that follows it', () => {
    const rows = parseDisplayReport(godownSummary);

    assert.deepEqual(
        rows.map((r) => [r.name, r.quantity]),
        [['Main Location', -5], ['Warehouse', 50]],
    );
});

test('negative godown quantities survive (stock issued out)', () => {
    // Main Location went negative because the sale issued from it while the
    // purchase landed in Warehouse — a real state Tally will report.
    const main = parseDisplayReport(godownSummary).find((r) => r.name === 'Main Location');

    assert.equal(main.quantity, -5);
});

test('rate and amount are read from their own elements', () => {
    const warehouse = parseDisplayReport(godownSummary).find((r) => r.name === 'Warehouse');

    assert.equal(warehouse.rate, 20000);
    assert.equal(warehouse.amount, -1000000);
});

test('an empty quantity element is 0, not NaN', () => {
    const group = parseDisplayReport(stockSummaryExploded).find((r) => r.name === 'Pump');

    assert.equal(group.quantity, 0);
    assert.equal(Number.isNaN(group.quantity), false);
});

test('rowsMatching keeps only real godowns, discarding stock groups', () => {
    // The exploded stream carries the group "Pump" alongside the item.
    // Classifying by known names is what makes this parseable at all —
    // the format itself gives no way to tell the levels apart.
    const rows = rowsMatching(stockSummaryExploded, ['Pump Model 1', 'TV Model 1']);

    assert.deepEqual(rows.map((r) => [r.name, r.quantity]), [['Pump Model 1', 45]]);
});

test('rowsMatching is case-insensitive but returns the caller\'s spelling', () => {
    const rows = rowsMatching(godownSummary, ['warehouse']);

    assert.equal(rows.length, 1);
    assert.equal(rows[0].name, 'warehouse', 'the caller\'s own spelling is echoed back for keying');
    assert.equal(rows[0].quantity, 50);
});

test('a name absent from the report simply yields no row', () => {
    assert.deepEqual(rowsMatching(godownSummary, ['Nonexistent Depot']), []);
});

test('quantityOf strips Tally unit suffixes', () => {
    assert.equal(quantityOf('50 PCS'), 50);
    assert.equal(quantityOf('-5 PCS'), -5);
    assert.equal(quantityOf('22222.22'), 22222.22);
    assert.equal(quantityOf(''), 0);
    assert.equal(quantityOf(null), 0);
});
