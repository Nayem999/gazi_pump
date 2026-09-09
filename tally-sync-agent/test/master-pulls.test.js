'use strict';

/**
 * Master pulls (dealer/retailer/product/depot) — how a Tally master list
 * becomes the `{ rows: [{ name, guid }] }` SFA acts on.
 *
 * Two live-verified facts drive these tests:
 *
 *  1. Tally's $Parent holds one group name, not an ancestor chain, so the
 *     configured group must be the ledger's immediate parent ("Sundry
 *     Debtors" works; "Current Assets", its own parent, returns nothing).
 *  2. A Tally ledger carries no dealer-vs-retailer flag. The only signal is
 *     which group the customer filed it under — so pulling both from one
 *     group imports every dealer a second time as a retailer of the same
 *     name, which is how "Dealer 1" ended up in both tables.
 */

const assert = require('node:assert/strict');
const test = require('node:test');

/**
 * handlers.js reads the ledger groups from process.env at call time, so a
 * test can set them per case. Each case gets a fresh module instance to
 * keep that honest.
 */
function handlersWith(env) {
    const previous = {};

    for (const key of ['TALLY_DEALER_LEDGER_GROUP', 'TALLY_RETAILER_LEDGER_GROUP']) {
        previous[key] = process.env[key];
        if (env[key] === undefined) {
            delete process.env[key];
        } else {
            process.env[key] = env[key];
        }
    }

    delete require.cache[require.resolve('../src/handlers')];
    const handlers = require('../src/handlers');

    return {
        handlers,
        restore() {
            for (const [key, value] of Object.entries(previous)) {
                if (value === undefined) {
                    delete process.env[key];
                } else {
                    process.env[key] = value;
                }
            }
        },
    };
}

/**
 * A stand-in Tally that records which group it was asked for and answers
 * with the envelope shape the real gateway returns — rows under
 * BODY.DATA.COLLECTION, with the name as an XML attribute.
 */
function fakeTally(rowsByGroup) {
    const asked = [];

    return {
        asked,
        async fetchLedgers(group) {
            asked.push(group);

            return {
                ENVELOPE: {
                    BODY: {
                        DATA: {
                            COLLECTION: {
                                LEDGER: (rowsByGroup[group] || []).map((row) => ({
                                    '@_NAME': row.name,
                                    GUID: row.guid,
                                })),
                            },
                        },
                    },
                },
            };
        },
    };
}

const LEDGERS = {
    'Sundry Debtors': [
        { name: 'Dealer 1', guid: 'guid-d1' },
        { name: 'Dealer 2', guid: 'guid-d2' },
    ],
    'Retail Customers': [{ name: 'Shop A', guid: 'guid-r1' }],
};

test('a dealer pull reads the configured dealer group', async () => {
    const { handlers, restore } = handlersWith({ TALLY_DEALER_LEDGER_GROUP: 'Sundry Debtors' });

    try {
        const tally = fakeTally(LEDGERS);
        const result = await handlers.handleJob({ entity_type: 'dealer' }, tally);

        assert.deepEqual(tally.asked, ['Sundry Debtors']);
        assert.equal(result.success, true);
        assert.deepEqual(result.response.rows, [
            { name: 'Dealer 1', guid: 'guid-d1' },
            { name: 'Dealer 2', guid: 'guid-d2' },
        ]);
    } finally {
        restore();
    }
});

test('a retailer pull with no group of its own imports nothing and says why', async () => {
    const { handlers, restore } = handlersWith({
        TALLY_DEALER_LEDGER_GROUP: 'Sundry Debtors',
        TALLY_RETAILER_LEDGER_GROUP: undefined,
    });

    try {
        const tally = fakeTally(LEDGERS);
        const result = await handlers.handleJob({ entity_type: 'retailer' }, tally);

        // Not a failure: there is nothing wrong with Tally, and marking it
        // failed would retry a configuration gap forever.
        assert.equal(result.success, true);
        assert.deepEqual(result.response.rows, []);
        assert.match(result.response.skipped, /TALLY_RETAILER_LEDGER_GROUP/);
        assert.deepEqual(tally.asked, [], 'must not fall back to the dealer group');
    } finally {
        restore();
    }
});

test('a retailer group equal to the dealer group is refused, not obeyed', async () => {
    const { handlers, restore } = handlersWith({
        TALLY_DEALER_LEDGER_GROUP: 'Sundry Debtors',
        // Same group, differing only in case — still the same group, and
        // still guarantees a duplicate of every dealer.
        TALLY_RETAILER_LEDGER_GROUP: 'sundry debtors',
    });

    try {
        const tally = fakeTally(LEDGERS);
        const result = await handlers.handleJob({ entity_type: 'retailer' }, tally);

        assert.deepEqual(result.response.rows, []);
        assert.deepEqual(tally.asked, []);
    } finally {
        restore();
    }
});

test('a retailer pull with its own group reads that group', async () => {
    const { handlers, restore } = handlersWith({
        TALLY_DEALER_LEDGER_GROUP: 'Sundry Debtors',
        TALLY_RETAILER_LEDGER_GROUP: 'Retail Customers',
    });

    try {
        const tally = fakeTally(LEDGERS);
        const result = await handlers.handleJob({ entity_type: 'retailer' }, tally);

        assert.deepEqual(tally.asked, ['Retail Customers']);
        assert.deepEqual(result.response.rows, [{ name: 'Shop A', guid: 'guid-r1' }]);
    } finally {
        restore();
    }
});

test('rows missing a GUID are dropped rather than passed on unmatched', async () => {
    const { handlers, restore } = handlersWith({ TALLY_DEALER_LEDGER_GROUP: 'Partial' });

    try {
        // Tally omits GUID when it answers from cache; a row without one
        // cannot be mapped, and SFA must never fall back to name identity.
        const tally = fakeTally({
            Partial: [
                { name: 'Has GUID', guid: 'guid-ok' },
                { name: 'No GUID', guid: undefined },
            ],
        });

        const result = await handlers.handleJob({ entity_type: 'dealer' }, tally);

        assert.deepEqual(result.response.rows, [{ name: 'Has GUID', guid: 'guid-ok' }]);
    } finally {
        restore();
    }
});
