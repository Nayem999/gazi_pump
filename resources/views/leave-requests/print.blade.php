<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Requests</title>
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
    <h1>Leave Requests</h1>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }}</div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Days</th>
                <th>Status</th>
                <th>Decided By</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaveRequests as $leaveRequest)
                <tr>
                    <td>{{ $leaveRequest->user?->name }}</td>
                    <td>{{ $leaveRequest->leaveType?->name }}</td>
                    <td>{{ $leaveRequest->from_date->format('d M Y') }}</td>
                    <td>{{ $leaveRequest->to_date->format('d M Y') }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $leaveRequest->days, 1), '0'), '.') }}</td>
                    <td>{{ $leaveRequest->status->label() }}</td>
                    <td>{{ $leaveRequest->approver?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No leave requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
