<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Entitlements</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h1>Leave Entitlements</h1>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }}</div>

    @php($fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.'))

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Employee ID</th>
                <th>Year</th>
                <th>Leave Type</th>
                <th class="num">Entitled</th>
                <th class="num">Carried Forward</th>
                <th class="num">Total</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaveBalances as $leaveBalance)
                <tr>
                    <td>{{ $leaveBalance->user?->name }}</td>
                    <td>{{ $leaveBalance->user?->employee_id }}</td>
                    <td>{{ $leaveBalance->year }}</td>
                    <td>{{ $leaveBalance->leaveType?->name }}</td>
                    <td class="num">{{ $fmt($leaveBalance->entitled_days) }}</td>
                    <td class="num">{{ $fmt($leaveBalance->carried_forward_days) }}</td>
                    <td class="num">{{ $fmt($leaveBalance->totalEntitlement()) }}</td>
                    <td>{{ $leaveBalance->remarks }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No entitlements found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
