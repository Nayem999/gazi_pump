@extends('layouts.admin')

@section('title', 'Tally Integration')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tally Integration</li>
@endsection

@section('content')
    @can('tally-integration.sync')
        <div class="card mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-semibold">Sync everything with Tally</div>
                    <div class="small text-muted">
                        Queues a refresh of dealers, retailers, products, depots and every mapped dealer's ledger.
                        The Sync Agent collects it within about a minute.
                        Stock arrives separately on the agent's own schedule.
                    </div>

                    @if ($waitingJobs > 0 && ! $agentOnline)
                        {{-- The stall the "already waiting for the agent" message used to hide. --}}
                        <div class="alert alert-warning py-2 px-3 small mt-2 mb-0">
                            <i class="ti ti-alert-triangle me-1"></i>
                            <strong>{{ $waitingJobs }} job(s) are queued but nothing is collecting them.</strong>
                            The Sync Agent
                            {{ $lastHeartbeat ? 'last checked in '.$lastHeartbeat->diffForHumans() : 'has never checked in' }}.
                            Start it on the machine running Tally (<code>npm start</code> in
                            <code>tally-sync-agent</code>) — clicking Sync Now again will not help.
                        </div>
                    @endif

                    @php($pushTotal = collect($pendingPush)->flatten()->count())

                    <div class="small mt-2">
                        @if (! $pushEnabled)
                            <span class="badge text-bg-secondary">Creating records in Tally is off</span>
                            <span class="text-muted ms-1">
                                This sync only reads from Tally. Set <code>SFA_TALLY_MASTER_PUSH_ENABLED=true</code>
                                to also create SFA-only dealers, products and depots in Tally.
                            </span>
                        @elseif ($pushTotal === 0)
                            <span class="badge text-bg-success">Creating records in Tally is on</span>
                            <span class="text-muted ms-1">Nothing here is missing from Tally right now.</span>
                        @else
                            {{-- Named outright: this button writes into the customer's accounts. --}}
                            <span class="badge text-bg-warning">Will create {{ $pushTotal }} record(s) in Tally</span>
                            <ul class="text-muted mb-0 mt-1 ps-3">
                                @foreach ($pendingPush as $entityType => $names)
                                    <li>
                                        <span class="text-capitalize">{{ str_replace('_', ' ', $entityType) }}</span>:
                                        {{ implode(', ', array_slice($names, 0, 5)) }}@if (count($names) > 5) and {{ count($names) - 5 }} more @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('tally-integration.sync-all') }}" class="d-print-none">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-refresh me-1"></i>Sync Now
                    </button>
                </form>
            </div>
        </div>
    @endcan

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-circle-check" label="Successful Today" value="{{ $successfulToday }}" color="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-clock" label="Pending" value="{{ $pending }}" color="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-alert-triangle" label="Failed" value="{{ $failed }}" color="danger" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-refresh" label="Retrying" value="{{ $retrying }}" color="warning" />
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Tally Connections
                    @can('tally-integration.configure')
                        <a href="{{ route('tally-connections.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    @endcan
                </div>
                <div class="card-body">
                    @forelse ($connections as $connection)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $connection->connection_name }}</div>
                                <div class="small text-muted">{{ $connection->tally_company_name }}</div>
                            </div>
                            <div class="text-end">
                                @if ($connection->isOnline())
                                    <span class="badge text-bg-success">&#9679; Connected</span>
                                @else
                                    <span class="badge text-bg-danger">&#9679; Offline</span>
                                @endif
                                <div class="small text-muted mt-1">Last heartbeat: {{ $connection->last_heartbeat_at?->format('h:i A') ?? '—' }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No Tally connections configured yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Overview</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Last Successful Sync</dt>
                        <dd class="col-sm-6">{{ $lastSuccessfulSync ? \Illuminate\Support\Carbon::parse($lastSuccessfulSync)->format('d M Y, h:i A') : '—' }}</dd>
                    </dl>
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a href="{{ route('tally-integration.sync-queue') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-list-check me-1"></i>Sync Queue</a>
                        <a href="{{ route('tally-integration.sync-logs') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-history me-1"></i>Sync Logs</a>
                        <a href="{{ route('tally-integration.mapping') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-link me-1"></i>Mapping</a>
                        @can('tally-integration.reconcile')
                            <a href="{{ route('tally-integration.reconciliation') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-git-compare me-1"></i>Reconciliation</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
