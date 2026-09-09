'use strict';

const axios = require('axios');

/**
 * Client for the three agent-only SFA endpoints (see
 * routes/api/v1.php's "Tally Sync Agent" group and
 * Api\V1\TallyIntegrationController). Authenticated with this connection's
 * own machine credential, never a human Sanctum token.
 */
class SfaClient {
    constructor({ baseUrl, agentToken }) {
        this.client = axios.create({
            baseURL: baseUrl,
            timeout: 15000,
            headers: {
                Authorization: `Bearer ${agentToken}`,
                Accept: 'application/json',
            },
        });
    }

    async heartbeat({ tallyCompanyGuid } = {}) {
        const { data } = await this.client.post('/integration/tally/agent/heartbeat', {
            tally_company_guid: tallyCompanyGuid ?? null,
        });
        return data;
    }

    async claimJobs(limit = 10) {
        const { data } = await this.client.get('/integration/tally/agent/jobs', { params: { limit } });
        return data.data ?? [];
    }

    async reportResult(jobId, result) {
        const { data } = await this.client.post(`/integration/tally/agent/jobs/${jobId}/result`, result);
        return data;
    }

    /**
     * Bulk-pushes a full godown-wise stock snapshot (see stockSnapshot.js)
     * — not part of the job-claim queue, since this isn't one queued
     * transaction but a full overwrite of Tally-owned stock figures.
     */
    async stockSync(rows) {
        const { data } = await this.client.post('/integration/tally/stock-sync', { rows });
        return data;
    }
}

module.exports = { SfaClient };
