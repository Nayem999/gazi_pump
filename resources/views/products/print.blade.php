<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Products Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $products->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Sales Team</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name }}</td>
                    <td>{{ $product->salesTeam?->name ?? 'All Teams' }}</td>
                    <td>{{ number_format((float) $product->price, 2) }}</td>
                    <td>{{ $product->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
