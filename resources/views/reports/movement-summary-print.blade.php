<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Movement Summary — {{ $summary->user->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { margin-bottom: 2px; }
        .meta { color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; width: 40%; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }} &mdash; Daily Activity Report</h2>
    <div class="meta">
        {{ $summary->user->name }} ({{ $summary->user->employee_id }}) &mdash; {{ $summary->date->format('d M Y') }}
        &mdash; Generated {{ now()->format('d M Y, h:i A') }}
    </div>

    @php
        $formatDuration = fn (?int $seconds) => $seconds === null ? '—' : intdiv($seconds, 3600).'h '.intdiv($seconds % 3600, 60).'m';
    @endphp

    <table>
        <tbody>
            <tr><th>Working Hours</th><td>{{ $formatDuration($summary->working_seconds) }}</td></tr>
            <tr><th>Active Movement</th><td>{{ $formatDuration($summary->active_seconds) }}</td></tr>
            <tr><th>Customer Visits</th><td>{{ $summary->visits_count }}</td></tr>
            <tr><th>Distance Travelled</th><td>{{ $summary->distance_km }} km</td></tr>
            <tr><th>Total Visit Time</th><td>{{ $formatDuration($summary->visit_seconds) }}</td></tr>
            <tr><th>Idle Time</th><td>{{ $formatDuration($summary->idle_seconds) }}</td></tr>
            <tr><th>First Location</th><td>{{ $summary->first_location ?? '—' }}</td></tr>
            <tr><th>Last Location</th><td>{{ $summary->last_location ?? '—' }}</td></tr>
            <tr><th>Total Locations Captured</th><td>{{ $summary->locations_captured }}</td></tr>
        </tbody>
    </table>
</body>
</html>
