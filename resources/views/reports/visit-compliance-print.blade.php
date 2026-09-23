<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visit Compliance Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        .num { text-align: right; }
        tfoot td { font-weight: bold; background: #f8fafc; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Visit Compliance Report</h2>
    <div class="meta">Generated {{ now()->format('d M Y, h:i A') }} &mdash; {{ $rows->count() }} executive(s)</div>

    <table>
        <thead>
            <tr>
                <th>Executive</th>
                <th>Territory</th>
                <th>Planned</th>
                <th>Completed</th>
                <th>Missed</th>
                <th>Completion Rate</th>
                <th>Total Visits</th>
                <th>GPS Verified</th>
                <th>GPS Verified Rate</th>
                <th class="num">Orders</th>
                <th class="num">Order Value</th>
                <th class="num">Avg Order Value</th>
                <th class="num">Productive Dealers</th>
                <th class="num">Strike Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->user?->name }}</td>
                    <td>{{ $row->user?->territory_names }}</td>
                    <td>{{ $row->planned_count }}</td>
                    <td>{{ $row->completed_count }}</td>
                    <td>{{ $row->missed_count }}</td>
                    <td>{{ $row->completion_rate }}%</td>
                    <td>{{ $row->total_visits }}</td>
                    <td>{{ $row->gps_verified_count }}</td>
                    <td>{{ $row->gps_verified_rate }}%</td>
                    <td class="num">{{ $row->order_count }}</td>
                    <td class="num">{{ number_format($row->order_value, 2) }}</td>
                    <td class="num">{{ number_format($row->avg_order_value, 2) }}</td>
                    <td class="num">{{ $row->productive_dealers }}</td>
                    <td class="num">
                        @if ($row->visited_dealers > 0)
                            {{ $row->strike_rate }}% ({{ $row->converted_dealers }}/{{ $row->visited_dealers }})
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        @if ($rows->isNotEmpty())
            @php
                $printOrders = $rows->sum('order_count');
                $printValue = round((float) $rows->sum('order_value'), 2);
            @endphp
            <tfoot>
                <tr>
                    <td colspan="9" class="num">Total, all executives</td>
                    <td class="num">{{ $printOrders }}</td>
                    <td class="num">{{ number_format($printValue, 2) }}</td>
                    <td class="num">{{ number_format($printOrders > 0 ? $printValue / $printOrders : 0, 2) }}</td>
                    <td class="num">&mdash;</td>
                    <td class="num">&mdash;</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="meta" style="margin-top: 10px;">
        Order figures exclude rejected orders. Productive Dealers counts every dealer an order came from; Strike
        Rate is the share of visited dealers who also ordered. Neither is totalled, since a dealer served by two
        executives would be counted twice.
    </div>
</body>
</html>
