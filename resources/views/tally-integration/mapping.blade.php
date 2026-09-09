@extends('layouts.admin')

@section('title', 'Tally Mapping')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tally-integration.dashboard') }}">Tally Integration</a></li>
    <li class="breadcrumb-item active">Mapping</li>
@endsection

@section('content')
    <x-filter-bar :action="route('tally-integration.mapping')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Tally name..." value="{{ $filters['search'] ?? '' }}">
        </div>
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
            <label class="form-label">Sync Status</label>
            <select name="sync_status" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\TallyMappingSyncStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['sync_status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <x-data-table title="Tally Mappings" :paginator="$mappings">
        <x-slot:thead>
            <tr>
                <th>Entity Type</th>
                <th>SFA ID</th>
                <th>Tally Name</th>
                <th>Tally GUID</th>
                <th>Tally Alter ID</th>
                <th>Status</th>
                <th>Last Synced</th>
            </tr>
        </x-slot:thead>

        @forelse ($mappings as $mapping)
            <tr>
                <td>{{ $mapping->entity_type->label() }}</td>
                <td>{{ $mapping->sfa_id }}</td>
                <td>{{ $mapping->tally_name ?? '—' }}</td>
                <td class="font-monospace small">{{ $mapping->tally_guid ?? '—' }}</td>
                <td>{{ $mapping->tally_alter_id ?? '—' }}</td>
                <td><span class="badge text-bg-{{ $mapping->sync_status->badgeColor() }}">{{ $mapping->sync_status->label() }}</span></td>
                <td>{{ $mapping->last_synced_at?->format('d M Y, h:i A') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No mappings found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
