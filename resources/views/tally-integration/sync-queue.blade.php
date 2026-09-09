@extends('layouts.admin')

@section('title', 'Sync Queue')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tally-integration.dashboard') }}">Tally Integration</a></li>
    <li class="breadcrumb-item active">Sync Queue</li>
@endsection

@section('content')
    <x-filter-bar :action="route('tally-integration.sync-queue')">
        <div class="col-md-4">
            <label class="form-label">Entity Type</label>
            <select name="entity_type" class="form-select">
                <option value="">All</option>
                @foreach ($entityTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['entity_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <x-data-table title="Sync Queue" :paginator="$items">
        <x-slot:thead>
            <tr>
                <th>Reference</th>
                <th>Entity</th>
                <th>Direction</th>
                <th>Status</th>
                <th>Attempts</th>
                <th>Last Attempt</th>
                <th>Next Attempt</th>
                <th>Outcome</th>
                <th>Error</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($items as $item)
            <tr>
                <td class="font-monospace small">{{ $item->external_reference }}</td>
                <td>{{ $item->entity_type->label() }}</td>
                <td>{{ $item->direction->label() }}</td>
                <td><span class="badge text-bg-{{ $item->status->badgeColor() }}">{{ $item->status->label() }}</span></td>
                <td>{{ $item->attempt_count }}</td>
                <td>{{ $item->last_attempt_at?->format('d M Y, h:i A') ?? '—' }}</td>
                <td>{{ $item->next_attempt_at?->format('d M Y, h:i A') ?? '—' }}</td>
                <td @class(['small']) @if ($detail = $item->outcomeDetail()) title="{{ $detail }}" @endif>
                    @forelse ($item->outcomeChips() as $chip)
                        <span class="badge text-bg-{{ $chip['tone'] }} me-1">{{ $chip['label'] }}</span>
                    @empty
                        <span class="text-muted">—</span>
                    @endforelse
                </td>
                <td class="small text-danger">{{ $item->error_code?->label() ?? $item->error_message }}</td>
                <td class="text-end">
                    @can('tally-integration.retry')
                        @if (in_array($item->status, [\App\Enums\SyncStatus::Failed, \App\Enums\SyncStatus::Retry], true))
                            <form method="POST" action="{{ route('tally-integration.sync-queue.retry', $item) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Retry"><i class="ti ti-refresh"></i></button>
                            </form>
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center text-muted py-4">No sync jobs found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
