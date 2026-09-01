<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visit Compliance Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Visit Compliance Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Planned</th>
                <th>Completed</th>
                <th>Missed</th>
                <th>Completion Rate</th>
                <th>Total Visits</th>
                <th>GPS Verified</th>
                <th>GPS Verified Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory_names }}</td>
                    <td>{{ $row->planned_count }}</td>
                    <td>{{ $row->completed_count }}</td>
                    <td>{{ $row->missed_count }}</td>
                    <td>{{ $row->completion_rate }}%</td>
                    <td>{{ $row->total_visits }}</td>
                    <td>{{ $row->gps_verified_count }}</td>
                    <td>{{ $row->gps_verified_rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
