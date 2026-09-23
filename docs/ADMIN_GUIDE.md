# Administrator Guide

Configuring and running Gazi Pump SFA.

Everything described here lives under **Settings**, pinned at the bottom of
the sidebar. The main menu holds what the business operates day to day;
Settings holds what is set up once and then left alone.

## Roles and permissions

Access is granted to **roles**, never to individual accounts. Assign
someone a role and they inherit its permissions; change a role and
everyone holding it changes with it.

| Role | Broadly |
|---|---|
| Super Admin | Everything, including company profile and permanent deletes |
| General Manager | Full operational control, most setup, approvals |
| Sales / Area / Territory Manager | Their own territories: approvals, reports, read-only master data |
| Sales Executive | Their own records only, through the mobile app and a limited admin view |

Two things are enforced beyond the permission itself:

- **Scope.** A manager sees only people in their own territories. A field
  executive sees only their own records. This applies to lists, reports
  and single-record screens alike.
- **Self-approval is refused.** Nobody approves their own leave, whatever
  role they hold.

Roles are seeded, so the authoritative list lives in
`database/seeders/RolePermissionSeeder.php` rather than being edited ad
hoc. Re-running the seeder is safe and is how new permissions reach
existing roles.

### A common cause of confusion

If a newly added menu does not appear for somebody, the usual cause is a
**stale permission cache** on their existing session, not a missing
permission. Flushing it fixes it:

```
php artisan permission:cache-reset
```

## Setting up the organisation

Work outward from geography, because everything else references it.

1. **Divisions → Districts → Thanas** — the geographic tree.
2. **Territories** — the sales areas, mapped onto that geography.
3. **Sales Teams** — team structure and the product lines each carries.
4. **Users** — staff accounts, their role, and the territories they cover.
   A manager with no territories assigned sees nothing to manage.
5. **Depots, Vehicles, Drivers** — where stock is held and how it moves.

## Holidays

Recorded holidays are excluded from attendance and from leave day counts.
Setting them up is not cosmetic: without them, a public holiday marks
every field executive absent overnight, and leave taken across one is
charged against the employee's balance.

The weekend is configuration, not a holiday — set
`SFA_ATTENDANCE_WEEKEND_DAYS` to match local practice.

## Leave

Three screens, in the order you need them:

1. **Leave Types** — Casual, Sick, Annual, Unpaid and so on, each with the
   yearly quota it carries. The quota is only the *default* used when an
   entitlement is first created; changing it later never rewrites
   entitlements already granted.
2. **Leave Entitlements** — how many days each person is granted, per
   type, per year. **Set Up Entitlements** seeds the missing rows for a
   year from each type's quota, which is what makes the module usable in
   January instead of adding rows by hand.
3. **Leave Requests** — the approvals queue.

**Set Up Entitlements is safe to press twice.** An entitlement that
already exists is left exactly as it is, including one you adjusted by
hand, and a re-run reports "Nothing to set up" rather than a misleading
success. An entitlement you **deliberately deleted is not reinstated** —
deleting one is a decision, and a bulk set-up must not quietly undo it.

Individual rows handle pro-rating and carry-forward. On the edit form the
employee, type and year are read-only: they are the entitlement's
identity, and moving one would silently re-grant one person's days to
another. Delete and re-add instead, which leaves both acts on the audit
trail.

### What approval actually does

Approving a request marks each covered working day as **On Leave** in
attendance. Three rules govern that:

- Only **approved** leave touches attendance. A pending request changes
  nothing, so nobody can make a day disappear simply by asking.
- A day somebody **actually worked** is never overwritten. A
  Present/Late/Half Day record stays — the check-in happened, and that is
  better evidence than the request.
- An **Absent** record *is* replaced. This is the retroactive case:
  somebody falls ill, the overnight job marks them absent, and the leave
  is approved the next day.

Approving beyond somebody's balance is allowed. The screen warns you and
the balance goes negative and stays visible, because unpaid leave and
advances against next year are real decisions a manager is entitled to
make. A refusal here would only be worked around outside the system.

## Content

News, Promotions, FAQs, Service Centers and Brochures feed the customer
portal and the mobile app. Announcements go to staff. All of it is written
once and then left alone, which is why it sits under Settings rather than
in the operational menu.

## Tally integration

Data moves between this system and TallyPrime through a **Sync Agent**
that runs on the PC beside Tally. This system only ever *queues* work; the
agent collects it, talks to Tally locally, and reports back.

The single most important consequence: **if the agent is not running,
nothing syncs**. Jobs accumulate as Pending. The integration dashboard
says so plainly — it names the queue depth and when the agent last checked
in — rather than looking healthy.

Because Tally serves its gateway from its own interface, the agent can
only work while **Tally is open on someone's desktop**. Nothing syncs at
the login screen or with Tally closed. That is Tally's constraint, not a
limitation of the agent.

### Setting it up

1. **Tally Connections** — create a connection and copy the agent
   credential it shows once.
2. On the Tally PC, configure the agent's `.env` with that credential, the
   SFA URL, and the Tally gateway port (9000 by default — *not* 9999,
   which is Tally's licensing service and does not speak this protocol).
3. Name the ledger groups the customer actually files dealers under.
   `$Parent` matches the **immediate** parent group only, so naming an
   ancestor matches nothing.
4. Install the agent as a scheduled task so it starts at logon.

### Direction of travel

Tally owns stock figures, ledgers and pricing. SFA owns reservations and
allocations, and never writes them back.

Master data moves both ways. Records imported *from* Tally arrive
**inactive**, because Tally supplies a name and an identifier but not the
phone number, price or category this system requires — a human completes
and activates them. Creating masters *in* Tally is off by default
(`SFA_TALLY_MASTER_PUSH_ENABLED`) and only ever creates; it never modifies
a master an accountant already owns.

## Backups and the audit trail

**Activity Log** records who changed what and when. Deletes throughout the
system are soft by default, so a mistaken delete is recoverable from the
**Trashed** filter on the relevant screen; only a Super Admin can destroy
a record permanently.

## Routine maintenance

The scheduler drives overnight work — marking absences, notification
checks, Tally retries. It must actually be running:

```
php artisan schedule:work
```

After deploying changes:

```
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder
php artisan permission:cache-reset
php artisan config:clear && php artisan view:clear
```

Seeding roles is safe to repeat and is how new permissions reach existing
roles.
