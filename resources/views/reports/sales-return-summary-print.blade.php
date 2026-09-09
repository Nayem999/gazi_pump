<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Return Summary Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        tfoot td { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Sales Return Summary Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Returns</th>
                <th>Pending</th>
                <th>Rejected</th>
                <th>Received</th>
                <th>Total Credited</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory_names }}</td>
                    <td>{{ $row->returns_count }}</td>
                    <td>{{ $row->pending_count }}</td>
                    <td>{{ $row->rejected_count }}</td>
                    <td>{{ $row->received_count }}</td>
                    <td>{{ number_format($row->total_credited, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td>{{ $rows->sum('returns_count') }}</td>
                    <td>{{ $rows->sum('pending_count') }}</td>
                    <td>{{ $rows->sum('rejected_count') }}</td>
                    <td>{{ $rows->sum('received_count') }}</td>
                    <td>{{ number_format($rows->sum('total_credited'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
