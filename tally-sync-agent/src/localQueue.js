'use strict';

const fs = require('fs');

/**
 * A tiny file-backed buffer for job results the agent couldn't deliver to
 * SFA's cloud API (the internet was briefly down, per spec §4/§47 — "never
 * lose a mobile order because Tally/SFA was temporarily unreachable" cuts
 * both ways here: never lose a Tally result because SFA was unreachable).
 * Not a real queue engine — just enough durability to survive the agent
 * process restarting while offline.
 */
class LocalQueue {
    constructor(filePath) {
        this.filePath = filePath;
    }

    read() {
        if (!fs.existsSync(this.filePath)) {
            return [];
        }
        try {
            return JSON.parse(fs.readFileSync(this.filePath, 'utf8'));
        } catch {
            return [];
        }
    }

    write(entries) {
        fs.writeFileSync(this.filePath, JSON.stringify(entries, null, 2));
    }

    push(entry) {
        const entries = this.read();
        entries.push(entry);
        this.write(entries);
    }

    /**
     * Attempts to flush every buffered entry via sendFn(entry); entries
     * that still fail are kept for the next attempt, in order.
     */
    async flush(sendFn) {
        const entries = this.read();
        if (entries.length === 0) {
            return { sent: 0, remaining: 0 };
        }

        const stillPending = [];
        let sent = 0;

        for (const entry of entries) {
            try {
                await sendFn(entry);
                sent++;
            } catch {
                stillPending.push(entry);
            }
        }

        this.write(stillPending);
        return { sent, remaining: stillPending.length };
    }
}

module.exports = { LocalQueue };
