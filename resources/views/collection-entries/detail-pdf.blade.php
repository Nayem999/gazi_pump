<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Detail &mdash; #{{ $collectionEntry->id }}</title>
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

    <h2>Collection Detail &mdash; #{{ $collectionEntry->id }}</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }}</div>

    <table class="data">
        <tr><th>Dealer</th><td>{{ $collectionEntry->dealer?->name }} ({{ $collectionEntry->dealer?->dealer_code }})</td></tr>
        <tr><th>Dealer Phone</th><td>{{ $collectionEntry->dealer?->phone ?? '—' }}</td></tr>
        <tr><th>Sales Executive</th><td>{{ $collectionEntry->user?->name }} ({{ $collectionEntry->user?->employee_id }})</td></tr>
        <tr><th>Collection Date</th><td>{{ $collectionEntry->collection_date->format('d M Y') }}</td></tr>
        <tr><th>Amount</th><td>{{ number_format((float) $collectionEntry->amount, 2) }}</td></tr>
        <tr><th>Payment Method</th><td>{{ $collectionEntry->payment_method->label() }}</td></tr>
        <tr><th>Reference No.</th><td>{{ $collectionEntry->reference_no ?? '—' }}</td></tr>
        @if ($collectionEntry->chequeImagePath())
            <tr><th>Cheque Image</th><td><img src="{{ $collectionEntry->chequeImagePath() }}" style="height:120px"></td></tr>
        @endif
        <tr><th>Remarks</th><td>{{ $collectionEntry->remarks ?? '—' }}</td></tr>
    </table>
</body>
</html>
