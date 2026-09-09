'use strict';

/**
 * Builds Tally voucher-import XML for the two Phase 2 push jobs (Sales
 * Order, Collection). These are deliberately ledger-level vouchers (one
 * debit line, one credit line, no per-product stock entries) — an itemized
 * voucher needs each product's Tally Stock Item name/godown, which Phase 3
 * (Depot/Stock) is what actually establishes. Once that mapping exists,
 * these builders should grow an <INVENTORYENTRIES.LIST> per line instead of
 * a single lump-sum ledger entry.
 *
 * Every customer's chart of accounts names its Sales/Cash ledgers
 * differently — "Sales Account" and "Cash" below are the common Tally
 * defaults, not something this code can know for certain without the
 * customer's own configuration. This is exactly the kind of
 * customer-specific setup called out in the project's Known Limitations.
 */

function escapeXml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function toTallyDate(isoDate) {
    return String(isoDate).replace(/-/g, '');
}

function wrapImportEnvelope(voucherXml) {
    return `<ENVELOPE>
 <HEADER>
  <TALLYREQUEST>Import Data</TALLYREQUEST>
 </HEADER>
 <BODY>
  <IMPORTDATA>
   <REQUESTDESC>
    <REPORTNAME>Vouchers</REPORTNAME>
   </REQUESTDESC>
   <REQUESTDATA>
    <TALLYMESSAGE xmlns:UDF="TallyUDF">
${voucherXml}
    </TALLYMESSAGE>
   </REQUESTDATA>
  </IMPORTDATA>
 </BODY>
</ENVELOPE>`;
}

function buildSalesVoucherXml(payload, externalReference) {
    const total = Number(payload.total_amount).toFixed(2);
    const partyName = escapeXml(payload.dealer_tally_name);

    return wrapImportEnvelope(`     <VOUCHER VCHTYPE="Sales" ACTION="Create">
      <DATE>${toTallyDate(payload.order_date)}</DATE>
      <VOUCHERTYPENAME>Sales</VOUCHERTYPENAME>
      <PARTYLEDGERNAME>${partyName}</PARTYLEDGERNAME>
      <REFERENCE>${escapeXml(externalReference)}</REFERENCE>
      <NARRATION>${escapeXml(payload.remarks || '')}</NARRATION>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>${partyName}</LEDGERNAME>
       <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
       <AMOUNT>-${total}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>Sales Account</LEDGERNAME>
       <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
       <AMOUNT>${total}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>
     </VOUCHER>`);
}

/**
 * Cash/Cheque payment-mode display rules (Phase 4): a Receipt voucher must
 * debit a different ledger depending on how the money actually arrived —
 * lumping every collection into "Cash" would misstate the customer's real
 * cash-in-hand vs. cheques-in-clearing. Bank Transfer/Mobile Banking also
 * clear through a bank ledger, not cash. "Cash"/"Bank" are still the common
 * Tally defaults, not a guarantee for this customer's own chart of accounts
 * (same caveat as the module doc comment above); BANKALLOCATIONS.LIST is
 * only meaningful for a real bank-clearing entry, never for Cash.
 */
function receiptLedgerFor(paymentMethod) {
    return paymentMethod === 'cash' ? 'Cash' : 'Bank';
}

function buildReceiptVoucherXml(payload, externalReference) {
    const amount = Number(payload.amount).toFixed(2);
    const partyName = escapeXml(payload.dealer_tally_name);
    const debitLedger = escapeXml(receiptLedgerFor(payload.payment_method));
    const isBankEntry = payload.payment_method !== 'cash';
    const bankAllocations = isBankEntry
        ? `
       <BANKALLOCATIONS.LIST>
        <TRANSACTIONTYPE>${payload.payment_method === 'cheque' ? 'Cheque' : 'Other'}</TRANSACTIONTYPE>
        <INSTRUMENTDATE>${toTallyDate(payload.collection_date)}</INSTRUMENTDATE>
        <INSTRUMENTNUMBER>${escapeXml(payload.reference_no || '')}</INSTRUMENTNUMBER>
        <AMOUNT>-${amount}</AMOUNT>
       </BANKALLOCATIONS.LIST>`
        : '';

    return wrapImportEnvelope(`     <VOUCHER VCHTYPE="Receipt" ACTION="Create">
      <DATE>${toTallyDate(payload.collection_date)}</DATE>
      <VOUCHERTYPENAME>Receipt</VOUCHERTYPENAME>
      <PARTYLEDGERNAME>${partyName}</PARTYLEDGERNAME>
      <REFERENCE>${escapeXml(externalReference)}</REFERENCE>
      <NARRATION>${escapeXml(payload.remarks || '')}</NARRATION>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>${debitLedger}</LEDGERNAME>
       <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
       <AMOUNT>-${amount}</AMOUNT>${bankAllocations}
      </ALLLEDGERENTRIES.LIST>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>${partyName}</LEDGERNAME>
       <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
       <AMOUNT>${amount}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>
     </VOUCHER>`);
}

/**
 * Delivery Note (spec's Delivery/Challan phase) — like the Sales/Receipt
 * vouchers above, ledger-level only (no per-product inventory entries yet;
 * see this file's own module doc comment). Vehicle/driver are SFA-only
 * masters (no Tally mapping), so they're recorded as plain narration text,
 * not a Tally ledger/party reference.
 */
function buildDeliveryNoteVoucherXml(payload, externalReference) {
    const partyName = escapeXml(payload.dealer_tally_name);
    const vehicleLine = payload.vehicle_number ? `Vehicle: ${payload.vehicle_number}. ` : '';
    const driverLine = payload.driver_name ? `Driver: ${payload.driver_name}. ` : '';
    const narration = `${vehicleLine}${driverLine}${payload.remarks || ''}`.trim();

    return wrapImportEnvelope(`     <VOUCHER VCHTYPE="Delivery Note" ACTION="Create">
      <DATE>${toTallyDate(payload.delivery_date)}</DATE>
      <VOUCHERTYPENAME>Delivery Note</VOUCHERTYPENAME>
      <PARTYLEDGERNAME>${partyName}</PARTYLEDGERNAME>
      <REFERENCE>${escapeXml(externalReference)}</REFERENCE>
      <NARRATION>${escapeXml(narration)}</NARRATION>
     </VOUCHER>`);
}

/**
 * Credit Note (spec's Phase 6 Sales Return workflow — pushed only once a
 * return reaches Received, and amounts are based on received_qty/
 * total_amount as the depot actually confirmed, never the original
 * request). Ledger-level only, same reasoning as the other vouchers above
 * — reverses the Sales voucher's effect on the dealer's ledger.
 */
function buildCreditNoteVoucherXml(payload, externalReference) {
    const total = Number(payload.total_amount).toFixed(2);
    const partyName = escapeXml(payload.dealer_tally_name);

    return wrapImportEnvelope(`     <VOUCHER VCHTYPE="Credit Note" ACTION="Create">
      <DATE>${toTallyDate(payload.return_date)}</DATE>
      <VOUCHERTYPENAME>Credit Note</VOUCHERTYPENAME>
      <PARTYLEDGERNAME>${partyName}</PARTYLEDGERNAME>
      <REFERENCE>${escapeXml(externalReference)}</REFERENCE>
      <NARRATION>${escapeXml(payload.remarks || '')}</NARRATION>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>${partyName}</LEDGERNAME>
       <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
       <AMOUNT>${total}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>Sales Account</LEDGERNAME>
       <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
       <AMOUNT>-${total}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>
     </VOUCHER>`);
}

module.exports = { buildSalesVoucherXml, buildReceiptVoucherXml, buildDeliveryNoteVoucherXml, buildCreditNoteVoucherXml };
