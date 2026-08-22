@extends('layouts.admin')

@section('title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <x-filter-bar :action="route('users.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name, email, employee ID..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('users.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected users?">
        @csrf
        <x-data-table
            title="All Users"
            :create-url="auth()->user()->can('create', \App\Models\User::class) ? route('users.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\User::class) ? route('users.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\User::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\User::class) ? route('users.print', request()->query()) : null"
            :paginator="$users"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Manager</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($users as $user)
                <tr>
                    <td>
                        @if (! $user->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $user->employee_id ?? '—' }}</td>
                    <td>
                        {{ $user->name }}
                        <div class="text-muted small">{{ $user->email }}</div>
                    </td>
                    <td>{{ $user->designation }}</td>
                    <td>{{ $user->manager?->name ?? '—' }}</td>
                    <td>
                        @foreach ($user->roles as $role)
                            <span class="badge text-bg-secondary">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($user->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $user->status ? 'success' : 'secondary' }}">
                                {{ $user->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($user->trashed())
                                @can('restore', $user)
                                    <form method="POST" action="{{ route('users.restore', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore">
                                            <i class="ti ti-arrow-back-up"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('forceDelete', $user)
                                    <form method="POST" action="{{ route('users.force-destroy', $user->id) }}" data-confirm data-confirm-title="Permanently delete this user?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently">
                                            <i class="ti ti-trash-x"></i>
                                        </button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $user)
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $user)
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.toggle-status', $user) }}"
                                            data-confirm
                                            data-confirm-title="{{ $user->status ? 'Deactivate this user?' : 'Activate this user?' }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-{{ $user->status ? 'danger' : 'success' }}" title="{{ $user->status ? 'Deactivate' : 'Activate' }}">
                                                <i class="ti ti-{{ $user->status ? 'ban' : 'user-check' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $user)
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm data-confirm-title="Move this user to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No users found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($users as $user)
                    <div class="col">
                        <x-item-card
                            icon="ti-user"
                            icon-color="primary"
                            :image="$user->photo ? $user->photoUrl() : null"
                            :title="$user->name"
                            :subtitle="$user->employee_id ?? '—'"
                            :status-label="$user->trashed() ? 'Trashed' : ($user->status ? 'Active' : 'Inactive')"
                            :status-color="$user->trashed() ? 'danger' : ($user->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>{{ $user->email }}</div>
                                <div>{{ $user->designation }}</div>
                                <div>Manager: {{ $user->manager?->name ?? '—' }}</div>
                                <div class="mt-1">
                                    @foreach ($user->roles as $role)
                                        <span class="badge text-bg-secondary">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $user->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($user->trashed())
                                    @can('restore', $user)
                                        <form method="POST" action="{{ route('users.restore', $user->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $user)
                                        <form method="POST" action="{{ route('users.force-destroy', $user->id) }}" data-confirm data-confirm-title="Permanently delete this user?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $user)
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $user)
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.toggle-status', $user) }}"
                                                data-confirm
                                                data-confirm-title="{{ $user->status ? 'Deactivate this user?' : 'Activate this user?' }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-{{ $user->status ? 'danger' : 'success' }}" title="{{ $user->status ? 'Deactivate' : 'Activate' }}">
                                                    <i class="ti ti-{{ $user->status ? 'ban' : 'user-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm data-confirm-title="Move this user to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No users found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('users.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="ti ti-trash me-1"></i>Delete Selected
                </button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\User::class)
        <x-modal id="importModal" title="Import Users">
            <form id="importForm" method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, name, email, phone, designation.</div>
                </div>
            </form>
            <x-slot:footer>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="importForm" class="btn btn-primary">Import</button>
            </x-slot:footer>
        </x-modal>
    @endcan
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
