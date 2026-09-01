<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dealer &amp; Ledger Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        tfoot td { font-weight: bold; background: #f8fafc; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Dealer &amp; Ledger Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} dealer(s)</div>

    <table>
        <thead>
            <tr>
                <th>Dealer Code</th>
                <th>Dealer Name</th>
                <th>Territory</th>
                <th>Total Ordered</th>
                <th>Total Collected</th>
                <th>Due Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->dealer->dealer_code }}</td>
                    <td>{{ $row->dealer->name }}</td>
                    <td>{{ $row->dealer->territory?->name }}</td>
                    <td>{{ number_format($row->total_ordered, 2) }}</td>
                    <td>{{ number_format($row->total_collected, 2) }}</td>
                    <td>{{ number_format($row->due_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Grand Total</td>
                <td>{{ number_format($rows->sum('total_ordered'), 2) }}</td>
                <td>{{ number_format($rows->sum('total_collected'), 2) }}</td>
                <td>{{ number_format($rows->sum('due_amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
