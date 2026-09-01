<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Retailers Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Retailers Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $retailers->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Dealer</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($retailers as $retailer)
                <tr>
                    <td>{{ $retailer->name }}</td>
                    <td>{{ $retailer->dealer?->name }}</td>
                    <td>{{ $retailer->phone }}</td>
                    <td>{{ $retailer->email }}</td>
                    <td>{{ $retailer->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
