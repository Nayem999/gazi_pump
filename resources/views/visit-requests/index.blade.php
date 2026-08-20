@extends('layouts.admin')

@section('title', 'Visit Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">Visit Requests</li>
@endsection

@section('content')
    <x-filter-bar :action="route('visit-requests.index')">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Address..." value="{{ $filters['search'] ?? '' }}">
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
    </x-filter-bar>

    <x-data-table title="Visit Requests" :paginator="$visitRequests">
        <x-slot:thead>
            <tr>
                <th>Customer</th>
                <th>Preferred Date</th>
                <th>Address</th>
                <th>Status</th>
                <th>Submitted</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($visitRequests as $visitRequest)
            <tr>
                <td>{{ $visitRequest->customerAccount?->name ?? '—' }}</td>
                <td>{{ $visitRequest->preferred_date->format('M d, Y') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($visitRequest->address, 50) }}</td>
                <td><span class="badge text-bg-{{ $visitRequest->status->badgeColor() }}">{{ $visitRequest->status->label() }}</span></td>
                <td>{{ $visitRequest->created_at->format('M d, Y H:i') }}</td>
                <td class="text-end">
                    @can('view', $visitRequest)
                        <a href="{{ route('visit-requests.show', $visitRequest) }}" class="btn btn-outline-secondary btn-sm" title="View"><i class="ti ti-eye"></i></a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No visit requests found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
