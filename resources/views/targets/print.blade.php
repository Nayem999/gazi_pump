<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Targets Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Targets Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $targets->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Period</th>
                <th>Sales Target</th>
                <th>Sales %</th>
                <th>Collection Target</th>
                <th>Collection %</th>
                <th>Qty Target</th>
                <th>Qty %</th>
                <th>Overall %</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($targets as $target)
                <tr>
                    <td>{{ $target->user?->name }}</td>
                    <td>{{ $target->periodLabel() }}</td>
                    <td>{{ number_format((float) $target->sales_value_target, 2) }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->sales_pct, 1).'%' : '—' }}</td>
                    <td>{{ number_format((float) $target->collection_target, 2) }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->collection_pct, 1).'%' : '—' }}</td>
                    <td>{{ $target->quantity_target }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->quantity_pct, 1).'%' : '—' }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->overall_pct, 1).'%' : '—' }}</td>
                    <td>{{ $target->achievement?->grade->value ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
