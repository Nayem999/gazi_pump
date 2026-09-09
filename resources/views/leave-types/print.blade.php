<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Types</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Leave Types</h1>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }}</div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Annual Quota</th>
                <th>Paid</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaveTypes as $leaveType)
                <tr>
                    <td>{{ $leaveType->name }}</td>
                    <td>{{ $leaveType->code }}</td>
                    <td>{{ $leaveType->annual_quota }}</td>
                    <td>{{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}</td>
                    <td>{{ $leaveType->status ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $leaveType->description }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No leave types found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
