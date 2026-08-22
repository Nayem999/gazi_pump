<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Districts Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Districts Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $districts->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Name (Bangla)</th>
                <th>Division</th>
                <th>Thanas</th>
                <th>Territories</th>
                <th>Dealers</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($districts as $district)
                <tr>
                    <td>{{ $district->name }}</td>
                    <td>{{ $district->name_bn }}</td>
                    <td>{{ $district->division?->name }}</td>
                    <td>{{ $district->thanas_count ?? $district->thanas()->count() }}</td>
                    <td>{{ $district->territories_count ?? $district->territories()->count() }}</td>
                    <td>{{ $district->dealers_count ?? $district->dealers()->count() }}</td>
                    <td>{{ $district->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
