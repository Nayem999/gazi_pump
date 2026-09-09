'use strict';

const axios = require('axios');
const { XMLParser } = require('fast-xml-parser');

const parser = new XMLParser({ ignoreAttributes: false });

/**
 * Thin client for TallyPrime's local HTTP-XML gateway. Only XML is
 * implemented here (the real customer instance — "GDN Tally" — speaks
 * XML); a JSON variant would live alongside this file and be selected by
 * the connection's api_format, never hardcoded to one Tally version.
 *
 * Every request goes through post() so the circuit breaker below can't be
 * bypassed. Tally's gateway is served by its own UI thread: an
 * unsupported request pops a modal on the customer's desktop that blocks
 * the gateway until a human dismisses it, and each *further* request
 * queues another modal behind it. Learned the hard way against the real
 * instance — twice — so after a timeout this client refuses to send
 * anything for a cooldown rather than burying the customer in dialogs.
 */
class TallyClient {
    constructor({ host, port, breakerCooldownMs = 120000 } = {}) {
        this.baseUrl = `http://${host}:${port}`;
        this.breakerCooldownMs = breakerCooldownMs;
        this.wedgedAt = null;
    }

    /**
     * Single choke point for every Tally request: trips on a timeout,
     * refuses further sends during the cooldown, and clears on any
     * successful response.
     */
    /**
     * Same choke point as post(), but returns the raw response text.
     * Display reports (see fetchGodownSummary) carry meaning in element
     * ORDER, which the XML parser destroys by grouping siblings.
     */
    async postRaw(xml, timeout) {
        return this.post(xml, timeout, { raw: true });
    }

    async post(xml, timeout, { raw = false } = {}) {
        if (this.wedgedAt !== null) {
            const waitedMs = Date.now() - this.wedgedAt;

            if (waitedMs < this.breakerCooldownMs) {
                const remainingSec = Math.ceil((this.breakerCooldownMs - waitedMs) / 1000);

                throw new Error(
                    `Tally gateway looks wedged (a request timed out ${Math.round(waitedMs / 1000)}s ago). `
                    + `Not sending for another ${remainingSec}s — more requests just stack error dialogs on the `
                    + 'Tally desktop. Dismiss any dialog there, or restart Tally, to clear it.',
                );
            }

            this.wedgedAt = null;
        }

        try {
            const response = await axios.post(this.baseUrl, xml, {
                headers: { 'Content-Type': 'text/xml' },
                timeout,
            });

            this.wedgedAt = null;

            return raw ? String(response.data) : parser.parse(response.data);
        } catch (error) {
            // A timeout/abort is the wedged-gateway signature; a refused
            // connection just means Tally isn't running, which needs no
            // cooldown (it can come back at any moment).
            if (error.code === 'ECONNABORTED' || error.code === 'ETIMEDOUT') {
                this.wedgedAt = Date.now();
            }

            throw error;
        }
    }

    /**
     * A minimal, version-agnostic request that just proves the gateway is
     * alive and returns a well-formed envelope — mirrors
     * TallyConnectionService::testConnection()'s PHP-side request exactly,
     * so both sides of the integration agree on what "reachable" means.
     */
    async ping() {
        const xml = `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Collection</TYPE>
  <ID>List of Companies</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVCURRENTCOMPANY>##SVCurrentCompany</SVCURRENTCOMPANY>
   </STATICVARIABLES>
  </DESC>
 </BODY>
</ENVELOPE>`;

        return this.post(xml, 10000);
    }

    /**
     * Fetches Tally's ledger list under a given parent group (Sundry
     * Debtors covers both Dealer and Retailer ledgers in a typical chart of
     * accounts).
     *
     * Filtered with a System Formulae <FILTER>, not a bare <PARENT> child
     * element: verified live against the real "GDN Tally" company on
     * 2026-09-07 that Tally **silently ignores** <PARENT> here and returns
     * every ledger regardless (the original "the filter didn't restrict
     * results" mystery from Phase 1 — it was this, not the customer's
     * chart of accounts). The <FILTER> form below was verified in the same
     * session to filter correctly.
     *
     * `parentGroup` must be the ledger's **immediate** parent group:
     * $Parent holds one group name, not an ancestor chain, so
     * "Current Assets" returns zero rows even though Sundry Debtors is
     * filed beneath it (also verified live, same company).
     */
    async fetchLedgers(parentGroup = 'Sundry Debtors') {
        const collectionName = uniqueCollectionName('List of Ledgers');
        const xml = `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Collection</TYPE>
  <ID>${collectionName}</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
   </STATICVARIABLES>
   <TDL>
    <TDLMESSAGE>
     <COLLECTION NAME="${collectionName}" ISMODIFY="No">
      <TYPE>Ledger</TYPE>
      <FILTER>SFALedgerParentFilter</FILTER>
      <FETCH>NAME, GUID, ALTERID, PARENT</FETCH>
     </COLLECTION>
     <SYSTEM TYPE="Formulae" NAME="SFALedgerParentFilter">$Parent = "${escapeForFormula(parentGroup)}"</SYSTEM>
    </TDLMESSAGE>
   </TDL>
  </DESC>
 </BODY>
</ENVELOPE>`;

        return this.post(xml, 20000);
    }

    /**
     * Fetches Tally's Stock Item master list — the Product-side analog of
     * fetchLedgers() above, used to resolve/confirm each Product's
     * tally_guid mapping. Godown-wise quantities are a separate concern
     * (see fetchGodowns()/fetchStockItemGodownStock()) since a Stock Item
     * is one master record shared across every godown it has balance in.
     */
    async fetchStockItems() {
        const collectionName = uniqueCollectionName('List of Stock Items');
        const xml = `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Collection</TYPE>
  <ID>${collectionName}</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
   </STATICVARIABLES>
   <TDL>
    <TDLMESSAGE>
     <COLLECTION NAME="${collectionName}" ISMODIFY="No">
      <TYPE>Stock Item</TYPE>
      <FETCH>NAME, GUID, ALTERID, PARENT, BASEUNITS</FETCH>
     </COLLECTION>
    </TDLMESSAGE>
   </TDL>
  </DESC>
 </BODY>
</ENVELOPE>`;

        return this.post(xml, 20000);
    }

    /**
     * Fetches Tally's Godown master list — the Depot-side analog of
     * fetchLedgers(), used to resolve/confirm each Depot's tally_guid
     * mapping.
     */
    async fetchGodowns() {
        const collectionName = uniqueCollectionName('List of Godowns');
        const xml = `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Collection</TYPE>
  <ID>${collectionName}</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
   </STATICVARIABLES>
   <TDL>
    <TDLMESSAGE>
     <COLLECTION NAME="${collectionName}" ISMODIFY="No">
      <TYPE>Godown</TYPE>
      <FETCH>NAME, GUID, ALTERID, PARENT</FETCH>
     </COLLECTION>
    </TDLMESSAGE>
   </TDL>
  </DESC>
 </BODY>
</ENVELOPE>`;

        return this.post(xml, 20000);
    }

    /**
     * Fetches godown-wise stock quantities, over the given period, as
     * Stock Items each carrying a BATCHALLOCATIONS.LIST of per-godown
     * entries — this is what populates SFA's product_stocks (Tally-owned
     * columns only; reserved/allocated stay purely SFA-side, see
     * DepotAllocationService).
     *
     * Verified live against "GDN Tally" on 2026-09-07: this request —
     * **exactly this TYPE and exactly this FETCH list** — is accepted
     * (STATUS 1) and returns each item with a BATCHALLOCATIONS.LIST
     * element. It replaces an earlier attempt that used
     * `<TYPE>Stock Item Godown</TYPE>`, which is NOT a valid type in
     * TallyPrime 7.1 and hung the HTTP gateway outright.
     *
     * DO NOT add fields to the FETCH list without re-testing live. Adding
     * `BASEUNITS, OPENINGBALANCE` to this exact request (both unused by
     * stockSnapshot.js, which reads quantities from the per-godown
     * allocation children instead) hung the gateway just as hard as the
     * bad TYPE did — so a wrong/unsupported FETCH field is NOT harmlessly
     * ignored here, contrary to what this file previously claimed. Treat
     * the verified list as load-bearing.
     *
     * Still unverified: the *child field names* inside each allocation
     * entry. That company has no vouchers/stock yet, so every
     * BATCHALLOCATIONS.LIST came back empty — the list is reachable, but
     * what a populated entry calls its godown/quantity fields can only be
     * confirmed once real stock exists. stockSnapshot.js parses those
     * defensively for that reason.
     */
    async fetchStockItemGodownStock(fromDate, toDate) {
        const collectionName = uniqueCollectionName('SFA Item Godown Stock');
        const xml = `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Collection</TYPE>
  <ID>${collectionName}</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
    <SVFROMDATE>${toTallyDateFormat(fromDate)}</SVFROMDATE>
    <SVTODATE>${toTallyDateFormat(toDate)}</SVTODATE>
   </STATICVARIABLES>
   <TDL>
    <TDLMESSAGE>
     <COLLECTION NAME="${collectionName}" ISMODIFY="No">
      <TYPE>Stock Item</TYPE>
      <FETCH>NAME, GUID, CLOSINGBALANCE, BATCHALLOCATIONS.LIST</FETCH>
     </COLLECTION>
    </TDLMESSAGE>
   </TDL>
  </DESC>
 </BODY>
</ENVELOPE>`;

        return this.post(xml, 30000);
    }

    /**
     * Posts a voucher-import XML envelope (see vouchers.js) and parses
     * Tally's short-form <RESPONSE> — { CREATED, ALTERED, ERRORS,
     * LASTVCHID, LASTMID }. Tally's import response does NOT include the
     * human-readable voucher number directly; getting that back would need
     * a follow-up export query keyed on LASTVCHID, which is real
     * customer-specific-verification work, not implemented here (see
     * Known Limitations).
     */
    async createVoucher(xml) {
        const parsed = await this.post(xml, 20000);
        const created = Number(parsed?.RESPONSE?.CREATED ?? 0);
        const errors = Number(parsed?.RESPONSE?.ERRORS ?? 0);

        return { raw: parsed, created, errors, lastVoucherId: parsed?.RESPONSE?.LASTVCHID ?? null };
    }

    /**
     * Posts a master-import XML envelope (see masters.js) and parses the
     * same short-form <RESPONSE>.
     *
     * Read `created` and `errors` together, never `errors` alone: Tally
     * answers a Create for an existing name with CREATED 0 / ERRORS 1
     * rather than a transport failure, so a caller that only checks for an
     * exception would record a silent no-op as a success.
     *
     * The response carries no GUID for the new master, which is why the
     * push handler follows a successful create with a normal master read
     * to resolve it (see handlers.js) rather than inventing a request
     * shape that has never been verified against a real instance.
     */
    async createMaster(xml) {
        const parsed = await this.post(xml, 20000);
        const response = parsed?.RESPONSE ?? {};
        const created = Number(response.CREATED ?? 0);
        const altered = Number(response.ALTERED ?? 0);
        const errors = Number(response.ERRORS ?? 0);

        // EXCEPTIONS, not ERRORS, is where TallyPrime records a rejected
        // master, with the reason in LINEERROR. Verified live on
        // 2026-09-08: a godown filed under a non-existent parent came back
        // ERRORS 0 / EXCEPTIONS 1 / LINEERROR "Godown 'Primary' does not
        // exist!". Reading ERRORS alone would call that a success on any
        // request that also created something.
        const exceptions = Number(response.EXCEPTIONS ?? 0);
        const lineErrors = [response.LINEERROR]
            .flat()
            .filter((line) => line !== undefined && line !== null && String(line).trim() !== '')
            .map((line) => String(line).trim());

        return { raw: parsed, created, altered, errors, exceptions, lineErrors };
    }

    /**
     * Every voucher in a period, with its full ledger-entry breakdown —
     * the source for Phase 5's Ledger/Credit Note/Debit Note sync.
     *
     * Deliberately UNFILTERED, which is a correction rather than an
     * oversight. The original filtered the collection on
     * `$PartyLedgerName = "<dealer>"`; a real Journal captured live on
     * 2026-09-07 has an **empty PARTYLEDGERNAME** and names its ledgers
     * only inside `ALLLEDGERENTRIES.LIST`, so that filter silently
     * returned nothing for precisely the hand-entered vouchers an
     * accountant creates. Selecting the dealer's own lines is now the
     * parser's job (`ledgerSnapshot.toRows(parsed, ledgerName)`).
     *
     * Tally returns each voucher's full object view, so ALLLEDGERENTRIES.LIST
     * arrives without being named in FETCH — do not add it there (an
     * unsupported FETCH field can hang the gateway; see this file's
     * fetchStockItemGodownStock note).
     *
     * Verified live: STATUS 1, and it returns the real 1-Sep-2026 Journal
     * that the filtered version could not see.
     */
    async fetchVouchers(fromDate, toDate) {
        const collectionName = uniqueCollectionName('SFA All Vouchers');
        const xml = `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Collection</TYPE>
  <ID>${collectionName}</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
    <SVFROMDATE>${toTallyDateFormat(fromDate)}</SVFROMDATE>
    <SVTODATE>${toTallyDateFormat(toDate)}</SVTODATE>
   </STATICVARIABLES>
   <TDL>
    <TDLMESSAGE>
     <COLLECTION NAME="${collectionName}" ISMODIFY="No">
      <TYPE>Voucher</TYPE>
      <FETCH>DATE, GUID, ALTERID, VOUCHERTYPENAME, VOUCHERNUMBER, PARTYLEDGERNAME, AMOUNT, NARRATION</FETCH>
     </COLLECTION>
    </TDLMESSAGE>
   </TDL>
  </DESC>
 </BODY>
</ENVELOPE>`;

        return this.post(xml, 30000);
    }

    /**
     * Kept as a thin alias so callers reading "ledger vouchers" still find
     * it; the ledger name is no longer part of the *request* (see
     * fetchVouchers) and is applied when parsing instead.
     */
    async fetchLedgerVouchers(ledgerName, fromDate, toDate) {
        return this.fetchVouchers(fromDate, toDate);
    }

    /**
     * Tally's built-in **display** reports, which return a flat
     * DSPACCNAME/DSPSTKINFO stream rather than an object collection — see
     * stockReports.js for the parser and why it is regex-based.
     *
     * These are the only route to godown-wise quantities found to work on
     * TallyPrime 7.1: godown is absent from voucher exports entirely
     * (checked via the default object view, a dotted FETCH path, and an
     * explicit list FETCH — all returned no GODOWNNAME, and the batch
     * allocation has no godown key at all).
     *
     * Verified live 2026-09-07 against a real purchase (50 into Warehouse)
     * and sale (5 out of Main Location):
     *   Godown Summary -> Main Location -5 PCS, Warehouse 50 PCS
     *   Stock Summary  -> Pump Model 1 45 PCS
     *
     * Both are returned as raw XML text, NOT parsed: post() runs everything
     * through an XML parser that collapses this format's meaningful element
     * ordering, so these two deliberately bypass it via postRaw().
     */
    async fetchGodownSummary(fromDate, toDate) {
        return this.postRaw(this.displayReportXml('Godown Summary', fromDate, toDate), 20000);
    }

    async fetchStockSummary(fromDate, toDate) {
        return this.postRaw(this.displayReportXml('Stock Summary', fromDate, toDate, true), 20000);
    }

    displayReportXml(reportId, fromDate, toDate, explode = false) {
        return `<ENVELOPE>
 <HEADER>
  <VERSION>1</VERSION>
  <TALLYREQUEST>Export</TALLYREQUEST>
  <TYPE>Data</TYPE>
  <ID>${reportId}</ID>
 </HEADER>
 <BODY>
  <DESC>
   <STATICVARIABLES>
    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
    <SVFROMDATE>${toTallyDateFormat(fromDate)}</SVFROMDATE>
    <SVTODATE>${toTallyDateFormat(toDate)}</SVTODATE>${explode ? '\n    <EXPLODEFLAG>Yes</EXPLODEFLAG>' : ''}
   </STATICVARIABLES>
  </DESC>
 </BODY>
</ENVELOPE>`;
    }
}

/**
 * A System Formulae expression sits inside XML element text AND inside a
 * double-quoted TDL string literal — escape both a literal quote (so a
 * ledger name containing one can't break out of the TDL string) and the
 * standard XML entities (so it stays well-formed XML).
 */
/** ISO (Y-m-d) -> Tally's YYYYMMDD, same conversion as vouchers.js's toTallyDate(). */
function toTallyDateFormat(isoDate) {
    return String(isoDate).replace(/-/g, '');
}

/**
 * A collection name that Tally has never seen before.
 *
 * This is load-bearing, not cosmetic. Tally caches collection results by
 * NAME and, on a repeat request for a name it already served, returns the
 * rows with **unchanged fields omitted** — GUID included. Verified live
 * (2026-09-07): the same request under a reused name came back with
 * `GUID: MISSING` on every godown, while a fresh name returned the real
 * GUIDs.
 *
 * Since GUID is the entire basis of SFA's matching, reusing a fixed name
 * would have meant the agent worked on its first run and then silently
 * resolved nothing on every run afterwards — no error, no rows matched.
 * So every collection request gets a unique name.
 */
let collectionSequence = 0;

function uniqueCollectionName(base) {
    collectionSequence += 1;

    return `${base} ${Date.now().toString(36)}${collectionSequence}`;
}

function escapeForFormula(value) {
    return String(value ?? '')
        .replace(/"/g, '\\"')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

module.exports = { TallyClient };
