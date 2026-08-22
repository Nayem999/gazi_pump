<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dealer Detail &mdash; {{ $dealer->name }}</title>
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
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; color: #fff; }
        .badge-success { background: #16a34a; }
        .badge-secondary { background: #64748b; }
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

    <h2>Dealer Detail</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }}</div>

    <table class="data">
        <tr><th>Dealer Code</th><td>{{ $dealer->dealer_code }}</td></tr>
        <tr><th>Name</th><td>{{ $dealer->name }}</td></tr>
        <tr><th>Type</th><td>{{ $dealer->type->label() }}</td></tr>
        <tr><th>Phone</th><td>{{ $dealer->phone }}</td></tr>
        <tr><th>Email</th><td>{{ $dealer->email ?? '—' }}</td></tr>
        <tr><th>Address</th><td>{{ $dealer->address ?? '—' }}</td></tr>
        <tr><th>Division</th><td>{{ $dealer->division?->name ?? '—' }}</td></tr>
        <tr><th>District</th><td>{{ $dealer->district?->name ?? '—' }}</td></tr>
        <tr><th>Thana / Upazila</th><td>{{ $dealer->thana?->name ?? '—' }}</td></tr>
        <tr><th>Territory</th><td>{{ $dealer->territory?->name ?? '—' }}</td></tr>
        <tr>
            <th>Status</th>
            <td><span class="badge {{ $dealer->status ? 'badge-success' : 'badge-secondary' }}">{{ $dealer->status ? 'Active' : 'Inactive' }}</span></td>
        </tr>
        <tr><th>Created</th><td>{{ $dealer->created_at?->format('M d, Y H:i') }}</td></tr>
    </table>
</body>
</html>
