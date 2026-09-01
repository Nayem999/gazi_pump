@extends('layouts.admin')

@section('title', 'Cash Handover')

@section('breadcrumb')
    <li class="breadcrumb-item active">Cash Handover</li>
@endsection

@section('content')
    @if (! is_null($cashInHand))
        <div class="alert {{ $dailyLimit && $cashInHand > $dailyLimit ? 'alert-danger' : 'alert-info' }} d-flex justify-content-between align-items-center">
            <span>Cash in Hand for {{ $executives->firstWhere('id', (int) $filters['user_id'])?->name ?? 'this executive' }}</span>
            <strong>
                ৳ {{ number_format($cashInHand, 2) }}
                @if ($dailyLimit)
                    <span class="text-muted small">/ limit ৳ {{ number_format($dailyLimit, 2) }}</span>
                @endif
            </strong>
        </div>
    @endif

    <x-filter-bar :action="route('cash-handovers.index')">
        <div class="col-md-3">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-2'])
    </x-filter-bar>

    <x-data-table
        title="Cash Handovers"
        :create-url="auth()->user()->can('create', \App\Models\CashHandover::class) ? route('cash-handovers.create') : null"
        create-label="Record Handover"
        :paginator="$cashHandovers"
    >
        <x-slot:thead>
            <tr>
                <th>Executive</th>
                <th>Amount</th>
                <th>Handover Date</th>
                <th>Status</th>
                <th>Confirmed By</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($cashHandovers as $cashHandover)
            <tr>
                <td>
                    {{ $cashHandover->user?->name }}
                    <div class="text-muted small">{{ $cashHandover->user?->employee_id }}</div>
                </td>
                <td>৳ {{ number_format((float) $cashHandover->amount, 2) }}</td>
                <td>{{ $cashHandover->handover_date->format('d M Y') }}</td>
                <td><span class="badge text-bg-{{ $cashHandover->status->badgeColor() }}">{{ $cashHandover->status->label() }}</span></td>
                <td>{{ $cashHandover->confirmedBy?->name ?? '—' }}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        @if ($cashHandover->trashed())
                            @can('restore', $cashHandover)
                                <form method="POST" action="{{ route('cash-handovers.restore', $cashHandover->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                </form>
                            @endcan
                        @else
                            @can('view', $cashHandover)
                                <a href="{{ route('cash-handovers.show', $cashHandover) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                            @endcan
                            @if ($cashHandover->status === \App\Enums\CashHandoverStatus::Pending)
                                @can('confirm', $cashHandover)
                                    <form method="POST" action="{{ route('cash-handovers.confirm', $cashHandover) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success" title="Confirm"><i class="ti ti-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('cash-handovers.reject', $cashHandover) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="ti ti-x"></i></button>
                                    </form>
                                @endcan
                            @endif
                            @can('delete', $cashHandover)
                                <form method="POST" action="{{ route('cash-handovers.destroy', $cashHandover) }}" data-confirm data-confirm-title="Move this record to trash?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No cash handovers found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
