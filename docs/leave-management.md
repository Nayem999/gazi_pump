# Leave Management

Leave request submission, approval, and balance tracking — plus the one
place where leave writes through into attendance.

## What it is made of

| Table | Holds | Notes |
|---|---|---|
| `leave_types` | Casual, Sick, Annual, Unpaid… | `annual_quota` is only the **default** used when an entitlement is first set up |
| `leave_requests` | One request, submission to decision | `days` is the working-day count *as approved* |
| `leave_balances` | Entitlement per person, per type, per year | **Entitlement only** — days used are never stored |

## Two decisions worth knowing

### Days used are derived, never stored

`leave_balances` holds what someone is *entitled* to. What they have
*taken* is summed from their approved requests every time a balance is
read.

A stored counter would have to be adjusted on approve, on cancel, on a
request being deleted or restored, and on any correction to an approved
request. Miss one path and the number drifts away from the requests it
claims to summarise, with no way to tell which is right. Deriving it
cannot drift.

What *is* stored is what cannot be derived: the entitlement itself,
including carry-forward and any individual adjustment.

### `days` is stored, and that is not a contradiction

The working-day count is calculated at submission — weekends and recorded
holidays removed — and then kept. The holiday calendar can be edited
afterwards, and an approved request has to keep the number of days it was
actually approved for.

## The attendance integration

This is the part that changes existing behaviour.

`AttendanceStatus` gains a **`leave`** case, and approving a request marks
every working day it covers:

- **Only approved leave touches attendance.** A pending request changes
  nothing, so nobody can make a day disappear from their attendance simply
  by asking for it.
- **Approving never overwrites a day someone actually worked.** A
  Present/Late/HalfDay row stays as it is — the check-in happened, and
  that is better evidence than the request. Only an **Absent** row, or no
  row at all, becomes Leave.
- **The Absent case is the retroactive one.** Someone falls ill, the
  nightly `attendance:mark-absent` job marks them absent, and the leave is
  approved the next day. Approval replaces that row rather than leaving
  them recorded as a no-show.
- **Withdrawing approved leave removes those rows again** — but only rows
  still marked Leave. If they turned up and checked in after all, that row
  is theirs and is left alone.

`MarkAbsentAttendanceAction` needed no change: it already skips any date
that has an attendance row, and approved leave now puts one there.

### The attendance report

`ReportService::attendanceSummary()` gains `leave_count` and
`judged_days`, and **approved leave is removed from both sides of the
attendance rate** rather than counted as a day missed. Leave the manager
granted must not read as poor attendance.

A consequence worth stating: someone on leave for a whole period has
**no rate** (the report shows `—`), not a rate of zero. There were no
working days to judge, so 0% would be a claim the data does not support.

Nothing historical changes — there were no `leave` rows before this
module.

## Who can do what

| | Sales Executive | Sales/Area/Territory Manager | General Manager |
|---|---|---|---|
| Submit own leave | ✅ | ✅ | ✅ |
| Submit for someone else | ❌ | ✅ | ✅ |
| See others' leave | ❌ | own territories | all |
| Approve / reject | ❌ | own territories | all |
| Withdraw own request | ✅ | ✅ | ✅ |
| Manage leave types | ❌ | view only | ✅ |

Two rules are enforced beyond the permission itself:

- `LeaveRequest` uses `HasVisibilityScope`, so a manager only ever sees
  and decides leave for executives in their own territories.
- **Nobody approves their own leave**, even holding
  `leave-requests.approve`. `LeaveRequestPolicy::approve()` refuses when
  the request belongs to the approver.

## Balance is shown at the decision, not enforced

The request screen shows the requester's used and remaining days, and
warns when approving would take them past their entitlement — but does
**not** block it. Approving leave someone has not accrued is a real
decision a manager is entitled to make (unpaid leave, an advance against
next year). The balance goes negative and stays visible, which is more
useful than a refusal the manager has to work around outside the system.

## Mobile API

Self-service only, under `/api/v1/leave`:

| Method | Path | Purpose |
|---|---|---|
| GET | `/leave/types` | Active types available to request |
| GET | `/leave/balance?year=` | Balance per type; `remaining_days` may be negative |
| GET | `/leave/requests?status=` | The caller's own requests |
| POST | `/leave/requests` | Submit |
| GET | `/leave/requests/{id}` | One of the caller's own |
| DELETE | `/leave/requests/{id}` | Withdraw |

**There is deliberately no approve endpoint.** Deciding someone else's
leave is a web-admin action; exposing it here would put an approval button
one permission mistake away from every phone in the field. A test asserts
the route 404s.

`user_id` is pinned to the caller on the list endpoint — it is never read
from the query string.

Validation is split on purpose: the FormRequests check *shape* only, while
whether the leave is legitimate (date ordering, overlaps, half-day rules,
whether the range has any working days) lives in
`LeaveRequestService::submit()`, which the web form and the API both go
through. Duplicating those rules is how the two surfaces would drift.

## Rules the service enforces

- End date cannot precede the start date.
- A half day is a single date and counts 0.5.
- A range containing no working days is refused rather than accepted as
  zero days.
- A new request cannot overlap the same person's **pending or approved**
  leave — pending counts, or two overlapping requests could both be
  approved. Rejected and cancelled requests do not block.
- A decided request cannot be decided again.
- Approved leave that has **already started** cannot be withdrawn: the
  attendance rows exist and the days were genuinely taken.

## Setup

`LeaveTypeSeeder` creates Casual / Sick / Annual / Unpaid with common
quotas, keyed on `code` so re-seeding never duplicates a type or
overwrites an adjusted quota. Edit them to match the customer's policy.

### The entitlement screen — Leave Entitlements

**Admin → Leave Entitlements** (`/leave-balances`) is where days are
granted. One row per person, per type, per year.

**Set Up Entitlements** seeds the missing rows for a year from each type's
quota. It is the action that makes the module usable in January — without
it somebody adds one row per person per type by hand. Optionally limited to
named employees, so a late joiner can be set up alone.

It is **safe to press twice**, and the difference matters:

- An entitlement that already exists is left exactly as it is, including
  one adjusted by hand. A re-run reports *"Nothing to set up"* rather than
  a misleading success.
- An entitlement that was **deliberately deleted is not reinstated**.
  Deleting one is a decision; a bulk set-up must not quietly undo it.

Individual rows can be added or edited for pro-rating, carry-forward, or
any per-person arrangement. On the edit form the employee, type and year
are **read-only** — they are the entitlement's identity, and moving one
would silently re-grant one person's days to another. Delete and re-add
instead, which leaves both acts on the audit trail.

Deleting an entitlement makes that person fall back to the leave type's
default quota rather than dropping to zero, which is why the confirmation
says so.

**Days taken are not editable here, or anywhere.** This screen owns only
the half that cannot be derived.

### A trap worth knowing

`leave_balances` is unique on `(user_id, leave_type_id, year)` **and**
soft-deletes, so a trashed row still occupies the key while being
invisible to a default-scoped query. A plain insert over one fails with a
duplicate-key error that reads as a bug rather than as "this entitlement
was deleted earlier".

`LeaveBalanceService::saveEntitlement()` therefore looks `withTrashed()`
and **revives** the existing row, keeping its id so the audit trail
survives. `setUpFor()` uses the same lookup. Both paths are covered by
tests.

### Permissions

Granting days sits with **General Manager and Super Admin only**, on its
own `leave-balances.*` permission set — deliberately *not* added to
`$orgModules`, since that list hands view rights to every line manager,
and one manager browsing the whole company's entitlements is not the same
as them approving their own team's leave.

A test pins the separation: a Territory Manager who holds
`leave-requests.approve` still gets a 403 on the entitlement screen.

Where no entitlement row exists yet, balances fall back to the type's own
quota rather than reporting zero — so the module reads sensibly before
anyone has been formally set up.
