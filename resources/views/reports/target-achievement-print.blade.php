<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Target vs Achievement Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Target vs Achievement Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $rows->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Order Target</th>
                <th>Order Achieved</th>
                <th>Collection Target</th>
                <th>Collection Achieved</th>
                <th>Overall %</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory?->name }}</td>
                    <td>{{ number_format($row->order_target, 2) }}</td>
                    <td>{{ number_format($row->order_achieved, 2) }}</td>
                    <td>{{ number_format($row->collection_target, 2) }}</td>
                    <td>{{ number_format($row->collection_achieved, 2) }}</td>
                    <td>{{ $row->overall_pct }}%</td>
                    <td>{{ $row->grade?->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
