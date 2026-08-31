<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Achievement Summary Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Achievement Summary Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $rows->count() }} executive(s), approved entries only</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Entries</th>
                <th>Order Achieved</th>
                <th>Collection Achieved</th>
                <th>Quantity Achieved</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory_names }}</td>
                    <td>{{ $row->entries_count }}</td>
                    <td>{{ number_format($row->total_order_achieved, 2) }}</td>
                    <td>{{ number_format($row->total_collection_achieved, 2) }}</td>
                    <td>{{ $row->total_quantity_achieved }}</td>
                </tr>
            @endforeach
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td>{{ $rows->sum('entries_count') }}</td>
                    <td>{{ number_format($rows->sum('total_order_achieved'), 2) }}</td>
                    <td>{{ number_format($rows->sum('total_collection_achieved'), 2) }}</td>
                    <td>{{ $rows->sum('total_quantity_achieved') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
