@extends('layouts.admin')

@section('title', 'Deliveries')

@section('breadcrumb')
    <li class="breadcrumb-item active">Deliveries</li>
@endsection

@section('content')
    <x-filter-bar :action="route('deliveries.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="External reference..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\DeliveryStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <x-data-table title="Deliveries" :paginator="$deliveries">
        <x-slot:thead>
            <tr>
                <th>Reference</th>
                <th>Order</th>
                <th>Dealer</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Delivery Date</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($deliveries as $delivery)
            <tr>
                <td class="font-monospace small">{{ $delivery->external_reference }}</td>
                <td><a href="{{ route('orders.show', $delivery->order) }}">#{{ $delivery->order_id }}</a></td>
                <td>{{ $delivery->order->dealer->name }}</td>
                <td>{{ $delivery->vehicle?->registration_number ?? '—' }}</td>
                <td>{{ $delivery->driver?->name ?? '—' }}</td>
                <td>{{ $delivery->delivery_date->format('d M Y') }}</td>
                <td><span class="badge text-bg-{{ $delivery->status->badgeColor() }}">{{ $delivery->status->label() }}</span></td>
                <td class="text-end">
                    <a href="{{ route('deliveries.show', $delivery) }}" class="btn btn-outline-secondary btn-sm" title="View"><i class="ti ti-eye"></i></a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No deliveries found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
