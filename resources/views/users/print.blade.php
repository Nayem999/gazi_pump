<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Users Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Users Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $users->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Designation</th>
                <th>Manager</th>
                <th>Status</th>
                <th>Roles</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->employee_id ?? '—' }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->designation }}</td>
                    <td>{{ $user->manager?->name }}</td>
                    <td>{{ $user->status ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
