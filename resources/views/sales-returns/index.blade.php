@extends('layouts.admin')

@section('title', 'Sales Returns')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Returns</li>
@endsection

@section('content')
    <x-filter-bar :action="route('sales-returns.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="External reference..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\SalesReturnStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <x-data-table title="Sales Returns" :paginator="$returns">
        <x-slot:thead>
            <tr>
                <th>Reference</th>
                <th>Order</th>
                <th>Dealer</th>
                <th>Requested By</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($returns as $return)
            <tr>
                <td class="font-monospace small">{{ $return->external_reference }}</td>
                <td><a href="{{ route('orders.show', $return->order) }}">#{{ $return->order_id }}</a></td>
                <td>{{ $return->dealer->name }}</td>
                <td>{{ $return->user->name }}</td>
                <td><span class="badge text-bg-{{ $return->status->badgeColor() }}">{{ $return->status->label() }}</span></td>
                <td class="text-end">
                    <a href="{{ route('sales-returns.show', $return) }}" class="btn btn-outline-secondary btn-sm" title="View"><i class="ti ti-eye"></i></a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No returns found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
