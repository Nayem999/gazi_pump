<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Attendance Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $attendances->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Date</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Status</th>
                <th>Late (min)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user?->employee_id }}</td>
                    <td>{{ $attendance->user?->name }}</td>
                    <td>{{ $attendance->date->format('d M Y') }}</td>
                    <td>{{ $attendance->check_in_at?->format('h:i A') }}</td>
                    <td>{{ $attendance->check_out_at?->format('h:i A') }}</td>
                    <td>{{ $attendance->status->label() }}</td>
                    <td>{{ $attendance->late_minutes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
