<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Executive Performance Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Executive Performance Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $rows->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Attendance</th>
                <th>Visit Completion</th>
                <th>GPS Verified</th>
                <th>Sales Value</th>
                <th>Collections</th>
                <th>Overall Achievement</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory?->name }}</td>
                    <td>{{ $row->attendance_rate }}%</td>
                    <td>{{ $row->visit_completion_rate }}%</td>
                    <td>{{ $row->gps_verified_rate }}%</td>
                    <td>{{ number_format($row->total_sales_value, 2) }}</td>
                    <td>{{ number_format($row->total_collection_amount, 2) }}</td>
                    <td>{{ $row->overall_achievement_pct }}%</td>
                    <td>{{ $row->grade?->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
