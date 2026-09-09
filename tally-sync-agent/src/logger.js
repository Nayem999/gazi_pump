'use strict';

const fs = require('fs');
const path = require('path');

/**
 * Console logging that also appends to a dated file when LOG_DIR is set.
 *
 * The agent owns its logging rather than leaving it to a shell wrapper,
 * because the wrapper approach cost real debugging time and one genuine
 * fault:
 *
 *   - PowerShell's `*>>` redirection writes UTF-16, turning every line
 *     into space-separated mojibake.
 *   - Piping to Add-Content fixed that but buffered, so a live tail showed
 *     nothing.
 *   - Worse, wrapping node in powershell -> cmd -> node meant Task
 *     Scheduler's Stop killed only the wrapper. The node grandchild
 *     survived orphaned and kept claiming jobs, so a "restart" quietly
 *     left TWO agents racing for the same queue.
 *
 * With the agent writing its own log, the scheduled task can execute
 * node.exe directly and stopping the task stops the actual agent.
 *
 * Writes are synchronous appends: the volume is a handful of lines a
 * minute, and a log that is always complete on disk is worth more here
 * than saving those microseconds — this is the only record of what an
 * unattended agent did overnight.
 */

const RETENTION_DAYS = Number(process.env.LOG_RETENTION_DAYS || 14);

let logDir = null;
let currentDate = null;
let currentStream = null;

function dateStamp(now) {
    return now.toISOString().slice(0, 10);
}

function pruneOldLogs() {
    try {
        const cutoff = Date.now() - RETENTION_DAYS * 24 * 60 * 60 * 1000;

        for (const name of fs.readdirSync(logDir)) {
            if (!/^agent-\d{4}-\d{2}-\d{2}\.log$/.test(name)) {
                continue;
            }

            const file = path.join(logDir, name);

            if (fs.statSync(file).mtimeMs < cutoff) {
                fs.unlinkSync(file);
            }
        }
    } catch {
        // Housekeeping must never stop the agent from logging or running.
    }
}

/**
 * Points file logging at a directory, creating it if needed. Called once at
 * startup; without it, logging stays console-only (which is what an
 * interactive `npm start` wants).
 */
function useLogDirectory(dir) {
    if (!dir) {
        return null;
    }

    logDir = path.resolve(dir);
    fs.mkdirSync(logDir, { recursive: true });
    pruneOldLogs();

    return logDir;
}

function streamFor(now) {
    const stamp = dateStamp(now);

    // Roll at midnight so a long-running agent does not accumulate one
    // enormous file, and so a given day's history is easy to find.
    if (stamp !== currentDate) {
        if (currentStream !== null) {
            fs.closeSync(currentStream);
        }

        currentStream = fs.openSync(path.join(logDir, `agent-${stamp}.log`), 'a');
        currentDate = stamp;
        pruneOldLogs();
    }

    return currentStream;
}

function log(...args) {
    const now = new Date();
    const line = [now.toISOString(), ...args.map(format)].join(' ');

    console.log(line);

    if (logDir === null) {
        return;
    }

    try {
        fs.writeSync(streamFor(now), line + '\n');
    } catch {
        // A log write failing (disk full, permissions) must not take the
        // agent down — the console line above already happened.
    }
}

/** Errors stringify to "[object Object]" otherwise, losing the message. */
function format(value) {
    if (value instanceof Error) {
        return value.stack || value.message;
    }

    if (typeof value === 'object' && value !== null) {
        try {
            return JSON.stringify(value);
        } catch {
            return String(value);
        }
    }

    return String(value);
}

module.exports = { log, useLogDirectory };
