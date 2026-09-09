@extends('layouts.admin')

@section('title', 'Tally Connections')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tally-integration.dashboard') }}">Tally Integration</a></li>
    <li class="breadcrumb-item active">Connections</li>
@endsection

@section('content')
    @if (session('plain_agent_token'))
        <div class="alert alert-warning">
            <strong>Sync Agent credential (copy it now — it will not be shown again):</strong>
            <div class="input-group mt-2">
                <input type="text" class="form-control font-monospace" id="plainAgentToken" value="{{ session('plain_agent_token') }}" readonly>
                <button type="button" class="btn btn-outline-secondary" data-copy="{{ session('plain_agent_token') }}"><i class="ti ti-copy"></i> Copy</button>
            </div>
        </div>
    @endif

    <x-filter-bar :action="route('tally-connections.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Connection or company name..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    {{--
        bulkForm is a standalone empty form (see visit-plans/index.blade.php
        for the same fix and why): the row-level test/regenerate/toggle/
        delete forms below live inside the table, and nesting a form inside
        another form is invalid HTML that breaks the data-confirm dialogs.
        The bulk checkboxes and "Delete Selected" button use form="bulkForm"
        instead of physical nesting.
    --}}
    <form id="bulkForm" method="POST" action="{{ route('tally-connections.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected connections?">
        @csrf
    </form>

    <div>
        <x-data-table
            title="Tally Connections"
            :create-url="auth()->user()->can('create', \App\Models\TallyConnection::class) ? route('tally-connections.create') : null"
            :paginator="$connections"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Connection</th>
                    <th>Tally Company</th>
                    <th>Host</th>
                    <th>Format</th>
                    <th>Status</th>
                    <th>Last Heartbeat</th>
                    <th>Last Sync</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($connections as $connection)
                <tr>
                    <td>
                        @if (! $connection->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $connection->id }}" class="form-check-input row-checkbox" form="bulkForm">
                        @endif
                    </td>
                    <td>{{ $connection->connection_name }}</td>
                    <td>{{ $connection->tally_company_name }}</td>
                    <td>{{ $connection->host }}:{{ $connection->port }}</td>
                    <td>{{ $connection->api_format->label() }}</td>
                    <td>
                        @if ($connection->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $connection->is_active ? 'success' : 'secondary' }}">{{ $connection->is_active ? 'Active' : 'Inactive' }}</span>
                            <div class="small mt-1">
                                @if ($connection->isOnline())
                                    <span class="text-success">&#9679; Connected</span>
                                @else
                                    <span class="text-danger">&#9679; Offline</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>{{ $connection->last_heartbeat_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    <td>{{ $connection->last_successful_sync_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($connection->trashed())
                                @can('restore', $connection)
                                    <form method="POST" action="{{ route('tally-connections.restore', $connection->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $connection)
                                    <form method="POST" action="{{ route('tally-connections.force-destroy', $connection->id) }}" data-confirm data-confirm-title="Permanently delete this connection?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('testConnection', $connection)
                                    <form method="POST" action="{{ route('tally-connections.test-connection', $connection) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info" title="Test Connection"><i class="ti ti-plug-connected"></i></button>
                                    </form>
                                @endcan
                                @can('update', $connection)
                                    <form method="POST" action="{{ route('tally-connections.regenerate-token', $connection) }}" data-confirm data-confirm-title="Regenerate Sync Agent credential?" data-confirm-text="The current credential will stop working immediately.">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Regenerate Sync Agent Token"><i class="ti ti-key"></i></button>
                                    </form>
                                    <a href="{{ route('tally-connections.edit', $connection) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $connection)
                                    <form method="POST" action="{{ route('tally-connections.destroy', $connection) }}" data-confirm data-confirm-title="Move this connection to trash?">
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
                    <td colspan="9" class="text-center text-muted py-4">No Tally connections configured yet.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($connections as $connection)
                    <div class="col">
                        <x-item-card
                            icon="ti-plug-connected"
                            icon-color="{{ $connection->is_active ? 'success' : 'secondary' }}"
                            :title="$connection->connection_name"
                            :subtitle="$connection->tally_company_name"
                            :status-label="$connection->trashed() ? 'Trashed' : ($connection->isOnline() ? 'Connected' : 'Offline')"
                            :status-color="$connection->trashed() ? 'danger' : ($connection->isOnline() ? 'success' : 'danger')"
                        >
                            <x-slot:meta>
                                <div>Host: {{ $connection->host }}:{{ $connection->port }}</div>
                                <div>Format: {{ $connection->api_format->label() }}</div>
                                <div>Last Sync: {{ $connection->last_successful_sync_at?->format('d M Y, h:i A') ?? '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $connection->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $connection->id }}" class="form-check-input row-checkbox" form="bulkForm">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if (! $connection->trashed())
                                    @can('update', $connection)
                                        <a href="{{ route('tally-connections.edit', $connection) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $connection)
                                        <form method="POST" action="{{ route('tally-connections.destroy', $connection) }}" data-confirm data-confirm-title="Move this connection to trash?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endcan
                                @endif
                            </x-slot:actions>
                        </x-item-card>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">No Tally connections configured yet.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('tally-integration.configure')
            <div class="mt-2">
                <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
