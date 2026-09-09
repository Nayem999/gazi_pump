'use strict';

/**
 * Parsers for Tally's built-in *display* reports (TALLYREQUEST=Export,
 * TYPE=Data), as opposed to the TDL Collections used everywhere else.
 *
 * These exist because godown-wise stock turned out not to be reachable
 * through voucher/collection exports at all on TallyPrime 7.1 — see
 * docs/tally-sfa-integration.md. Two built-in reports were verified live
 * against "GDN Tally" on 2026-09-07:
 *
 *   Godown Summary -> quantity per GODOWN, summed across all items
 *   Stock Summary  -> quantity per STOCK ITEM, summed across all godowns
 *
 * Neither yields the (item x godown) pair that SFA's product_stocks is
 * keyed on, and that pair is not derivable from the two of them (row and
 * column totals do not determine the cells). That limitation is the
 * documented open question, not something these parsers paper over.
 *
 * Display-report XML is a FLAT, alternating sequence with no nesting and
 * no depth markers:
 *
 *   <DSPACCNAME><DSPDISPNAME>Warehouse</DSPDISPNAME></DSPACCNAME>
 *   <DSPSTKINFO><DSPSTKCL><DSPCLQTY>50 PCS</DSPCLQTY>...</DSPSTKCL></DSPSTKINFO>
 *
 * So a name is paired with the quantity block that follows it. With
 * EXPLODEFLAG the same stream also carries stock-GROUP rows, which is why
 * callers pass the set of names they actually care about (godowns from
 * fetchGodowns(), items from fetchStockItems()) and rows are classified by
 * lookup rather than by guessing at position or depth.
 */

/** "50 PCS" / "-5 PCS" / "" -> 50 / -5 / 0 */
function quantityOf(raw) {
    const match = String(raw ?? '').match(/-?[\d.]+/);

    return match ? parseFloat(match[0]) : 0;
}

/**
 * Pairs each <DSPDISPNAME> with the <DSPCLQTY> that follows it.
 *
 * Parsed with a regex rather than an XML parser on purpose: the format is
 * a flat token stream where document order *is* the association, and
 * fast-xml-parser collapses the repeated sibling elements into arrays that
 * lose the interleaving between the two element types.
 *
 * @returns {Array<{name: string, quantity: number, rate: number, amount: number}>}
 */
function parseDisplayReport(xml) {
    const rows = [];
    const token = /<DSPDISPNAME>([^<]*)<\/DSPDISPNAME>|<DSPCLQTY>([^<]*)<\/DSPCLQTY>|<DSPCLRATE>([^<]*)<\/DSPCLRATE>|<DSPCLAMTA>([^<]*)<\/DSPCLAMTA>/g;

    let pending = null;
    let match;

    while ((match = token.exec(String(xml ?? ''))) !== null) {
        const [, name, qty, rate, amount] = match;

        if (name !== undefined) {
            if (pending) {
                rows.push(pending);
            }

            pending = { name: name.trim(), quantity: 0, rate: 0, amount: 0 };

            continue;
        }

        if (! pending) {
            continue;
        }

        if (qty !== undefined) {
            pending.quantity = quantityOf(qty);
        } else if (rate !== undefined) {
            pending.rate = quantityOf(rate);
        } else if (amount !== undefined) {
            pending.amount = quantityOf(amount);
        }
    }

    if (pending) {
        rows.push(pending);
    }

    return rows;
}

/**
 * Keeps only the rows whose name is one of `known` — the caller's real
 * godown or stock-item names. Everything else in the stream (stock groups,
 * totals) is dropped, which is what makes this safe to parse despite the
 * format carrying several hierarchy levels in one flat list.
 *
 * @param  {Iterable<string>} known
 */
function rowsMatching(xml, known) {
    const wanted = new Map();

    for (const name of known) {
        wanted.set(String(name).trim().toLowerCase(), String(name));
    }

    const seen = new Map();

    for (const row of parseDisplayReport(xml)) {
        const canonical = wanted.get(row.name.toLowerCase());

        // A name can legitimately repeat in an exploded report; the first
        // occurrence is the one that carries that level's own total.
        if (canonical && ! seen.has(canonical)) {
            seen.set(canonical, { ...row, name: canonical });
        }
    }

    return [...seen.values()];
}

module.exports = { parseDisplayReport, rowsMatching, quantityOf };
