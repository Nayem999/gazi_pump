'use strict';

/**
 * Builds Tally master-import XML for the SFA -> Tally push: a Ledger for a
 * dealer or retailer, a Stock Item for a product, a Godown for a depot.
 *
 * These are the only requests in this agent that CREATE permanent masters
 * in the customer's accounting system, so two rules apply that don't apply
 * to the read side:
 *
 *  1. **Every element here is a documented Tally master field.** An
 *     unsupported element does not fail cleanly — it pops a modal on the
 *     Tally desktop that blocks the HTTP gateway until a human dismisses
 *     it (see tally.js's fetchStockItemGodownStock note; this cost two
 *     restarts during development). Nothing speculative gets added.
 *  2. **Create only, never modify.** SFA does not own these masters once
 *     they exist; a record already in Tally is matched by GUID and left
 *     alone. Letting an SFA edit overwrite an accountant's own correction
 *     is the failure this direction must never produce.
 *
 * A warning that cost a live investigation to learn: **ACTION="Create"
 * against a name that already exists does NOT error.** Tally silently
 * ALTERS the existing master and answers CREATED 0 / ALTERED 1. So rule 2
 * above cannot be enforced by the verb alone — handlers.js checks for the
 * name first and refuses, and that check is what actually protects the
 * accountant's data.
 */

function escapeXml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * The master-import envelope. Same shape as the voucher one in vouchers.js
 * apart from REPORTNAME, which selects the import target — "All Masters"
 * is Tally's own name for the masters report and accepts LEDGER,
 * STOCKITEM and GODOWN messages alike.
 */
function wrapMasterEnvelope(masterXml) {
    return `<ENVELOPE>
 <HEADER>
  <TALLYREQUEST>Import Data</TALLYREQUEST>
 </HEADER>
 <BODY>
  <IMPORTDATA>
   <REQUESTDESC>
    <REPORTNAME>All Masters</REPORTNAME>
   </REQUESTDESC>
   <REQUESTDATA>
    <TALLYMESSAGE xmlns:UDF="TallyUDF">
${masterXml}
    </TALLYMESSAGE>
   </REQUESTDATA>
  </IMPORTDATA>
 </BODY>
</ENVELOPE>`;
}

/**
 * A dealer or retailer as a Tally Ledger.
 *
 * ISBILLWISEON is on because the whole point of a dealer ledger here is
 * per-invoice outstanding tracking — without it Tally keeps only a running
 * balance and the Outstanding report this system reads has nothing to
 * break down. The group must be one the pull side actually reads back
 * (TALLY_DEALER_LEDGER_GROUP / TALLY_RETAILER_LEDGER_GROUP), or the record
 * would go out and never return.
 */
function buildLedgerMasterXml(payload) {
    const name = escapeXml(payload.name);
    const lines = [
        `     <LEDGER NAME="${name}" ACTION="Create">`,
        `      <NAME>${name}</NAME>`,
        `      <PARENT>${escapeXml(payload.group)}</PARENT>`,
        '      <ISBILLWISEON>Yes</ISBILLWISEON>',
    ];

    // Optional contact fields, emitted only when SFA actually holds one —
    // an empty element is not the same as an absent one to Tally.
    if (payload.phone) {
        lines.push(`      <LEDGERPHONE>${escapeXml(payload.phone)}</LEDGERPHONE>`);
    }

    if (payload.email) {
        lines.push(`      <EMAIL>${escapeXml(payload.email)}</EMAIL>`);
    }

    if (payload.address) {
        lines.push('      <ADDRESS.LIST TYPE="String">');
        lines.push(`       <ADDRESS>${escapeXml(payload.address)}</ADDRESS>`);
        lines.push('      </ADDRESS.LIST>');
    }

    lines.push('     </LEDGER>');

    return wrapMasterEnvelope(lines.join('\n'));
}

/**
 * A product as a Tally Stock Item.
 *
 * BASEUNITS is required for an item that will ever carry a quantity, and
 * the unit must already exist in the company — Tally rejects an unknown
 * one rather than creating it, which is why the unit is configuration
 * (SFA_TALLY_STOCK_ITEM_UNIT) and not derived from SFA data.
 *
 * Deliberately no opening balance and no rate: Tally owns stock figures
 * and pricing history in this integration (see the ownership model in
 * docs/tally-sfa-integration.md), so pushing SFA's price as an item rate
 * would be SFA writing into a column it does not own.
 */
function buildStockItemMasterXml(payload) {
    const name = escapeXml(payload.name);
    const lines = [
        `     <STOCKITEM NAME="${name}" ACTION="Create">`,
        `      <NAME>${name}</NAME>`,
    ];

    // Omitted when unset, rather than defaulted to "Primary". Tally's
    // reserved roots are stored with a LEADING SPACE (" Primary" — read
    // live off this company's own godowns), which XML will not carry
    // reliably, and Tally rejects the space-less spelling outright: the
    // godown push failed with "Godown 'Primary' does not exist!". Leaving
    // PARENT out lets Tally file the item under its own default root,
    // which is the thing the guess was trying to name anyway.
    if (payload.group) {
        lines.push(`      <PARENT>${escapeXml(payload.group)}</PARENT>`);
    }

    lines.push(`      <BASEUNITS>${escapeXml(payload.unit)}</BASEUNITS>`);
    lines.push('     </STOCKITEM>');

    return wrapMasterEnvelope(lines.join('\n'));
}

/**
 * A depot as a Tally Godown. See the PARENT note inline — its exact
 * spelling is load-bearing and took four live attempts to establish.
 */
function buildGodownMasterXml(payload) {
    const name = escapeXml(payload.name);
    const lines = [
        `     <GODOWN NAME="${name}" ACTION="Create">`,
        `      <NAME>${name}</NAME>`,
        // An EMPTY element, which is neither "omit it" nor "name the root".
        // All four spellings were tried live: <PARENT>Primary</PARENT>, the
        // numeric entity &#32;Primary, a literal leading space, and omitting
        // PARENT altogether ALL failed with "Godown 'Primary' does not
        // exist!" — Tally supplies that default itself when the element is
        // absent, and this company's real root is stored as " Primary" with
        // a leading space that XML will not carry. Only <PARENT/> is
        // accepted. Do not "tidy" this into an omission.
        '      <PARENT/>',
    ];

    if (payload.address) {
        lines.push('      <ADDRESS.LIST TYPE="String">');
        lines.push(`       <ADDRESS>${escapeXml(payload.address)}</ADDRESS>`);
        lines.push('      </ADDRESS.LIST>');
    }

    lines.push('     </GODOWN>');

    return wrapMasterEnvelope(lines.join('\n'));
}

module.exports = {
    buildLedgerMasterXml,
    buildStockItemMasterXml,
    buildGodownMasterXml,
    wrapMasterEnvelope,
    escapeXml,
};
