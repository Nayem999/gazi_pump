<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Categories Report</title>
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
    <h2>{{ config('app.name') }} &mdash; Product Categories Report</h2>
    <div class="meta">Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $categories->count() }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Parent Category</th>
                <th>Products</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->code }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->parent->name ?? '—' }}</td>
                    <td>{{ $category->products_count ?? $category->products()->count() }}</td>
                    <td>{{ $category->status ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
