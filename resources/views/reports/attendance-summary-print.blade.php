<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Summary Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Attendance Summary Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Present</th>
                <th>Late</th>
                <th>Half Day</th>
                <th>Absent</th>
                <th>Late Minutes</th>
                <th>Total Days</th>
                <th>Attendance Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory?->name }}</td>
                    <td>{{ $row->present_count }}</td>
                    <td>{{ $row->late_count }}</td>
                    <td>{{ $row->half_day_count }}</td>
                    <td>{{ $row->absent_count }}</td>
                    <td>{{ $row->total_late_minutes }}</td>
                    <td>{{ $row->total_days }}</td>
                    <td>{{ $row->attendance_rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
