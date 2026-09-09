@extends('layouts.admin')

@section('title', 'Sync Logs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tally-integration.dashboard') }}">Tally Integration</a></li>
    <li class="breadcrumb-item active">Sync Logs</li>
@endsection

@section('content')
    <x-filter-bar :action="route('tally-integration.sync-logs')">
        <div class="col-md-3">
            <label class="form-label">Entity Type</label>
            <select name="entity_type" class="form-select">
                <option value="">All</option>
                @foreach ($entityTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['entity_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
    </x-filter-bar>

    <x-data-table title="Sync Logs" :paginator="$logs">
        <x-slot:thead>
            <tr>
                <th>Reference</th>
                <th>Entity</th>
                <th>Direction</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Responded</th>
                <th>Tally Voucher</th>
                <th>Error</th>
            </tr>
        </x-slot:thead>

        @forelse ($logs as $log)
            <tr>
                <td class="font-monospace small">{{ $log->external_reference ?? '—' }}</td>
                <td>{{ $log->entity_type->label() }}</td>
                <td>{{ $log->direction->label() }}</td>
                <td>
                    <span class="badge text-bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'retry' ? 'warning' : 'danger') }}">
                        {{ ucfirst($log->status) }}
                    </span>
                </td>
                <td>{{ $log->request_time->format('d M Y, h:i A') }}</td>
                <td>{{ $log->response_time?->format('d M Y, h:i A') ?? '—' }}</td>
                <td>{{ $log->tally_voucher_number ?? '—' }}</td>
                <td class="small text-danger">{{ $log->error_message ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No sync logs found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
