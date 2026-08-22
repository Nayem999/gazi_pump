<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GPS Report</title>
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
    <h2>{{ config('app.name') }} &mdash; GPS Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Ping Count</th>
                <th>Avg Accuracy</th>
                <th>Avg Battery</th>
                <th>Last Seen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory_names }}</td>
                    <td>{{ $row->ping_count }}</td>
                    <td>{{ $row->avg_accuracy !== null ? $row->avg_accuracy.' m' : '—' }}</td>
                    <td>{{ $row->avg_battery_level !== null ? $row->avg_battery_level.'%' : '—' }}</td>
                    <td>{{ $row->last_seen_at?->format('M d, Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
