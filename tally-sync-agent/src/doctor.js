'use strict';

/**
 * `npm run doctor` — a safe, sequential connectivity check against the
 * local Tally instance, for whoever is installing this on the customer's
 * machine.
 *
 * Why this exists: Tally's HTTP-XML gateway is served by its own UI
 * thread, so it stops answering whenever Tally is busy, showing a modal,
 * or not fully exited — and the failure looks identical from the outside
 * (a TCP connect that succeeds, then silence). Diagnosing that by hand
 * took several rounds during development. This script does the same
 * checks in the right order and says what to actually do about each
 * outcome.
 *
 * It only ever sends live-verified READ requests (see
 * test/verified-requests.test.js), one at a time, and stops at the first
 * failure rather than queueing more work onto a gateway that is already
 * struggling — each extra request to a wedged Tally can stack another
 * modal on the user's desktop.
 */

const net = require('net');
const { TallyClient } = require('./tally');

// Deliberately NOT ./config: that module hard-requires SFA_API_BASE_URL and
// SFA_AGENT_TOKEN, and this check must be runnable purely to diagnose the
// Tally side — typically before the SFA credential has even been issued.
require('dotenv').config();

const config = {
    tallyHost: process.env.TALLY_HOST || 'localhost',
    tallyPort: Number(process.env.TALLY_PORT || 9000),
    dealerGroup: process.env.TALLY_DEALER_LEDGER_GROUP || 'Sundry Debtors',
};

function ok(msg) {
    console.log(`  [32mOK[0m    ${msg}`);
}

function bad(msg) {
    console.log(`  [31mFAIL[0m  ${msg}`);
}

function hint(lines) {
    for (const line of [].concat(lines)) {
        console.log(`        ${line}`);
    }
}

function tcpCheck(host, port, timeout = 5000) {
    return new Promise((resolve) => {
        const socket = net.createConnection({ host, port, timeout }, () => {
            socket.end();
            resolve(true);
        });

        socket.on('error', () => resolve(false));
        socket.on('timeout', () => {
            socket.destroy();
            resolve(false);
        });
    });
}

async function main() {
    const { tallyHost: host, tallyPort: port, dealerGroup } = config;

    console.log(`\nTally Sync Agent — connectivity check against ${host}:${port}\n`);

    // 1. Is anything listening at all?
    if (! await tcpCheck(host, port)) {
        bad(`nothing is listening on ${host}:${port}`);
        hint([
            'Is TallyPrime running on this machine?',
            'In Tally: F12 > Advanced Configuration > Client/Server configuration',
            '  - TallyPrime acts as: Both (or Server)',
            '  - Enable ODBC: Yes',
            `  - Port: ${port}`,
            'Note: this is the "Client/Server and ODBC Services" port, NOT the',
            '"Tally Gateway Server" port (often 9999) from Tally\'s About screen.',
        ]);

        return 1;
    }

    ok(`TCP connect to ${host}:${port} succeeded (a process is listening)`);

    const tally = new TallyClient({ host, port });

    // 2. Does the gateway actually answer? A listening port that never
    //    replies is the classic "Tally is busy / showing a dialog / did not
    //    exit cleanly" signature.
    let company = null;

    try {
        const ping = await tally.ping();
        company = ping?.ENVELOPE?.BODY?.DATA?.COLLECTION?.COMPANY?.['@_NAME'] ?? null;
        ok(`gateway answered${company ? ` — company loaded: "${company}"` : ''}`);
    } catch (error) {
        bad(`the port is listening but the gateway never answered (${error.code || error.message})`);
        hint([
            'The port being open only proves a process holds it — not that',
            'Tally is serving. In order of likelihood:',
            '  1. A dialog/modal is open on the Tally desktop — dismiss it (Esc).',
            '  2. Tally is mid-operation — wait, then re-run.',
            '  3. A previous Tally did not exit cleanly and still holds the port:',
            '     end tally.exe in Task Manager, confirm it disappears, reopen.',
            'Quick independent check: open http://localhost:' + port + ' in a browser.',
            'If the browser hangs too, it is Tally-side, not this agent.',
        ]);

        return 1;
    }

    if (! company) {
        bad('the gateway answered but no company appears to be loaded');
        hint('Open the company in Tally (its data must be loaded to serve masters).');

        return 1;
    }

    // 3. The verified read requests, one at a time. Each failure is
    //    reported and stops the run — no piling on.
    const reads = [
        ['Godowns (-> SFA Depots)', () => tally.fetchGodowns(), 'GODOWN'],
        ['Stock Items (-> SFA Products)', () => tally.fetchStockItems(), 'STOCKITEM'],
        [`Ledgers under \"${dealerGroup}\" (-> SFA Dealers)`, () => tally.fetchLedgers(dealerGroup), 'LEDGER'],
    ];

    const { nameToGuidMap } = require('./stockSnapshot');

    for (const [label, run, tag] of reads) {
        try {
            const found = nameToGuidMap(await run(), tag);

            if (found.size === 0) {
                ok(`${label}: 0 found — reachable, but nothing is configured in Tally yet`);
            } else {
                ok(`${label}: ${found.size} found`);
                for (const [name, guid] of found) {
                    console.log(`          ${name}  ->  ${guid}`);
                }
            }
        } catch (error) {
            bad(`${label}: ${error.code || error.message}`);
            hint('Stopping here so a struggling gateway is not sent more work.');

            return 1;
        }
    }

    console.log('\nAll checks passed. The GUIDs above are what SFA matches on —');
    console.log('map them to Depots/Products/Dealers via Tally Integration > Mapping.\n');

    return 0;
}

main()
    .then((code) => { process.exitCode = code; })
    .catch((error) => {
        bad(`unexpected error: ${error.message}`);
        process.exitCode = 1;
    });
