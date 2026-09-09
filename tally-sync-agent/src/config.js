'use strict';

require('dotenv').config();

function required(name) {
    const value = process.env[name];
    if (!value) {
        throw new Error(`Missing required environment variable: ${name}. Copy .env.example to .env and fill it in.`);
    }
    return value;
}

module.exports = {
    sfaApiBaseUrl: required('SFA_API_BASE_URL').replace(/\/+$/, ''),
    sfaAgentToken: required('SFA_AGENT_TOKEN'),
    tallyHost: process.env.TALLY_HOST || 'localhost',
    tallyPort: Number(process.env.TALLY_PORT || 9000),
    // The Tally group the customer files dealer ledgers under.
    // Must be the ledger's IMMEDIATE parent, since $Parent holds a single
    // group name — naming an ancestor like "Current Assets" matches
    // nothing even though Sundry Debtors sits under it. Verified live
    // against "GDN Tally" on 2026-09-07. Still worth checking against the
    // customer's own chart of accounts, since the grouping is their choice.
    tallyDealerLedgerGroup: process.env.TALLY_DEALER_LEDGER_GROUP || 'Sundry Debtors',
    // Retailers only, and deliberately with no default: dealer and
    // retailer pulls used to read the same group, so every dealer ledger
    // was imported a second time as a retailer of the same name. Tally
    // cannot tell the two apart — only the customer's own grouping can —
    // so if this is unset (or set to the dealer group), the retailer pull
    // reports that instead of guessing. See handlers.js.
    tallyRetailerLedgerGroup: (process.env.TALLY_RETAILER_LEDGER_GROUP || '').trim(),
    heartbeatIntervalMs: Number(process.env.HEARTBEAT_INTERVAL_SECONDS || 60) * 1000,
    pollIntervalMs: Number(process.env.POLL_INTERVAL_SECONDS || 30) * 1000,
    stockSyncIntervalMs: Number(process.env.STOCK_SYNC_INTERVAL_SECONDS || 900) * 1000,
    localQueueFile: process.env.LOCAL_QUEUE_FILE || './local-queue.json',
};
