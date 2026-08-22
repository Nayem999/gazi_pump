<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dealers Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Dealers Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $dealers->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Phone</th>
                <th>Division</th>
                <th>District</th>
                <th>Thana</th>
                <th>Territory</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dealers as $dealer)
                <tr>
                    <td>{{ $dealer->dealer_code }}</td>
                    <td>{{ $dealer->name }}</td>
                    <td>{{ $dealer->type->label() }}</td>
                    <td>{{ $dealer->phone }}</td>
                    <td>{{ $dealer->division?->name }}</td>
                    <td>{{ $dealer->district?->name }}</td>
                    <td>{{ $dealer->thana?->name }}</td>
                    <td>{{ $dealer->territory?->name }}</td>
                    <td>{{ $dealer->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
