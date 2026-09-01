<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dealer Ledger — {{ $dealer->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .balance { margin-top: 12px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Dealer Ledger</h2>
    <div class="meta">
        {{ $dealer->name }} ({{ $dealer->dealer_code }})
        @if ($dealer->territory) &mdash; {{ $dealer->territory->name }} @endif
        &mdash; Generated {{ now()->format('d M Y, h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->date->format('d M Y') }}</td>
                    <td>{{ $row->description }}</td>
                    <td>{{ $row->debit > 0 ? number_format($row->debit, 2) : '' }}</td>
                    <td>{{ $row->credit > 0 ? number_format($row->credit, 2) : '' }}</td>
                    <td>{{ number_format($row->balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="balance">Current Balance: {{ number_format($balance, 2) }}</div>
</body>
</html>
