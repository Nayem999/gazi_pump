@extends('layouts.portal-account')

@section('title', 'My Payments')

@section('content')
    <h1 class="mb-4">My Payments</h1>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Reference No.</th>
                        <th>Cheque Image</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->collection_date->format('d M Y') }}</td>
                            <td>{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method->label() }}</td>
                            <td>{{ $payment->reference_no ?? '—' }}</td>
                            <td>
                                @if ($payment->chequeImageUrl())
                                    <a href="{{ $payment->chequeImageUrl() }}" target="_blank">
                                        <img src="{{ $payment->chequeImageUrl() }}" style="width:32px;height:32px;object-fit:cover" class="rounded">
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">You haven't made any payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->onEachSide(1)->links() }}</div>
@endsection
