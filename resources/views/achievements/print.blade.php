<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Achievements Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Achievements Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $entries->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Date</th>
                <th>Order Achieved</th>
                <th>Collection Achieved</th>
                <th>Qty Achieved</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
                <tr>
                    <td>{{ $entry->user?->name }}</td>
                    <td>{{ $entry->entryDateLabel() }}</td>
                    <td>{{ number_format((float) $entry->order_value_achieved, 2) }}</td>
                    <td>{{ number_format((float) $entry->collection_achieved, 2) }}</td>
                    <td>{{ $entry->quantity_achieved }}</td>
                    <td>{{ $entry->status->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
