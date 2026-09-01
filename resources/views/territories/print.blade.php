<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Territories Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Territories Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $territories->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Division</th>
                <th>District</th>
                <th>Thana</th>
                <th>Manager</th>
                <th>Executives</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($territories as $territory)
                <tr>
                    <td>{{ $territory->code }}</td>
                    <td>{{ $territory->name }}</td>
                    <td>{{ $territory->division?->name }}</td>
                    <td>{{ $territory->district?->name }}</td>
                    <td>{{ $territory->thana?->name }}</td>
                    <td>{{ $territory->manager?->name }}</td>
                    <td>{{ $territory->users_count ?? $territory->users()->count() }}</td>
                    <td>{{ $territory->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
