<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dealer Coverage Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Dealer Coverage Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} territory(ies)</div>

    <table>
        <thead>
            <tr>
                <th>Territory</th>
                <th>Total Dealers</th>
                <th>Visited</th>
                <th>Not Visited</th>
                <th>Coverage Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->territory?->name }}</td>
                    <td>{{ $row->total_dealers }}</td>
                    <td>{{ $row->visited_dealers }}</td>
                    <td>{{ $row->not_visited_dealers }}</td>
                    <td>{{ $row->coverage_rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
