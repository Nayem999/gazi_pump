'use strict';

const { buildSalesVoucherXml, buildReceiptVoucherXml, buildDeliveryNoteVoucherXml, buildCreditNoteVoucherXml } = require('./vouchers');
const { toRows: ledgerRowsFrom } = require('./ledgerSnapshot');
const { nameToGuidMap } = require('./stockSnapshot');
const { buildLedgerMasterXml, buildStockItemMasterXml, buildGodownMasterXml } = require('./masters');

const MASTER_ENTITY_TYPES = ['dealer', 'retailer', 'product', 'depot'];

// Read straight from the environment rather than requiring ./config: that
// module hard-requires the SFA credential, and pulling it in here made the
// handlers (and anything testing them) unloadable without one. The Tally
// side must stay diagnosable on its own — same reasoning as doctor.js.
function dealerLedgerGroup() {
    return process.env.TALLY_DEALER_LEDGER_GROUP || 'Sundry Debtors';
}

// No default, unlike the dealer group: see config.js. An unset (or
// dealer-identical) value means "the customer hasn't told us how to tell
// retailers from dealers", which pullRetailerLedgers() reports rather than
// resolving by guesswork.
function retailerLedgerGroup() {
    return (process.env.TALLY_RETAILER_LEDGER_GROUP || '').trim();
}

/**
 * One handler per TallyEntityType the agent knows how to execute — Phase 1
 * added Dealer/Retailer/Product (pull), Phase 2 adds Sales Order/Collection
 * (push, as Tally vouchers). Each handler returns the shape
 * SfaClient.reportResult() expects: { success, response, tally_guid?,
 * tally_voucher_number?, error_code?, error_message? }.
 */
async function handleJob(job, tally) {
    // Direction is checked before entity type: a dealer job can be either a
    // master pull or a master push, and the four master entity types below
    // would otherwise always read.
    if (job.direction === 'push_to_tally' && MASTER_ENTITY_TYPES.includes(job.entity_type)) {
        return pushMaster(job, tally);
    }

    switch (job.entity_type) {
        case 'dealer':
            return pullLedgers(tally, dealerLedgerGroup());
        case 'retailer':
            return pullRetailerLedgers(tally);
        case 'product':
            return pullStockItems(tally);
        case 'depot':
            return pullGodowns(tally);
        case 'sales_order':
            return pushSalesOrder(job, tally);
        case 'collection':
            return pushCollection(job, tally);
        case 'delivery':
            return pushDelivery(job, tally);
        case 'ledger':
            return pullLedgerVouchers(job, tally);
        case 'return':
            return pushSalesReturn(job, tally);
        default:
            return {
                success: false,
                error_code: 'TALLY_INVALID_VOUCHER',
                error_message: `No Sync Agent handler implemented yet for entity_type "${job.entity_type}".`,
            };
    }
}

async function pushSalesOrder(job, tally) {
    try {
        const xml = buildSalesVoucherXml(job.payload, job.external_reference);
        const result = await tally.createVoucher(xml);

        if (result.created >= 1 && result.errors === 0) {
            return { success: true, response: result.raw, tally_voucher_number: result.lastVoucherId };
        }

        return {
            success: false,
            response: result.raw,
            error_code: 'TALLY_INVALID_VOUCHER',
            error_message: `Tally reported ${result.errors} error(s) creating the Sales voucher.`,
        };
    } catch (error) {
        return { success: false, error_code: 'TALLY_OFFLINE', error_message: error.message };
    }
}

async function pushCollection(job, tally) {
    try {
        const xml = buildReceiptVoucherXml(job.payload, job.external_reference);
        const result = await tally.createVoucher(xml);

        if (result.created >= 1 && result.errors === 0) {
            return { success: true, response: result.raw, tally_voucher_number: result.lastVoucherId };
        }

        return {
            success: false,
            response: result.raw,
            error_code: 'TALLY_INVALID_VOUCHER',
            error_message: `Tally reported ${result.errors} error(s) creating the Receipt voucher.`,
        };
    } catch (error) {
        return { success: false, error_code: 'TALLY_OFFLINE', error_message: error.message };
    }
}

async function pushDelivery(job, tally) {
    try {
        const xml = buildDeliveryNoteVoucherXml(job.payload, job.external_reference);
        const result = await tally.createVoucher(xml);

        if (result.created >= 1 && result.errors === 0) {
            return { success: true, response: result.raw, tally_voucher_number: result.lastVoucherId };
        }

        return {
            success: false,
            response: result.raw,
            error_code: 'TALLY_INVALID_VOUCHER',
            error_message: `Tally reported ${result.errors} error(s) creating the Delivery Note voucher.`,
        };
    } catch (error) {
        return { success: false, error_code: 'TALLY_OFFLINE', error_message: error.message };
    }
}

/**
 * One dealer's voucher history (Phase 5: Ledger/Credit Note/Debit Note —
 * all just voucher_type values in the same ledger). Unlike the master
 * pulls below, this is per-dealer (job.payload carries which dealer and
 * date range) and its rows are normalized here, in the agent, so SFA gets
 * back a ready-to-upsert row list rather than raw Tally XML to interpret.
 */
async function pushSalesReturn(job, tally) {
    try {
        const xml = buildCreditNoteVoucherXml(job.payload, job.external_reference);
        const result = await tally.createVoucher(xml);

        if (result.created >= 1 && result.errors === 0) {
            return { success: true, response: result.raw, tally_voucher_number: result.lastVoucherId };
        }

        return {
            success: false,
            response: result.raw,
            error_code: 'TALLY_INVALID_VOUCHER',
            error_message: `Tally reported ${result.errors} error(s) creating the Credit Note voucher.`,
        };
    } catch (error) {
        return { success: false, error_code: 'TALLY_OFFLINE', error_message: error.message };
    }
}

async function pullLedgerVouchers(job, tally) {
    try {
        const parsed = await tally.fetchVouchers(job.payload.from_date, job.payload.to_date);

        // The dealer's own lines are selected here, not by the request:
        // Tally can't filter a voucher collection by a ledger buried in
        // ALLLEDGERENTRIES.LIST, and a Journal has no PARTYLEDGERNAME to
        // filter on at all (see tally.js's fetchVouchers note).
        const rows = ledgerRowsFrom(parsed, job.payload.dealer_tally_name);

        return { success: true, response: { rows } };
    } catch (error) {
        return { success: false, error_code: 'TALLY_OFFLINE', error_message: error.message };
    }
}

/**
 * Normalizes a master collection into the row shape SFA can act on
 * directly — `{ rows: [{ name, guid }] }`, the same contract
 * pullLedgerVouchers() uses.
 *
 * Previously these handlers returned Tally's raw parsed envelope, which
 * SFA stored and then ignored, so master pulls looked like they worked and
 * mapped nothing. Normalizing here also keeps every assumption about
 * Tally's XML shape (rows nested under BODY.DATA.COLLECTION, name as an
 * attribute) in the agent, where it is tested against real captures.
 */
function masterRowsFrom(parsed, rowTag) {
    return [...nameToGuidMap(parsed, rowTag)].map(([name, guid]) => ({ name, guid }));
}

async function pullLedgers(tally, group) {
    try {
        const parsed = await tally.fetchLedgers(group);

        return { success: true, response: { rows: masterRowsFrom(parsed, 'LEDGER'), group } };
    } catch (error) {
        return {
            success: false,
            error_code: 'TALLY_OFFLINE',
            error_message: error.message,
        };
    }
}

/**
 * Retailers, which need their own group to be meaningful.
 *
 * A Tally ledger doesn't say whether it belongs to a dealer or a retailer;
 * the only signal is which group the customer filed it under. When dealer
 * and retailer pulls both read the dealer group, every dealer was imported
 * twice — once as a Dealer, once as a Retailer with the same name — which
 * is worse than importing nothing. So an unconfigured (or dealer-identical)
 * retailer group returns no rows and says why, leaving SFA's retailers
 * untouched until someone sets TALLY_RETAILER_LEDGER_GROUP.
 */
async function pullRetailerLedgers(tally) {
    const group = retailerLedgerGroup();

    if (group === '' || group.toLowerCase() === dealerLedgerGroup().toLowerCase()) {
        return {
            success: true,
            response: {
                rows: [],
                skipped: 'TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer '
                    + 'ledgers cannot be told apart from dealer ledgers. Set it to the group the '
                    + "customer files retailers under (it must differ from the dealers' group).",
            },
        };
    }

    return pullLedgers(tally, group);
}

/**
 * Godown master list (name/GUID/alter ID) — the Depot-side analog of
 * pullLedgers(), used to resolve each Depot's tally_guid mapping.
 */
async function pullGodowns(tally) {
    try {
        const parsed = await tally.fetchGodowns();

        return { success: true, response: { rows: masterRowsFrom(parsed, 'GODOWN') } };
    } catch (error) {
        return {
            success: false,
            error_code: 'TALLY_OFFLINE',
            error_message: error.message,
        };
    }
}

/**
 * Stock Item master list (name/GUID/alter ID) — the Product-side analog of
 * pullLedgers(), used to resolve each Product's tally_guid mapping.
 */
async function pullStockItems(tally) {
    try {
        const parsed = await tally.fetchStockItems();

        return { success: true, response: { rows: masterRowsFrom(parsed, 'STOCKITEM') } };
    } catch (error) {
        return {
            success: false,
            error_code: 'TALLY_OFFLINE',
            error_message: error.message,
        };
    }
}

/**
 * Creates one SFA master in Tally, then resolves the GUID Tally assigned
 * it.
 *
 * The GUID matters more than the create: without it SFA has written a
 * record into the accounting system it cannot subsequently recognise, and
 * the next pull would see an unknown Tally master with a name that happens
 * to match — the exact ambiguity the GUID rule exists to avoid. So a
 * create whose GUID cannot be read back is reported as a failure, with the
 * create itself called out in the message: the master IS in Tally, and a
 * blind retry would collide on the name rather than fix anything.
 *
 * Tally's import response carries no GUID, so it is read with the ordinary
 * live-verified master read rather than a bespoke request.
 */
async function pushMaster(job, tally) {
    const payload = job.payload || {};
    const built = buildMasterXml(job.entity_type, payload);

    if (!built) {
        return {
            success: false,
            error_code: 'TALLY_INVALID_VOUCHER',
            error_message: `No master push builder for entity_type "${job.entity_type}".`,
        };
    }

    try {
        // Pre-flight, and not optional: ACTION="Create" against a name that
        // already exists does NOT error — Tally silently ALTERS the
        // existing master (verified live: CREATED 0 / ALTERED 1). Without
        // this check a push would overwrite an accountant's own record with
        // SFA's version of it, which is the one thing this direction must
        // never do. The create below is only reached for a name Tally has
        // never seen.
        const existingGuid = await resolveMasterGuid(job.entity_type, payload, tally);

        if (existingGuid) {
            return {
                success: false,
                error_code: 'TALLY_INVALID_VOUCHER',
                error_message: `"${payload.name}" already exists in Tally (GUID ${existingGuid}), so it was NOT overwritten. `
                    + 'Run a Tally to SFA sync instead — that links an unmapped record to the existing master by name.',
            };
        }

        const result = await tally.createMaster(built.xml);

        // All three are checked: Tally does not raise a transport error for
        // a rejected master, and it does not use ERRORS for one either —
        // a rejection arrives as EXCEPTIONS with the reason in LINEERROR
        // (verified live). Tally's own wording is passed straight through,
        // because it names the actual problem ("Godown 'Primary' does not
        // exist!") far better than anything this code could infer.
        if (result.created < 1 || result.errors > 0 || result.exceptions > 0) {
            const reason = result.lineErrors.length > 0
                ? result.lineErrors.join(' ')
                : result.altered > 0
                    // Should be unreachable given the pre-flight above, but
                    // reported explicitly rather than as a generic failure:
                    // it means an existing master was modified.
                    ? 'Tally altered an existing master instead of creating one. Check that master in Tally.'
                    : 'Tally gave no reason.';

            return {
                success: false,
                response: result.raw,
                error_code: 'TALLY_INVALID_VOUCHER',
                error_message: `Tally did not create "${payload.name}": ${reason}`,
            };
        }

        const guid = await resolveMasterGuid(job.entity_type, payload, tally);

        if (!guid) {
            return {
                success: false,
                response: result.raw,
                error_code: 'TALLY_INVALID_VOUCHER',
                error_message: `"${payload.name}" WAS created in Tally, but its GUID could not be read back, `
                    + 'so it is unmapped here. Resolve it with a master pull rather than retrying this push, '
                    + 'which would collide on the name.',
            };
        }

        return { success: true, response: result.raw, tally_guid: guid };
    } catch (error) {
        return { success: false, error_code: 'TALLY_OFFLINE', error_message: error.message };
    }
}

function buildMasterXml(entityType, payload) {
    switch (entityType) {
        case 'dealer':
        case 'retailer':
            return { xml: buildLedgerMasterXml(payload) };
        case 'product':
            return { xml: buildStockItemMasterXml(payload) };
        case 'depot':
            return { xml: buildGodownMasterXml(payload) };
        default:
            return null;
    }
}

/**
 * Reads back the GUID Tally assigned to a master just created, by name.
 *
 * Name lookup is sound here specifically because the name was written one
 * request ago and Tally rejects duplicate master names — this is not the
 * name-based identity matching the rest of the system forbids.
 */
async function resolveMasterGuid(entityType, payload, tally) {
    const wanted = String(payload.name || '').trim().toLowerCase();

    if (wanted === '') {
        return null;
    }

    let parsed;
    let rowTag;

    if (entityType === 'dealer' || entityType === 'retailer') {
        parsed = await tally.fetchLedgers(payload.group);
        rowTag = 'LEDGER';
    } else if (entityType === 'product') {
        parsed = await tally.fetchStockItems();
        rowTag = 'STOCKITEM';
    } else {
        parsed = await tally.fetchGodowns();
        rowTag = 'GODOWN';
    }

    for (const [name, guid] of nameToGuidMap(parsed, rowTag)) {
        if (String(name).trim().toLowerCase() === wanted) {
            return guid;
        }
    }

    return null;
}

module.exports = { handleJob };
