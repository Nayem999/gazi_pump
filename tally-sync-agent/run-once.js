'use strict';

/**
 * Drains the SFA job queue once, reporting each job as it completes, then
 * exits — as opposed to `npm start`, which runs forever on a timer.
 *
 * Useful for a bulk master push, where the operator wants to watch 45
 * records go through and see exactly which ones Tally refused, rather than
 * discovering it later in the Sync Queue screen.
 *
 * Jobs run strictly one at a time: TallyPrime's HTTP gateway is its UI
 * thread and serves a single request at a time, and concurrent requests
 * have wedged it during development.
 */

require('dotenv').config();

const config = require('./src/config');
const { TallyClient } = require('./src/tally');
const { SfaClient } = require('./src/sfa');
const { handleJob } = require('./src/handlers');

const tally = new TallyClient({ host: config.tallyHost, port: config.tallyPort });
const sfa = new SfaClient({ baseUrl: config.sfaApiBaseUrl, agentToken: config.sfaAgentToken });

async function main() {
    let done = 0;
    let failed = 0;

    for (;;) {
        const jobs = await sfa.claimJobs(10);

        if (jobs.length === 0) {
            break;
        }

        for (const job of jobs) {
            const result = await handleJob(job, tally);
            const label = `${job.entity_type}/${job.direction}`;

            if (result.success) {
                done++;
                const detail = result.tally_guid
                    ? `guid=${result.tally_guid}`
                    : `rows=${(result.response?.rows || []).length}`;
                console.log(`  ok   ${label.padEnd(24)} ${job.payload?.name ?? ''} ${detail}`);
            } else {
                failed++;
                console.log(`  FAIL ${label.padEnd(24)} ${job.payload?.name ?? ''} — ${result.error_message}`);
            }

            await sfa.reportResult(job.id, result);
        }
    }

    console.log(`\nFinished: ${done} succeeded, ${failed} failed.`);
}

main().catch((error) => {
    console.error('Run aborted:', error.message);
    process.exitCode = 1;
});
