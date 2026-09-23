# Navigation

## The shape

The sidebar is a flex column of three bands:

| Band | Behaviour |
|---|---|
| Brand | fixed height, always visible |
| `.sidebar-scroll` | the menu — **this is the only part that scrolls** |
| `.sidebar-footer` | pinned to the bottom: **Settings** and **Guide** |

Settings and Guide are destinations you reach from anywhere rather than
steps in a workflow, and the menu is long enough that anything at its end
is effectively hidden. Pinning them means menu length never buries them.

### Two CSS rules that are load-bearing

Both were found by the layout breaking, so neither is decoration:

- **`flex-wrap: nowrap` on `.sidebar-scroll`.** Bootstrap's `.nav` sets
  `flex-wrap: wrap`. Harmless while the whole sidebar scrolled, but once
  the nav became a height-constrained flex item it wrapped into a *second
  column* of half-cut labels instead of scrolling.
- **`min-height: 0` on `.sidebar-scroll`.** A flex item's `min-height`
  defaults to `auto`, which refuses to shrink below its content and so
  defeats `overflow-y` entirely.

## What lives where

The main menu keeps what the business **operates** day to day. Settings
holds what an administrator **configures** once and then leaves alone.
That split is what keeps the main menu short enough to scan — it went
from 45+ entries to around 30.

**Main menu:** Dashboard · Order Operations · Performance · Field
Operations (incl. Leave Requests) · GIS · Reports · Dealer Management ·
Product Management · Customer Requests · Communication.

**Settings** (`/settings`): General · Access Control · Organization ·
Logistics · Leave · Content · System — 23 screens in all.

Two judgement calls worth recording:

- **Inquiries and Visit Requests stayed in the main menu.** They are
  incoming customer work, not setup. The portal *content* they relate to
  (news, promotions, FAQs, service centres, brochures) did move, because
  that is written once and left alone.
- **Announcements moved to Settings → Content** and was removed from
  Communication, so it appears in exactly one place.

## Settings

`App\Support\SettingsNavigation` is the single source of truth: sections,
their items, each item's icon, description and gating permission.

- `for($user)` returns the list with anything the viewer cannot reach
  removed. An empty section disappears rather than rendering as a heading
  with nothing under it.
- `availableTo($user)` decides whether the pinned **Settings** link is
  rendered at all. **A field executive has no setup screens, so they get
  no link** — a link that answers 403 is worse than no link.
- `isActive()` highlights Settings while you are on any of its screens.
  Matched on route name, not URL, because most of these live outside a
  `/settings` prefix; they were operational screens before Settings
  adopted them.
- `for()` also skips any item whose route does not exist, so a
  half-installed module cannot take the whole navigation down with it. A
  test asserts every declared route resolves, which catches a rename
  leaving a dead tile behind.

### The URL move

`/settings` is now the hub. The company-profile form moved to
`/settings/company` but **kept its `settings.edit` route name**, so every
existing link and test that routes by name is unaffected.

The hub is gated on *having at least one reachable item*, not on a
settings permission of its own — someone who can manage holidays but not
the company profile still has a Settings section worth opening.

## Guide

`/guide` is the in-app manual, **open to every signed-in user and gated by
no permission**. It is the one page that explains why somebody sees what
they see, so withholding it from a user with few permissions would
withhold exactly the explanation they need. Nothing on it is sensitive: it
describes how the system works, not what is in it.

### Three parts

1. **How the work flows** — an inline SVG showing the system as **four
   parallel tracks**, not one queue, because that is what it actually is:

   | Track | Covers |
   |---|---|
   | The working day | Approved leave → Attendance → Visit plan → GPS tracking → Dealer visit |
   | The sale | Order → Approval → Depot allocation → Delivery → Sales return |
   | The money | Collection → Cash handover → Sync queue → TallyPrime |
   | Performance | Daily achievement → Approved → Monthly target |

   Solid arrows are the next step within a track; **dashed arrows are a
   feed into a different track** — a sales return joining the sync queue,
   and the day's figures being reported as an achievement. The
   distinction matters: without it the picture reads as one long sequence
   and implies an order that does not exist.

   Inline rather than an image so it inherits the theme, stays sharp at
   any zoom, and carries `<title>` and `<desc>` for screen readers.

   A test asserts every module named above still appears in the drawing,
   and that the `<desc>` mentions what the boxes show — a description
   drifting from the diagram would leave a screen-reader user with an
   account of a picture that no longer exists.
2. **User Guide** — working in the system day to day.
3. **Administrator Guide** — configuring and running it.

### The content lives in markdown, not in the view

Both guides are `docs/USER_GUIDE.md` and `docs/ADMIN_GUIDE.md`, rendered at
request time. **One copy of the documentation**: the page cannot drift
from the files a developer reads, and editing the manual needs no Blade.

`GuideController::render()` does four things worth knowing:

- **Strips embedded HTML** (`html_input => strip`). The files are ours,
  but they are read off disk at request time, and a page that renders
  whatever HTML happens to be in a file is one bad deployment away from
  being an injection point.
- **Prefixes every heading id with the guide key** (`user-`, `admin-`).
  Both files use the same heading levels, so unprefixed ids would collide
  and every contents link would land on whichever came first.
- **Builds the contents list in the same pass**, from `h2` only — `h3` is
  detail within a topic and would bury the topics themselves.
- **Wraps each table in its own horizontal scroller.** Markdown tables are
  the first thing to push a phone sideways, and generated markdown has no
  element to hang that on otherwise.

A test asserts every `href="#…"` on the page has a matching `id`, which is
how a contents list of this kind usually rots.

### It still adapts to the reader

The contents rail ends with what *this* account can configure, and someone
with no setup access is told so explicitly ("No configuration access")
rather than left wondering where Settings went.

### Anchors and the fixed topbar

Headings carry `scroll-margin-top: calc(var(--topbar-height) + 1rem)`.
Without it a clicked anchor lands under the fixed topbar, putting the
heading you asked for just off screen.

## Adding a screen to Settings

Add an entry to the relevant section in `SettingsNavigation::sections()`
with its `permission`, and remove the link from
`resources/views/layouts/partials/admin-sidebar.blade.php` if it had one.
Nothing else needs touching — the hub, the pinned link's visibility and
its active state all derive from that one list.
