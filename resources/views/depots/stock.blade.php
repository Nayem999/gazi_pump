@extends('layouts.admin')

@section('title', 'Depot Stock')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('depots.index') }}">Depots</a></li>
    <li class="breadcrumb-item active">Stock</li>
@endsection

@section('content')
    <div class="alert alert-info">
        Tally is the source of truth for Opening/In/Out/Closing/Available —
        these figures only ever change via a Tally stock-sync job. Reserved
        and Allocated are SFA's own operational overlay for order
        allocation and are never sent back to Tally.
    </div>

    <x-filter-bar :action="route('depot-stock.index')">
        <div class="col-md-4">
            <label class="form-label">Depot</label>
            <select name="depot_id" class="form-select">
                <option value="">All</option>
                @foreach ($depots as $depot)
                    <option value="{{ $depot->id }}" @selected(($filters['depot_id'] ?? '') == $depot->id)>{{ $depot->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Search Product</label>
            <input type="text" name="search" class="form-control" placeholder="Product name..." value="{{ $filters['search'] ?? '' }}">
        </div>
    </x-filter-bar>

    <x-data-table title="Depot Stock" :paginator="$stocks">
        <x-slot:thead>
            <tr>
                <th>Depot</th>
                <th>Product</th>
                <th>Closing Qty</th>
                <th>Available Qty</th>
                <th>Reserved</th>
                <th>Allocated</th>
                <th>Sellable</th>
                <th>Last Synced</th>
            </tr>
        </x-slot:thead>

        @forelse ($stocks as $stock)
            <tr>
                <td>{{ $stock->depot->name }}</td>
                <td>{{ $stock->product->name }}</td>
                <td>{{ number_format((float) $stock->closing_qty, 2) }}</td>
                <td>{{ number_format((float) $stock->available_qty, 2) }}</td>
                <td>{{ number_format((float) $stock->reserved_qty, 2) }}</td>
                <td>{{ number_format((float) $stock->allocated_qty, 2) }}</td>
                <td class="fw-semibold">{{ number_format($stock->sellableQty(), 2) }}</td>
                <td>{{ $stock->last_synced_at?->format('d M Y, h:i A') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No stock records found.</td>
            </tr>
        @endforelse
    </x-data-table>
@endsection
