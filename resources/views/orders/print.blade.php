<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Orders Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $orders->count() }} order(s)</div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Executive</th>
                <th>Dealer</th>
                <th>Order Date</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->user?->name }}</td>
                        <td>{{ $order->dealer?->name }}</td>
                        <td>{{ $order->order_date->format('M d, Y') }}</td>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                        <td>{{ number_format((float) $item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">Grand Total</td>
                <td>{{ number_format((float) $orders->sum('total_amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
