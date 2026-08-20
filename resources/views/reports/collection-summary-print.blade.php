<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Summary Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Collection Summary Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Collections</th>
                <th>Total Amount</th>
                <th>Cash</th>
                <th>Cheque</th>
                <th>Bank Transfer</th>
                <th>Mobile Banking</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory?->name }}</td>
                    <td>{{ $row->collections_count }}</td>
                    <td>{{ number_format($row->total_amount, 2) }}</td>
                    <td>{{ number_format($row->cash_total, 2) }}</td>
                    <td>{{ number_format($row->cheque_total, 2) }}</td>
                    <td>{{ number_format($row->bank_transfer_total, 2) }}</td>
                    <td>{{ number_format($row->mobile_banking_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td>{{ $rows->sum('collections_count') }}</td>
                    <td>{{ number_format($rows->sum('total_amount'), 2) }}</td>
                    <td>{{ number_format($rows->sum('cash_total'), 2) }}</td>
                    <td>{{ number_format($rows->sum('cheque_total'), 2) }}</td>
                    <td>{{ number_format($rows->sum('bank_transfer_total'), 2) }}</td>
                    <td>{{ number_format($rows->sum('mobile_banking_total'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
