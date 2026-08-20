@extends('layouts.admin')

@section('title', 'Roles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Roles</li>
@endsection

@section('content')
    <x-filter-bar :action="route('roles.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Role name..." value="{{ $filters['search'] ?? '' }}">
        </div>
    </x-filter-bar>

    <x-data-table
        title="All Roles"
        :create-url="auth()->user()->can('create', \Spatie\Permission\Models\Role::class) ? route('roles.create') : null"
        :paginator="$roles"
    >
        <x-slot:thead>
            <tr>
                <th>Name</th>
                <th>Permissions</th>
                <th>Users</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td><span class="badge text-bg-secondary">{{ $role->permissions_count }}</span></td>
                <td><span class="badge text-bg-secondary">{{ $role->users_count }}</span></td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        @can('update', $role)
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                        @endcan
                        @can('delete', $role)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm data-confirm-title="Delete this role?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted py-4">No roles found.</td>
            </tr>
        @endforelse

        <x-slot:cards>
            @forelse ($roles as $role)
                <div class="col">
                    <x-item-card icon="ti-shield-lock" icon-color="secondary" :title="$role->name">
                        <x-slot:meta>
                            <div>Permissions: {{ $role->permissions_count }}</div>
                            <div>Users: {{ $role->users_count }}</div>
                        </x-slot:meta>
                        <x-slot:actions>
                            @can('update', $role)
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                            @endcan
                            @can('delete', $role)
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm data-confirm-title="Delete this role?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                </form>
                            @endcan
                        </x-slot:actions>
                    </x-item-card>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">No roles found.</div>
            @endforelse
        </x-slot:cards>
    </x-data-table>
@endsection
