<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dealer Visits Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Dealer Visits Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $visits->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Dealer</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>GPS Verified</th>
                <th>Distance (m)</th>
                <th>Feedback</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($visits as $visit)
                <tr>
                    <td>{{ $visit->user?->name }}</td>
                    <td>{{ $visit->dealer?->name }}</td>
                    <td>{{ $visit->check_in_at?->format('M d, Y H:i') }}</td>
                    <td>{{ $visit->check_out_at?->format('M d, Y H:i') }}</td>
                    <td>{{ match ($visit->is_gps_verified) { true => 'Yes', false => 'No', default => 'Unknown' } }}</td>
                    <td>{{ $visit->distance_from_dealer_meters }}</td>
                    <td>{{ $visit->feedback }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
