@extends('layouts.portal-account')

@section('title', 'My Purchases')

@section('content')
    <h1 class="mb-4">My Purchases</h1>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->sale_date->format('M d, Y') }}</td>
                            <td>{{ $purchase->items_count }}</td>
                            <td>{{ number_format((float) $purchase->total_amount, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('portal.purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye me-1"></i>View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">You haven't made any purchases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $purchases->onEachSide(1)->links() }}</div>
@endsection
