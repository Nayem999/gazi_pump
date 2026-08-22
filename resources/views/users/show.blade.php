@extends('layouts.admin')

@section('title', $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    @if ($user->photo)
                        <img src="{{ $user->photoUrl() }}" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover">
                    @else
                        <i class="ti ti-user-circle display-1 text-secondary mb-2 d-block"></i>
                    @endif
                    <h5 class="mb-0">{{ $user->name }}</h5>
                    <div class="text-muted">{{ $user->designation }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-{{ $user->status ? 'success' : 'secondary' }}">
                            {{ $user->status ? 'Active' : 'Inactive' }}
                        </span>
                        @foreach ($user->roles as $role)
                            <span class="badge text-bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2 d-print-none">
                        @can('update', $user)
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline"
                                    data-confirm
                                    data-confirm-title="{{ $user->status ? 'Deactivate this user?' : 'Activate this user?' }}"
                                    data-confirm-text="{{ $user->status ? 'They will no longer be able to log in.' : 'They will be able to log in again.' }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $user->status ? 'danger' : 'success' }} btn-sm">
                                        <i class="ti ti-{{ $user->status ? 'ban' : 'user-check' }} me-1"></i>{{ $user->status ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-pencil me-1"></i>Edit
                            </a>
                        @endcan
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i>Print
                        </button>
                        <a href="{{ route('users.download-pdf', $user) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-file-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Profile Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Employee ID</dt>
                        <dd class="col-sm-8">{{ $user->employee_id ?? '—' }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $user->phone ?? '—' }}</dd>

                        <dt class="col-sm-4">Manager</dt>
                        <dd class="col-sm-8">{{ $user->manager?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Sales Team</dt>
                        <dd class="col-sm-8">{{ $user->salesTeam?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Territory</dt>
                        <dd class="col-sm-8">{{ $user->territory?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Direct Reports</dt>
                        <dd class="col-sm-8">{{ $user->subordinates->count() }}</dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $user->created_at?->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-4">Last Updated</dt>
                        <dd class="col-sm-8">{{ $user->updated_at?->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
