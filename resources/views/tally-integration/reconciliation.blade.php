@extends('layouts.admin')

@section('title', 'Tally Reconciliation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tally-integration.dashboard') }}">Tally Integration</a></li>
    <li class="breadcrumb-item active">Reconciliation</li>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header">Sync Failures</div>
        <div class="card-body">
            @php $totalFailed = collect($failedSyncs)->sum(fn ($rows) => $rows->count()); @endphp

            @if ($totalFailed === 0)
                <p class="text-success mb-0"><i class="ti ti-circle-check me-1"></i>Nothing currently stuck — every Order/Delivery/Collection/Return has synced or is still pending.</p>
            @else
                @foreach ([['label' => 'Orders', 'rows' => $failedSyncs['orders'], 'route' => 'orders.show'], ['label' => 'Deliveries', 'rows' => $failedSyncs['deliveries'], 'route' => 'deliveries.show'], ['label' => 'Collections', 'rows' => $failedSyncs['collections'], 'route' => 'collection-entries.show'], ['label' => 'Sales Returns', 'rows' => $failedSyncs['returns'], 'route' => 'sales-returns.show']] as $group)
                    @if ($group['rows']->isNotEmpty())
                        <h6 class="mt-3">{{ $group['label'] }} ({{ $group['rows']->count() }})</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Error</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['rows'] as $row)
                                    <tr>
                                        <td class="font-monospace small">{{ $row->external_reference }}</td>
                                        <td class="text-danger small">{{ $row->sync_error }}</td>
                                        <td>
                                            @if ($group['route'])
                                                <a href="{{ route($group['route'], $row) }}" class="btn btn-outline-secondary btn-sm">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Dealer Balance Reconciliation</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Dealer</th>
                            <th>SFA Balance</th>
                            <th>Tally Balance</th>
                            <th>Difference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dealerBalances as $row)
                            <tr>
                                <td>{{ $row->dealer->name }}</td>
                                <td>{{ number_format($row->sfa_balance, 2) }}</td>
                                <td>{{ number_format($row->tally_balance, 2) }}</td>
                                <td class="{{ abs($row->difference) >= 0.01 ? 'text-danger fw-semibold' : '' }}">{{ number_format($row->difference, 2) }}</td>
                                <td><span class="badge text-bg-{{ $row->status->badgeColor() }}">{{ $row->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No dealer has a synced Tally ledger yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @foreach ([['label' => 'Dealers', 'rows' => $dealers], ['label' => 'Retailers', 'rows' => $retailers], ['label' => 'Products', 'rows' => $products]] as $group)
        <div class="card mb-4">
            <div class="card-header">{{ $group['label'] }}</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Tally GUID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($group['rows'] as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="font-monospace small">{{ $row['mapping']?->tally_guid ?? '—' }}</td>
                                    <td><span class="badge text-bg-{{ $row['status']->badgeColor() }}">{{ $row['status']->label() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
