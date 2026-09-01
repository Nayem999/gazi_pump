<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Performance Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Order Performance Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Order Count</th>
                <th>Total Quantity</th>
                <th>Total Order Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory_names }}</td>
                    <td>{{ $row->order_count }}</td>
                    <td>{{ $row->total_quantity }}</td>
                    <td>{{ number_format($row->total_order_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td>{{ $rows->sum('order_count') }}</td>
                    <td>{{ $rows->sum('total_quantity') }}</td>
                    <td>{{ number_format($rows->sum('total_order_value'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
