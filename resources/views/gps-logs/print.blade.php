<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GPS Tracking Report</title>
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
    <h2>{{ config('app.name') }} &mdash; GPS Tracking Report</h2>
    <div class="meta">
        {{ $user?->name }} ({{ $user?->employee_id }}) &mdash; {{ \Illuminate\Support\Carbon::parse($date)->format('M d, Y') }}
        &mdash; {{ $logs->count() }} ping(s) &mdash; {{ $distanceKm }} km traveled
        &mdash; Generated {{ now()->format('M d, Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Accuracy</th>
                <th>Speed</th>
                <th>Battery</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->recorded_at->format('H:i:s') }}</td>
                    <td>{{ $log->lat }}</td>
                    <td>{{ $log->lng }}</td>
                    <td>{{ $log->accuracy !== null ? $log->accuracy.' m' : '—' }}</td>
                    <td>{{ $log->speed !== null ? $log->speed.' km/h' : '—' }}</td>
                    <td>{{ $log->battery_level !== null ? $log->battery_level.'%' : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
