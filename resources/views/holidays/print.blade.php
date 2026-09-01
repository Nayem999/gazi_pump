<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Holidays Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Holidays Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $holidays->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($holidays as $holiday)
                <tr>
                    <td>{{ $holiday->date->format('d M Y') }}</td>
                    <td>{{ $holiday->name }}</td>
                    <td>{{ $holiday->description }}</td>
                    <td>{{ $holiday->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
