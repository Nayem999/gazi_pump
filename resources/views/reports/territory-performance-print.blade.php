<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Territory Performance Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Territory Performance Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} territory(ies)</div>

    <table>
        <thead>
            <tr>
                <th>Territory</th>
                <th>Executives</th>
                <th>Total Order Value</th>
                <th>Total Collection Amount</th>
                <th>Total Visits</th>
                <th>GPS Verified Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->territory?->name }}</td>
                    <td>{{ $row->executive_count }}</td>
                    <td>{{ number_format($row->total_order_value, 2) }}</td>
                    <td>{{ number_format($row->total_collection_amount, 2) }}</td>
                    <td>{{ $row->total_visits }}</td>
                    <td>{{ $row->gps_verified_rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
