@extends('layouts.admin')

@section('title', 'Permissions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Permissions</li>
@endsection

@section('content')
    @foreach ($groupedPermissions as $module => $permissions)
        <div class="card mb-3">
            <div class="card-header bg-white text-capitalize fw-semibold">{{ $module }}</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                <th>Type</th>
                                <th>Assigned to Roles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                    <td>
                                        <span class="badge text-bg-secondary">{{ \App\Helpers\PermissionName::typeOf($permission->name)->value }}</span>
                                    </td>
                                    <td>{{ $permission->roles_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
