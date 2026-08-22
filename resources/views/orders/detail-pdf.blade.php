<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Detail &mdash; #{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .letterhead { width: 100%; border-collapse: collapse; margin-bottom: 16px; border-bottom: 2px solid #1e293b; padding-bottom: 8px; }
        .letterhead td { vertical-align: middle; border: none; padding: 0; }
        .company-name { font-size: 16px; font-weight: bold; }
        .company-meta { font-size: 10px; color: #64748b; }
        h2 { margin: 16px 0 2px; }
        .meta { color: #64748b; margin-bottom: 12px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.data th { background: #f1f5f9; width: 30%; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th, table.items td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.items th { background: #f1f5f9; }
        table.items tfoot td { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
    <table class="letterhead">
        <tr>
            <td style="width:50%">
                @if ($setting->logoPath())
                    <img src="{{ $setting->logoPath() }}" style="height:50px">
                @endif
            </td>
            <td style="width:50%; text-align:right">
                <div class="company-name">{{ $setting->company_name }}</div>
                @if ($setting->company_address)<div class="company-meta">{{ $setting->company_address }}</div>@endif
                @if ($setting->company_phone)<div class="company-meta">Phone: {{ $setting->company_phone }}</div>@endif
                @if ($setting->company_email)<div class="company-meta">Email: {{ $setting->company_email }}</div>@endif
            </td>
        </tr>
    </table>

    <h2>Order Detail &mdash; #{{ $order->id }}</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }}</div>

    <table class="data">
        <tr><th>Dealer</th><td>{{ $order->dealer?->name }} ({{ $order->dealer?->dealer_code }})</td></tr>
        <tr><th>Dealer Phone</th><td>{{ $order->dealer?->phone ?? '—' }}</td></tr>
        <tr><th>Sales Executive</th><td>{{ $order->user?->name }} ({{ $order->user?->employee_id }})</td></tr>
        <tr><th>Order Date</th><td>{{ $order->order_date->format('M d, Y') }}</td></tr>
        <tr><th>Total Amount</th><td>{{ number_format((float) $order->total_amount, 2) }}</td></tr>
        <tr><th>Remarks</th><td>{{ $order->remarks ?? '—' }}</td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product?->name }} ({{ $item->product?->sku }})</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Grand Total</td>
                <td>{{ number_format((float) $order->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
