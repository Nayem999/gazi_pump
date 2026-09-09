'use strict';

const config = require('./config');
const { TallyClient } = require('./tally');
const { SfaClient } = require('./sfa');
const { LocalQueue } = require('./localQueue');
const { handleJob } = require('./handlers');
const { buildSingleDepotSnapshot } = require('./stockSnapshot');
const { log, useLogDirectory } = require('./logger');

const tally = new TallyClient({ host: config.tallyHost, port: config.tallyPort });
const sfa = new SfaClient({ baseUrl: config.sfaApiBaseUrl, agentToken: config.sfaAgentToken });
const localQueue = new LocalQueue(config.localQueueFile);

async function sendHeartbeat() {
    try {
        let tallyCompanyGuid = null;
        try {
            const ping = await tally.ping();
            tallyCompanyGuid = ping?.ENVELOPE?.HEADER?.STATUS === '1' ? undefined : null;
        } catch (error) {
            log('Tally is unreachable locally:', error.message);
        }

        await sfa.heartbeat({ tallyCompanyGuid });
        log('Heartbeat sent.');
    } catch (error) {
        log('Could not reach SFA for heartbeat (will keep retrying):', error.message);
    }
}

async function pollAndProcessJobs() {
    let jobs;
    try {
        jobs = await sfa.claimJobs(10);
    } catch (error) {
        log('Could not reach SFA to claim jobs (will retry next poll):', error.message);
        return;
    }

    if (jobs.length === 0) {
        return;
    }

    log(`Claimed ${jobs.length} job(s).`);

    for (const job of jobs) {
        const result = await handleJob(job, tally);

        try {
            await sfa.reportResult(job.id, result);
        } catch (error) {
            log(`Could not report result for job ${job.id}, buffering locally:`, error.message);
            localQueue.push({ jobId: job.id, result });
        }
    }
}

async function syncStockSnapshot() {
    try {
        const toDate = new Date();
        const fromDate = new Date(toDate.getFullYear(), toDate.getMonth(), 1);
        const rows = await buildSingleDepotSnapshot(tally, { fromDate: formatTallyDate(fromDate), toDate: formatTallyDate(toDate) });

        if (rows.length === 0) {
            log('Stock snapshot returned no rows; skipping push.');
            return;
        }

        const result = await sfa.stockSync(rows);
        log(`Stock snapshot pushed: ${rows.length} row(s).`, result?.message ?? '');
    } catch (error) {
        log('Stock snapshot sync failed (will retry next interval):', error.message);
    }
}

function formatTallyDate(date) {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}${mm}${dd}`;
}

async function flushLocalQueue() {
    const { sent, remaining } = await localQueue.flush((entry) => sfa.reportResult(entry.jobId, entry.result));
    if (sent > 0) {
        log(`Flushed ${sent} buffered result(s) to SFA; ${remaining} still pending.`);
    }
}

async function tick() {
    await flushLocalQueue();
    await pollAndProcessJobs();
}

async function main() {
    // Unattended runs (the scheduled task) set LOG_DIR so there is a
    // record of what happened overnight; an interactive `npm start` leaves
    // it unset and stays console-only.
    const logDir = useLogDirectory(process.env.LOG_DIR);

    log('Gazi Pump Tally Sync Agent starting.');

    if (logDir) {
        log(`Logging to ${logDir}`);
    }
    log(`Tally gateway: http://${config.tallyHost}:${config.tallyPort}`);
    log(`SFA API: ${config.sfaApiBaseUrl}`);

    await sendHeartbeat();
    setInterval(sendHeartbeat, config.heartbeatIntervalMs);
    setInterval(tick, config.pollIntervalMs);
    setInterval(syncStockSnapshot, config.stockSyncIntervalMs);
    await tick();
    await syncStockSnapshot();
}

main().catch((error) => {
    log('Fatal error, exiting:', error);
    process.exitCode = 1;
});
