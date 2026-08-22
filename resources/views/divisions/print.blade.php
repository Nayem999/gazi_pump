<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Divisions Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Divisions Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $divisions->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Name (Bangla)</th>
                <th>Districts</th>
                <th>Territories</th>
                <th>Dealers</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($divisions as $division)
                <tr>
                    <td>{{ $division->name }}</td>
                    <td>{{ $division->name_bn }}</td>
                    <td>{{ $division->districts_count ?? $division->districts()->count() }}</td>
                    <td>{{ $division->territories_count ?? $division->territories()->count() }}</td>
                    <td>{{ $division->dealers_count ?? $division->dealers()->count() }}</td>
                    <td>{{ $division->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
