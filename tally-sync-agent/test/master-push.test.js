'use strict';

/**
 * SFA -> Tally master push: the only requests in this agent that create
 * permanent masters in the customer's accounting system.
 *
 * The request strings are pinned for the same reason the read requests are
 * (see verified-requests.test.js): an unsupported element does not fail
 * cleanly, it pops a modal that blocks Tally's HTTP gateway until a human
 * dismisses it. Changing one of these means verifying the new string live
 * first.
 */

const assert = require('node:assert/strict');
const test = require('node:test');
const { buildLedgerMasterXml, buildStockItemMasterXml, buildGodownMasterXml } = require('../src/masters');
const { handleJob } = require('../src/handlers');

test('a dealer ledger is created under the configured group, bill-wise on', () => {
    const xml = buildLedgerMasterXml({ name: 'Savar Pump House', group: 'Sundry Debtors' });

    assert.match(xml, /<REPORTNAME>All Masters<\/REPORTNAME>/);
    assert.match(xml, /<LEDGER NAME="Savar Pump House" ACTION="Create">/);
    assert.match(xml, /<PARENT>Sundry Debtors<\/PARENT>/);
    // Without bill-wise tracking Tally keeps only a running balance, and
    // the Outstanding report this system reads has nothing to break down.
    assert.match(xml, /<ISBILLWISEON>Yes<\/ISBILLWISEON>/);
});

test('a master push never alters an existing record', () => {
    // SFA does not own these masters once they exist. An Alter would let an
    // SFA edit silently overwrite an accountant's own correction.
    const built = [
        buildLedgerMasterXml({ name: 'A', group: 'Sundry Debtors' }),
        buildStockItemMasterXml({ name: 'B', group: 'Pump', unit: 'PCS' }),
        buildGodownMasterXml({ name: 'C' }),
    ];

    for (const xml of built) {
        assert.match(xml, /ACTION="Create"/);
        assert.doesNotMatch(xml, /ACTION="Alter"/i);
    }
});

test('optional contact fields are omitted entirely when absent', () => {
    // An empty element is not the same as an absent one to Tally.
    const bare = buildLedgerMasterXml({ name: 'No Contact', group: 'Sundry Debtors' });

    assert.doesNotMatch(bare, /<LEDGERPHONE>/);
    assert.doesNotMatch(bare, /<EMAIL>/);
    assert.doesNotMatch(bare, /<ADDRESS\.LIST/);

    const full = buildLedgerMasterXml({
        name: 'Has Contact',
        group: 'Sundry Debtors',
        phone: '01711000000',
        email: 'a@b.com',
        address: '12 Road, Savar',
    });

    assert.match(full, /<LEDGERPHONE>01711000000<\/LEDGERPHONE>/);
    assert.match(full, /<EMAIL>a@b\.com<\/EMAIL>/);
    assert.match(full, /<ADDRESS>12 Road, Savar<\/ADDRESS>/);
});

test('a stock item carries a unit but no rate or opening balance', () => {
    const xml = buildStockItemMasterXml({ name: 'Gazi Tubewell', group: 'Pump', unit: 'PCS' });

    assert.match(xml, /<STOCKITEM NAME="Gazi Tubewell" ACTION="Create">/);
    assert.match(xml, /<PARENT>Pump<\/PARENT>/);
    assert.match(xml, /<BASEUNITS>PCS<\/BASEUNITS>/);
    // Tally owns stock figures and pricing in this integration; pushing
    // SFA's price would be writing into a column SFA does not own.
    assert.doesNotMatch(xml, /OPENINGBALANCE|OPENINGRATE|<RATE>/i);
});

test('an unset group omits PARENT rather than guessing "Primary"', () => {
    // Verified live: Tally stores its reserved roots with a leading space
    // (" Primary") and rejects the space-less spelling outright — the
    // first godown push failed with "Godown 'Primary' does not exist!".
    const item = buildStockItemMasterXml({ name: 'Ungrouped', group: '', unit: 'PCS' });

    assert.doesNotMatch(item, /<PARENT>/);
    assert.match(item, /<BASEUNITS>PCS<\/BASEUNITS>/);
});

test('a godown never claims a parent at all', () => {
    const godown = buildGodownMasterXml({ name: 'Main Depot' });

    assert.doesNotMatch(godown, /<PARENT>/);
    assert.match(godown, /<GODOWN NAME="Main Depot" ACTION="Create">/);
});

test('a name with XML metacharacters cannot break the document', () => {
    const xml = buildLedgerMasterXml({ name: 'Ali & Sons <Pvt> "Ltd"', group: 'Sundry Debtors' });

    assert.match(xml, /<LEDGER NAME="Ali &amp; Sons &lt;Pvt&gt; &quot;Ltd&quot;" ACTION="Create">/);
    assert.doesNotMatch(xml, /NAME="Ali & Sons </);
});

/**
 * A Tally stand-in that records what it was asked to do.
 *
 * `existing` is what Tally already holds — read by the pre-flight check.
 * `assigns` is the GUID a successful create then makes readable, so the
 * fake reproduces the real sequence: absent before the create, present
 * after it. Conflating the two would hide the pre-flight entirely.
 */
function fakeTally({ created = 1, errors = 0, exceptions = 0, altered = 0, lineErrors = [], existing = {}, assigns = null } = {}) {
    const calls = [];
    const masters = { ...existing };

    const collection = (tag) => ({
        ENVELOPE: {
            BODY: {
                DATA: {
                    COLLECTION: {
                        [tag]: Object.entries(masters).map(([name, guid]) => ({ '@_NAME': name, GUID: guid })),
                    },
                },
            },
        },
    });

    return {
        calls,
        async createMaster(xml) {
            calls.push({ op: 'createMaster', xml });

            if (created >= 1 && assigns) {
                masters[assigns.name] = assigns.guid;
            }

            return { raw: { RESPONSE: {} }, created, altered, errors, exceptions, lineErrors };
        },
        async fetchLedgers(group) {
            calls.push({ op: 'fetchLedgers', group });

            return collection('LEDGER');
        },
        async fetchStockItems() {
            calls.push({ op: 'fetchStockItems' });

            return collection('STOCKITEM');
        },
        async fetchGodowns() {
            calls.push({ op: 'fetchGodowns' });

            return collection('GODOWN');
        },
    };
}

const PUSH_JOB = {
    entity_type: 'dealer',
    direction: 'push_to_tally',
    payload: { name: 'Savar Pump House', group: 'Sundry Debtors' },
};

test('a push job creates the master and reports the GUID Tally assigned', async () => {
    const tally = fakeTally({ assigns: { name: 'Savar Pump House', guid: 'GUID-NEW' } });

    const result = await handleJob(PUSH_JOB, tally);

    assert.equal(result.success, true);
    assert.equal(result.tally_guid, 'GUID-NEW');
    // Reads first (does it already exist?), creates, then reads the GUID.
    assert.deepEqual(tally.calls.map((c) => c.op), ['fetchLedgers', 'createMaster', 'fetchLedgers']);
});

test('a name Tally already holds is refused, never overwritten', async () => {
    // The check that matters most here: ACTION="Create" against an existing
    // name does NOT error, it silently ALTERS that master (verified live,
    // CREATED 0 / ALTERED 1). Without the pre-flight read, a push would
    // quietly overwrite an accountant's own record.
    const tally = fakeTally({ existing: { 'Savar Pump House': 'GUID-THEIRS' } });

    const result = await handleJob(PUSH_JOB, tally);

    assert.equal(result.success, false);
    assert.match(result.error_message, /already exists in Tally \(GUID GUID-THEIRS\)/);
    assert.match(result.error_message, /NOT overwritten/);
    assert.deepEqual(tally.calls.map((c) => c.op), ['fetchLedgers'], 'must not reach createMaster at all');
});

test('a dealer job still PULLS when its direction says so', async () => {
    // Direction, not entity type, decides — the same entity type is used
    // for both directions.
    const tally = fakeTally({ existing: { 'Dealer 1': 'G1' } });

    const result = await handleJob({ ...PUSH_JOB, direction: 'pull_from_tally' }, tally);

    assert.equal(result.success, true);
    assert.deepEqual(tally.calls.map((c) => c.op), ['fetchLedgers']);
    assert.ok(!('tally_guid' in result));
});

test('a rejected create is a failure, not a silent no-op', async () => {
    // Tally does not raise a transport error for a rejected master, so
    // checking only for a thrown exception would record it as success.
    const tally = fakeTally({ created: 0, errors: 1 });

    const result = await handleJob(PUSH_JOB, tally);

    assert.equal(result.success, false);
    assert.match(result.error_message, /did not create "Savar Pump House"/);
});

test('a rejection reported as EXCEPTIONS is caught, with Tally own reason passed through', async () => {
    // The trap this exists for: verified live, a godown filed under a
    // non-existent parent came back ERRORS 0 / EXCEPTIONS 1, with the real
    // reason only in LINEERROR. Reading ERRORS alone calls that a success.
    const tally = fakeTally({
        created: 0,
        errors: 0,
        exceptions: 1,
        lineErrors: ["Godown 'Primary' does not exist!"],
    });

    const result = await handleJob(PUSH_JOB, tally);

    assert.equal(result.success, false);
    assert.match(result.error_message, /Godown 'Primary' does not exist!/);
});

test('a create whose GUID cannot be read back is a failure that says so', async () => {
    // The master IS in Tally. Reporting success would leave SFA holding a
    // record it wrote but cannot recognise; a blind retry would collide.
    // Created, but the read-back finds nothing (assigns omitted).
    const tally = fakeTally({ assigns: null });

    const result = await handleJob(PUSH_JOB, tally);

    assert.equal(result.success, false);
    assert.match(result.error_message, /WAS created in Tally/);
    assert.match(result.error_message, /rather than retrying/);
});

test('a product push creates a stock item and reads its GUID back', async () => {
    const tally = fakeTally({ assigns: { name: 'Gazi Tubewell', guid: 'GUID-P' } });

    const result = await handleJob({
        entity_type: 'product',
        direction: 'push_to_tally',
        payload: { name: 'Gazi Tubewell', group: 'Primary', unit: 'PCS' },
    }, tally);

    assert.equal(result.tally_guid, 'GUID-P');
    assert.deepEqual(tally.calls.map((c) => c.op), ['fetchStockItems', 'createMaster', 'fetchStockItems']);
});

test('a depot push creates a godown and reads its GUID back', async () => {
    const tally = fakeTally({ assigns: { name: 'Main Depot', guid: 'GUID-G' } });

    const result = await handleJob({
        entity_type: 'depot',
        direction: 'push_to_tally',
        payload: { name: 'Main Depot' },
    }, tally);

    assert.equal(result.tally_guid, 'GUID-G');
    assert.deepEqual(tally.calls.map((c) => c.op), ['fetchGodowns', 'createMaster', 'fetchGodowns']);
});
