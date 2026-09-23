@extends('layouts.admin')

@section('title', 'Guide')

@section('breadcrumb')
    <li class="breadcrumb-item active">Guide</li>
@endsection

@section('content')
    <div class="row g-4">
        {{-- Contents rail. Sticky so it stays put while a long guide
             scrolls past it, which is the whole reason it earns a column. --}}
        <div class="col-lg-3 order-lg-1 d-print-none">
            <div class="guide-toc">
                <div class="card">
                    <div class="list-group list-group-flush small">
                        <a href="#workflow" class="list-group-item list-group-item-action fw-semibold">
                            <i class="ti ti-route me-1"></i>How the work flows
                        </a>
                    </div>
                </div>

                @foreach ($sections as $section)
                    <div class="card mt-3">
                        <div class="card-header py-2 small fw-semibold">
                            <i class="ti {{ $section['icon'] }} me-1"></i>{{ $section['title'] }}
                        </div>
                        <div class="list-group list-group-flush small">
                            @foreach ($section['contents'] as $entry)
                                <a href="#{{ $entry['id'] }}" class="list-group-item list-group-item-action">
                                    {{ $entry['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if ($canConfigure)
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="small fw-semibold mb-2">You can configure</div>
                            <ul class="small text-muted mb-3 ps-3">
                                @foreach ($settingsSections as $settingsSection)
                                    <li>{{ $settingsSection['label'] }} <span class="text-muted">({{ count($settingsSection['items']) }})</span></li>
                                @endforeach
                            </ul>
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="ti ti-settings me-1"></i>Open Settings
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="small fw-semibold mb-1">No configuration access</div>
                            <p class="text-muted small mb-0">
                                Your role includes no setup screens, which is why there is no Settings link in your
                                sidebar. That is expected for field roles.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-9 order-lg-0 guide-content">
            <div class="mb-4">
                <h4 class="mb-1">{{ config('app.name') }} guide</h4>
                <p class="text-muted mb-0">
                    How the work flows, then the two manuals &mdash; one for working in the system, one for
                    running it. Both are the documentation shipped with the software, so this page cannot drift
                    from it.
                </p>
            </div>

            <section id="workflow" class="mb-5">
                <div class="card">
                    <div class="card-header">How the work flows</div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Four tracks running in parallel, not one queue. Solid arrows are the next step in a
                            track; dashed ones are a feed into a different track.
                        </p>

                        <div class="row g-2 small text-muted mb-3">
                            <div class="col-md-6 col-xl-3">
                                <strong class="d-block text-body">The working day</strong>
                                Leave, attendance, the visit plan, the GPS trail and the visits themselves.
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <strong class="d-block text-body">The sale</strong>
                                Order, approval, depot allocation, delivery &mdash; and returns coming back.
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <strong class="d-block text-body">The money</strong>
                                Collection, handover, then the sync queue that settles it in Tally.
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <strong class="d-block text-body">Performance</strong>
                                The daily achievement, once approved, moves the monthly target.
                            </div>
                        </div>

                        {{-- Inline SVG rather than an image: it inherits the
                             theme's colours, stays sharp at any zoom, and the
                             title/desc make it readable to a screen reader
                             instead of being an unlabelled picture. --}}
                        <div class="guide-diagram">
                            <svg viewBox="0 0 1000 690" role="img" aria-labelledby="wf-title wf-desc"
                                 xmlns="http://www.w3.org/2000/svg" width="100%" height="auto">
                                <title id="wf-title">The Gazi Pump SFA workflow</title>
                                <desc id="wf-desc">
                                    Four tracks. The working day: approved leave marks the day as leave;
                                    otherwise the executive checks in, follows the visit plan for that day, is
                                    tracked by GPS, and visits dealers. The sale: an order is previewed and
                                    placed, approved by a manager, allocated to a depot and delivered, and
                                    goods that come back raise a sales return. The money: collections are
                                    taken, handed over and confirmed, then queued for the Sync Agent to write
                                    into TallyPrime, which owns stock and ledgers. Performance: the figures
                                    for the day are reported as a daily achievement, approved, and roll into
                                    the monthly target. Every change is recorded in the activity log.
                                </desc>

                                <defs>
                                    <marker id="wf-arrow" viewBox="0 0 10 10" refX="9" refY="5"
                                            markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                        <path d="M 0 0 L 10 5 L 0 10 z" fill="currentColor" />
                                    </marker>
                                </defs>

                                {{-- Solid arrows are the next step in a track; dashed
                                     ones are a feed into a different track. --}}
                                <g fill="none" stroke="currentColor" stroke-width="1.5"
                                   marker-end="url(#wf-arrow)" opacity=".5">
                                    <path d="M182 89 L211 89" />
                                    <path d="M377 89 L406 89" />
                                    <path d="M572 89 L601 89" />
                                    <path d="M767 89 L796 89" />
                                    <path d="M880 120 L880 196" />
                                    <path d="M798 229 L769 229" />
                                    <path d="M603 229 L574 229" />
                                    <path d="M408 229 L379 229" />
                                    <path d="M213 229 L184 229" />
                                    <path d="M295 260 L295 336" />
                                    <path d="M377 369 L406 369" />
                                    <path d="M572 369 L601 369" />
                                    <path d="M767 369 L796 369" />
                                    <path d="M377 509 L406 509" />
                                    <path d="M572 509 L601 509" />
                                </g>

                                <g fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5 4"
                                   marker-end="url(#wf-arrow)" opacity=".4">
                                    <path d="M100 260 L100 300 L640 300 L640 336" />
                                    <path d="M295 400 L295 476" />
                                </g>

                                <g font-size="11" font-weight="700" fill="currentColor" opacity=".55"
                                   letter-spacing="0.5">
                                    <text x="20" y="46">THE WORKING DAY</text>
                                    <text x="20" y="186">THE SALE</text>
                                    <text x="215" y="326">THE MONEY</text>
                                    <text x="215" y="466">PERFORMANCE</text>
                                </g>

                                <g>
                                    <rect x="20" y="60" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-primary-rgb), .12)" stroke="var(--bs-primary)" />
                                    <text x="100" y="84" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Approved leave</text>
                                    <text x="100" y="102" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">marks the day On Leave</text>

                                    <rect x="215" y="60" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-primary-rgb), .12)" stroke="var(--bs-primary)" />
                                    <text x="295" y="84" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Attendance</text>
                                    <text x="295" y="102" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">check in with GPS and photo</text>

                                    <rect x="410" y="60" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-primary-rgb), .12)" stroke="var(--bs-primary)" />
                                    <text x="490" y="84" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Visit plan</text>
                                    <text x="490" y="102" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">the route for the day</text>

                                    <rect x="605" y="60" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-primary-rgb), .12)" stroke="var(--bs-primary)" />
                                    <text x="685" y="84" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">GPS tracking</text>
                                    <text x="685" y="102" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">the trail actually travelled</text>

                                    <rect x="800" y="60" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-primary-rgb), .12)" stroke="var(--bs-primary)" />
                                    <text x="880" y="84" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Dealer visit</text>
                                    <text x="880" y="102" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">check-in verified by distance</text>
                                </g>

                                <g>
                                    <rect x="800" y="200" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-warning-rgb), .14)" stroke="var(--bs-warning)" />
                                    <text x="880" y="224" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Order</text>
                                    <text x="880" y="242" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">previewed, then placed</text>

                                    <rect x="605" y="200" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-warning-rgb), .14)" stroke="var(--bs-warning)" />
                                    <text x="685" y="224" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Approval</text>
                                    <text x="685" y="242" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">a manager decides</text>

                                    <rect x="410" y="200" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-info-rgb), .14)" stroke="var(--bs-info)" />
                                    <text x="490" y="224" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Depot allocation</text>
                                    <text x="490" y="242" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">shortfall reported, not invented</text>

                                    <rect x="215" y="200" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-info-rgb), .14)" stroke="var(--bs-info)" />
                                    <text x="295" y="224" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Delivery</text>
                                    <text x="295" y="242" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">vehicle and driver</text>

                                    <rect x="20" y="200" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-secondary-rgb), .16)" stroke="var(--bs-secondary)" />
                                    <text x="100" y="224" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Sales return</text>
                                    <text x="100" y="242" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">credited on what came back</text>
                                </g>

                                <g>
                                    <rect x="215" y="340" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-success-rgb), .14)" stroke="var(--bs-success)" />
                                    <text x="295" y="364" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Collection</text>
                                    <text x="295" y="382" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">cash or cheque</text>

                                    <rect x="410" y="340" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-success-rgb), .14)" stroke="var(--bs-success)" />
                                    <text x="490" y="364" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Cash handover</text>
                                    <text x="490" y="382" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">confirmed by a manager</text>

                                    <rect x="605" y="340" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-secondary-rgb), .16)" stroke="var(--bs-secondary)" />
                                    <text x="685" y="364" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Sync queue</text>
                                    <text x="685" y="382" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">collected by the Sync Agent</text>

                                    <rect x="800" y="340" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-danger-rgb), .12)" stroke="var(--bs-danger)" />
                                    <text x="880" y="364" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">TallyPrime</text>
                                    <text x="880" y="382" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">owns stock and ledgers</text>
                                </g>

                                <g>
                                    <rect x="215" y="480" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-primary-rgb), .12)" stroke="var(--bs-primary)" />
                                    <text x="295" y="504" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Daily achievement</text>
                                    <text x="295" y="522" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">order value and collection</text>

                                    <rect x="410" y="480" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-warning-rgb), .14)" stroke="var(--bs-warning)" />
                                    <text x="490" y="504" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Approved</text>
                                    <text x="490" y="522" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">only then does it count</text>

                                    <rect x="605" y="480" width="160" height="58" rx="10"
                                          fill="rgba(var(--bs-success-rgb), .14)" stroke="var(--bs-success)" />
                                    <text x="685" y="504" text-anchor="middle" font-size="12.5" font-weight="600"
                                          fill="currentColor">Monthly target</text>
                                    <text x="685" y="522" text-anchor="middle" font-size="10.5"
                                          fill="currentColor" opacity=".7">recalculated, then graded</text>
                                </g>

                                <g>
                                    <rect x="20" y="595" width="940" height="60" rx="10"
                                          fill="rgba(var(--bs-body-color-rgb), .06)"
                                          stroke="currentColor" stroke-opacity=".25" stroke-dasharray="4 3" />
                                    <text x="490" y="621" text-anchor="middle" font-size="12" font-weight="600"
                                          fill="currentColor">Approved leave keeps a day out of the attendance rate &mdash; it never counts as absence</text>
                                    <text x="490" y="640" text-anchor="middle" font-size="11"
                                          fill="currentColor" opacity=".7">every change above is recorded in the activity log</text>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            @foreach ($sections as $section)
                <section id="{{ $section['key'] }}" class="mb-5">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="ti {{ $section['icon'] }} fs-5"></i>
                        <h5 class="mb-0">{{ $section['title'] }}</h5>
                    </div>
                    <p class="text-muted">{{ $section['summary'] }}</p>

                    <div class="card">
                        <div class="card-body guide-prose">
                            {{-- Rendered from markdown the app itself ships and
                                 strips of any embedded HTML - see
                                 GuideController::render(). --}}
                            {!! $section['html'] !!}
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection
