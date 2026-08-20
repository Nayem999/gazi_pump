<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Log</title>
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
    <h2>{{ config('app.name') }} &mdash; Activity Log</h2>
    <div class="meta">
        Generated {{ now()->format('M d, Y H:i') }} &mdash; {{ $activities->count() }} entries
        @if (isset($totalMatching) && $totalMatching > $activities->count())
            (showing the {{ $activities->count() }} most recent of {{ $totalMatching }} matching &mdash; narrow the date range to see more)
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Causer</th>
                <th>Event</th>
                <th>Subject</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $activity)
                <tr>
                    <td>{{ $activity->created_at?->format('M d, Y H:i') }}</td>
                    <td>{{ $activity->causer?->name ?? 'System' }}</td>
                    <td>{{ ucfirst($activity->event ?? 'n/a') }}</td>
                    <td>{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::singular($activity->log_name ?? '')) }} #{{ $activity->subject_id }}</td>
                    <td>{{ $activity->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
