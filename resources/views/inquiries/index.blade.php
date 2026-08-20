@extends('layouts.admin')

@section('title', 'Inquiries')

@section('breadcrumb')
    <li class="breadcrumb-item active">Inquiries</li>
@endsection

@section('content')
    <x-filter-bar :action="route('inquiries.index')">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name, email, subject..." value="{{ $filters['search'] ?? '' }}">
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

    <x-data-table title="Inquiries" :paginator="$inquiries">
        <x-slot:thead>
            <tr>
                <th>Name</th>
                <th>Subject</th>
                <th>Product</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Received</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($inquiries as $inquiry)
            <tr>
                <td>
                    {{ $inquiry->name }}
                    <div class="text-muted small">{{ $inquiry->email }}</div>
                </td>
                <td>{{ $inquiry->subject }}</td>
                <td>{{ $inquiry->product?->name ?? '—' }}</td>
                <td>{{ $inquiry->customerAccount?->name ?? 'Guest' }}</td>
                <td><span class="badge text-bg-{{ $inquiry->status->badgeColor() }}">{{ $inquiry->status->label() }}</span></td>
                <td>{{ $inquiry->created_at->format('M d, Y H:i') }}</td>
                <td class="text-end">
                    @can('view', $inquiry)
                        <a href="{{ route('inquiries.show', $inquiry) }}" class="btn btn-outline-secondary btn-sm" title="View"><i class="ti ti-eye"></i></a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No inquiries found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
