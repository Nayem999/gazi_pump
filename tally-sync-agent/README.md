# Gazi Pump Tally Sync Agent

A small Node.js program that runs on the customer's own PC/server, next to
TallyPrime — **not** part of the Laravel SFA application, never merged into
its dependency tree. It is the only thing allowed to reach TallyPrime's
local HTTP-XML gateway; SFA's cloud API never talks to Tally directly.

```
SFA Cloud API  <-- HTTPS -->  Sync Agent (this program)  <-- HTTP -->  TallyPrime
```

## What it does

1. Sends a heartbeat to SFA on an interval, so the admin's Integration
   Dashboard can show 🟢 Connected / 🔴 Offline.
2. Polls SFA for pending sync jobs (`GET /api/v1/integration/tally/agent/jobs`)
   and executes each against the local Tally instance — Dealer/Retailer
   ledger and Product/Depot master lookups (pull), one dealer's ledger
   voucher history (pull, Phase 5 — Ledger/Credit Note/Debit Note are all
   just voucher_type values in the same ledger), Sales Order/Collection/
   Delivery Note/Credit Note vouchers (push, the last one being Phase 6's
   Sales Return workflow reaching Received) — see `src/handlers.js`.
3. Reports each job's result back to SFA (`POST .../jobs/{id}/result`).
4. On its own interval (`STOCK_SYNC_INTERVAL_SECONDS`), pushes a full
   godown-wise stock snapshot straight to SFA
   (`POST /api/v1/integration/tally/stock-sync`) — this bypasses the job
   queue entirely, since a stock quantity is a godown+item pair, not a
   single queued entity (see `src/stockSnapshot.js`).
5. If SFA is briefly unreachable, buffers job results in a local file
   (`local-queue.json`) and flushes them once connectivity returns, so a
   Tally-side result is never silently lost. (The stock snapshot push
   itself simply retries on its next interval rather than buffering, since
   a newer snapshot always supersedes an older one.)

## Setup

```bash
cd tally-sync-agent
npm install
cp .env.example .env
```

Edit `.env`:

- `SFA_API_BASE_URL` — the SFA cloud API's base URL.
- `SFA_AGENT_TOKEN` — the Sync Agent credential shown once when the
  connection was created (or its token regenerated) on the SFA admin's
  **Tally Connections** page. This is a secret — never commit it, never
  paste it anywhere but here.
- `TALLY_HOST` / `TALLY_PORT` — where *this same machine* reaches Tally's
  HTTP-XML gateway (defaults `localhost:9000`, TallyPrime's standard port).
- `TALLY_DEALER_LEDGER_GROUP` — the Tally group dealer ledgers hang off
  **directly**. Tally's `$Parent` holds one group name, not an ancestor
  chain, so naming a parent-of-a-parent matches nothing: on the reference
  instance `Sundry Debtors` returns the dealers and `Current Assets` — its
  own parent — returns zero rows. `npm run doctor` prints which group it
  checked and what it found.
- `TALLY_RETAILER_LEDGER_GROUP` — the group retailers are filed under.
  **No default, and it must differ from the dealer group.** A Tally ledger
  carries no dealer-vs-retailer flag; the grouping is the only signal. When
  both pulls read one group, every dealer gets imported a second time as a
  retailer of the same name. Left blank, the retailer pull imports nothing
  and reports why — the right outcome for a customer who keeps every party
  in a single group.

Run it:

```bash
npm start
```

Check the Tally connection before running it for real:

```bash
npm run doctor
```

Walks the checks in order — is anything listening, does the gateway
actually answer, is a company loaded, do the master reads work — and prints
what to do about whatever fails, plus the real Tally GUIDs to map against.
Needs only `TALLY_HOST`/`TALLY_PORT`, not the SFA credential, so it can be
run before the connection is even created in SFA. It sends only
live-verified read requests, one at a time, and stops at the first failure
rather than sending more work to a struggling gateway.

The check worth knowing about: **a listening port does not mean Tally is
serving.** Tally's gateway is its UI thread, so it goes silent whenever
Tally is showing a dialog, mid-operation, or didn't exit cleanly — and from
outside, all three look the same (TCP connects, then nothing). `doctor`
distinguishes them.

### Creating masters in Tally (SFA -> Tally)

The agent also handles the other direction: an SFA dealer, product or
depot with no counterpart in Tally is created there as a Ledger, Stock
Item or Godown. **The switch is on the SFA side**, not here —
`SFA_TALLY_MASTER_PUSH_ENABLED`, off by default — because it is the SFA
operator who decides whether records may be written into the accounting
system. The agent simply executes the jobs it is given.

Two behaviours are worth knowing before you touch this code:

- **`ACTION="Create"` against an existing name does NOT error.** Tally
  silently *alters* that master (`CREATED 0 / ALTERED 1`). The push
  therefore reads the master list first and refuses a name that already
  exists, rather than trusting the verb. Removing that pre-flight read
  would let SFA overwrite an accountant's own record.
- **A rejected master is reported in `EXCEPTIONS`/`LINEERROR`, not
  `ERRORS`.** Checking `ERRORS` alone reports a rejection as a success.

To drain the queue once and watch it happen — useful for a bulk master
push, where you want to see which records Tally refused rather than find
out later on the Sync Queue screen:

```bash
npm run run-once
```

It processes every pending job strictly one at a time (Tally's gateway is
its UI thread and serves a single request at a time), prints each result,
then exits.

Run its tests:

```bash
npm test
```

Those tests pin the **exact** request strings that were live-verified
against the real instance (`test/verified-requests.test.js`), and assert
every request routes through the circuit breaker. They exist because an
unsupported element anywhere in a Tally request wedges its gateway — see
Known limitations — so a verified request drifting by even one FETCH field
is a production incident, not a detail. If you need to change one, verify
the new string live first, then update the expectation.

## Running it automatically (Windows Task Scheduler)

`deploy/` registers the agent as a scheduled task that starts at logon:

```powershell
cd tally-sync-agent\deploy
powershell -ExecutionPolicy Bypass -File .\install-task.ps1   # register + start
powershell -ExecutionPolicy Bypass -File .\task-status.ps1    # state + log tail
powershell -ExecutionPolicy Bypass -File .\uninstall-task.ps1 # remove
```

No administrator rights needed: the task runs as the signed-in user, which
is all the agent needs (it talks to localhost and writes to its own
folder). `install-task.ps1` adds `LOG_DIR` to `.env` so the unattended
agent writes `logs/agent-YYYY-MM-DD.log`, kept for 14 days.

**At logon, not at boot** - and that is deliberate. Tally serves its XML
gateway from its own UI thread, so it only answers while Tally is open on
someone's desktop. A boot-time task would start an agent with nothing to
talk to. The consequence is real and worth telling the customer: nothing
syncs while the machine sits at the login screen, or while Tally is
closed. That is Tally's constraint, not the task's.

**The task executes `node.exe` directly** - working directory set by the
scheduler, logging done by the agent. Do not reintroduce a `powershell` or
`cmd` wrapper: Task Scheduler's Stop kills only the process it launched,
so a wrapped agent survives as an orphan and keeps claiming jobs. That
actually happened during development - a "restart" left two agents racing
for the same queue, with the orphan's output going nowhere, so jobs
completed with no trace in the log. If you ever see jobs succeeding that
the log does not mention, check for a stray `node.exe` first.

On Linux use systemd instead - see the main project's
`docs/deployment.md` for the equivalent pattern already used for the
Laravel queue worker.

## Known limitations

- The exact Tally XML request/response shape was live-verified against the
  real "GDN Tally" instance on 2026-09-07 for `List of Ledgers`/`List of
  Godowns`/`List of Stock Items` — rows nest at
  `ENVELOPE.BODY.DATA.COLLECTION.<TAG>` and a row's name is the `NAME`
  XML attribute, not a child element (see `stockSnapshot.js`'s
  `rowsFromEnvelope()`/`nameToGuidMap()` and
  `docs/tally-sfa-integration.md`'s Known Limitations for the full story).
  A different customer's Tally setup, chart-of-accounts naming, or TDL
  customization may still need `src/tally.js` adjusted — expected
  integration work, not a bug.
- **Two hard-won rules for anyone extending `src/tally.js`**, both learned
  by breaking the real instance:
  1. **Anything unsupported in a request hangs the gateway — `<TYPE>` and
     `<FETCH>` fields alike.** `<TYPE>Stock Item Godown</TYPE>` isn't valid
     in TallyPrime 7.1 and locked the gateway until a dialog was dismissed
     on the Tally desktop; separately, adding two unused FETCH fields
     (`BASEUNITS, OPENINGBALANCE`) to an *already-verified* request wedged
     it just as hard. So: **treat a live-verified request as a fixed
     artifact** — don't edit any part of it without re-verifying live, and
     verify the exact string you intend to ship.
  2. **Tally serves one request at a time.** `buildSnapshot()`'s original
     `Promise.all` of three reads timed the gateway out and left it stuck.
     All Tally reads must stay sequential.
- Populated-response shapes are still unconfirmed for two parsers: a real
  voucher row (`ledgerSnapshot.js`) and a populated `BATCHALLOCATIONS.LIST`
  (`stockSnapshot.js`). The real company has no vouchers and no stock, so
  both came back empty — the requests are confirmed accepted, but each
  parser accepts several conventional field spellings until real data can
  confirm which Tally actually uses.
- Voucher **pushes** are additionally blocked by that instance running in
  **Educational Mode**, which restricts voucher entry to a few dates per
  month. See `docs/tally-sfa-integration.md`'s Known Limitations.
- Invoice is not implemented — nothing in the roadmap has needed a
  Tally-side Invoice pull yet (Delivery/Credit Note/Ledger are all
  implemented, as pushes or pulls — see point 2 above).
- JSON-format Tally gateways aren't implemented yet (`src/tally.js` is
  XML-only); a `TallyApiFormat.Json` connection would need a sibling client
  added here, selected the same way the PHP side does.
