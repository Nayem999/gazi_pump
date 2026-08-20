<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visit Plans Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Visit Plans Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $visitPlans->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Customer</th>
                <th>Planned Date</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($visitPlans as $visitPlan)
                <tr>
                    <td>{{ $visitPlan->user?->name }}</td>
                    <td>{{ $visitPlan->customer?->name }}</td>
                    <td>{{ $visitPlan->planned_date->format('M d, Y') }}</td>
                    <td>{{ $visitPlan->status->label() }}</td>
                    <td>{{ $visitPlan->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
