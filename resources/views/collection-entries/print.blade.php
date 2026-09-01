<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Entries Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        tfoot td { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Collection Entries Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $collectionEntries->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Dealer</th>
                <th>Collection Date</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Reference No</th>
                <th>Cheque Image</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($collectionEntries as $collectionEntry)
                <tr>
                    <td>{{ $collectionEntry->user?->name }}</td>
                    <td>{{ $collectionEntry->dealer?->name }}</td>
                    <td>{{ $collectionEntry->collection_date->format('d M Y') }}</td>
                    <td>{{ number_format((float) $collectionEntry->amount, 2) }}</td>
                    <td>{{ $collectionEntry->payment_method->label() }}</td>
                    <td>{{ $collectionEntry->reference_no }}</td>
                    <td>
                        @if ($collectionEntry->chequeImagePath())
                            <img src="{{ $collectionEntry->chequeImagePath() }}" style="height:40px">
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $collectionEntry->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Grand Total</td>
                <td>{{ number_format((float) $collectionEntries->sum('amount'), 2) }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
