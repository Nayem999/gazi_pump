@extends('layouts.admin')

@section('title', 'Activity Log')

@section('breadcrumb')
    <li class="breadcrumb-item active">Activity Log</li>
@endsection

@section('content')
    <x-filter-bar :action="route('activity-log.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Description..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Module</label>
            <select name="log_name" class="form-select">
                <option value="">All</option>
                @foreach ($logNames as $logName)
                    <option value="{{ $logName }}" @selected(($filters['log_name'] ?? '') === $logName)>{{ \Illuminate\Support\Str::headline($logName) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Event</label>
            <select name="event" class="form-select">
                <option value="">All</option>
                @foreach ($events as $event)
                    <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ ucfirst($event) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">User</label>
            <select name="causer_id" class="form-select">
                <option value="">All</option>
                @foreach ($causers as $causer)
                    <option value="{{ $causer->id }}" @selected((string) ($filters['causer_id'] ?? '') === (string) $causer->id)>{{ $causer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
    </x-filter-bar>

    <x-data-table
        title="Activity Log"
        :export-url="auth()->user()->can('activity-log.export') ? route('activity-log.export', request()->query()) : null"
        :print-url="auth()->user()->can('activity-log.print') ? route('activity-log.print', request()->query()) : null"
        :paginator="$activities"
    >
        <x-slot:thead>
            <tr>
                <th>Date</th>
                <th>Causer</th>
                <th>Event</th>
                <th>Subject</th>
                <th>Description</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($activities as $activity)
            <tr>
                <td>{{ $activity->created_at->format('d M Y, h:i A') }}</td>
                <td>{{ $activity->causer?->name ?? 'System' }}</td>
                <td>
                    <span class="badge text-bg-{{ match ($activity->event) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'secondary',
                    } }}">
                        {{ ucfirst($activity->event ?? 'n/a') }}
                    </span>
                </td>
                <td>{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::singular($activity->log_name ?? '')) }} #{{ $activity->subject_id }}</td>
                <td>{{ \Illuminate\Support\Str::limit($activity->description, 60) }}</td>
                <td class="text-end">
                    <a href="{{ route('activity-log.show', $activity) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No activity found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
