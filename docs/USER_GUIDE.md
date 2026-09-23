# User Guide

Working in Gazi Pump SFA day to day.

What you see depends on your role. If a screen described here is missing
from your sidebar, your role does not carry that permission — the last
section explains how that works.

## Finding your way around

The sidebar groups screens by what they are for: Order Operations,
Performance, Field Operations, Reports, and so on. Only what your role
allows appears, so two people can see different menus on the same system.

Two links stay pinned at the bottom while the rest of the menu scrolls:

- **Settings** — everything an administrator configures. Not shown if your
  role has no setup screens.
- **Guide** — this page.

Every list screen works the same way: filters across the top, a table
below, and Export / Print buttons that respect whatever you have filtered.
Deleting moves a record to the trash rather than destroying it; the
**Trashed** filter finds it again.

## Attendance

Attendance records who was at work.

Field staff check in and out from the mobile app, which captures GPS and a
photo. A check-in more than the configured distance from the dealer's
registered location is marked **unverified** rather than rejected — phone
GPS is imperfect and shops move within a building.

Anyone with no check-in on a working day is marked **Absent**
automatically overnight. Weekends and recorded holidays are skipped, so a
missing entry on those days is expected rather than counted against
anybody.

| Status | Means |
|---|---|
| Present | Checked in on time |
| Late | Checked in after the grace period |
| Half Day | Recorded as a half day |
| Absent | No check-in on a working day |
| On Leave | Covered by approved leave |

## Applying for leave

Open **Leave Requests** and press **Apply for Leave**. Your remaining
balance for each leave type is shown above the form, so you can see what
you have before choosing.

Fill in the type, the dates and a reason, then submit. The request starts
as **Pending** and changes nothing until somebody approves it.

Rules worth knowing before you apply:

- Only **working days** count. Weekends and holidays inside your date
  range are not deducted from your balance.
- A **half day** applies to a single date and counts 0.5 days, so the
  From and To dates must match.
- You cannot book dates that overlap leave you have already requested,
  whether it is pending or already approved.
- A range that contains no working days at all is refused rather than
  accepted as zero days.

### Changing your mind

You can withdraw a request while it is **Pending**, and also after it has
been approved as long as the leave **has not started yet** — withdrawing
approved leave puts those days back and clears them from your attendance.

Once approved leave is under way it stays on record. Those days were
genuinely taken, so the system will not pretend otherwise.

### Checking your balance

**Leave Requests → Leave Balances** shows, per leave type: what you are
entitled to, anything carried forward from last year, what you have taken,
and what is left.

"Taken" counts **approved** requests only, and is added up from the
requests themselves rather than kept as a separate total — so the number
always agrees with the list. A negative balance means leave was approved
beyond your entitlement, which a manager is allowed to do.

## Orders

Orders are recorded against a dealer, with one line per product.

**Preview the order before submitting it.** The preview computes the
totals the dealer will actually be billed and is the moment to catch a
wrong quantity or an over-large discount — a discount beyond the
configured cap is rejected outright rather than quietly trimmed, so you
re-enter the correct figure or escalate it.

A submitted order waits for a manager's approval. Once approved it can be
allocated to a depot and dispatched; where one depot cannot cover a line
in full, the remainder is reported as a shortfall rather than invented.

## Collections

Collections record money taken from a dealer.

Cash and cheque are handled differently at the point of entry, and the
amount is checked against what the dealer actually owes. A small
overpayment is tolerated — field collections round up — but a collection
far beyond the outstanding balance almost always means the wrong dealer or
the wrong amount, so it is refused.

Cash you are holding is later handed over and confirmed, which is what
clears it from your own balance.

## Visits

**Visit Plans** are what you intend to do; **Dealer Visits** are what
happened. Checking in at a dealer records GPS, so a visit is evidenced
rather than asserted.

## Sales returns

A return is requested against an order, then approved or rejected,
dispatched, and finally received at the depot. The credit note raised in
accounts is based on what was **actually received**, not on what was
originally requested — those two differ often enough that assuming they
match would put wrong money on a dealer's ledger.

## Reports

Every report filters by date range and, where it makes sense, by territory
or person, then exports to Excel or prints to PDF.

What you see is limited to your own scope: a territory manager's reports
cover their own territories; a field executive sees their own figures.
This is why the same report can show different rows to two people.

One report behaviour worth knowing: approved leave is **excluded from the
attendance rate** rather than counted as a day missed, so authorised time
off does not read as poor attendance. Someone on leave for a whole period
shows no rate at all rather than nought per cent — there were no working
days to judge.

## Why you can and cannot see things

Access is decided by your **role**, not your individual account. A role
carries a set of permissions, and every menu, button and screen checks
them. If something is missing from your sidebar, your role does not carry
that permission — ask an administrator rather than looking harder.

Some screens are also **scoped** on top of that. Even with permission, a
territory manager sees only people in their own territories, and a field
executive sees only their own records. This is why two people with the
same menus can still see different rows.
