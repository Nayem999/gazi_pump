<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thanas Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Thanas Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $thanas->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Name (Bangla)</th>
                <th>District</th>
                <th>Division</th>
                <th>Territories</th>
                <th>Dealers</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($thanas as $thana)
                <tr>
                    <td>{{ $thana->name }}</td>
                    <td>{{ $thana->name_bn }}</td>
                    <td>{{ $thana->district?->name }}</td>
                    <td>{{ $thana->district?->division?->name }}</td>
                    <td>{{ $thana->territories_count ?? $thana->territories()->count() }}</td>
                    <td>{{ $thana->dealers_count ?? $thana->dealers()->count() }}</td>
                    <td>{{ $thana->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
