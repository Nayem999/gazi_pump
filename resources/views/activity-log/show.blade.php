@extends('layouts.admin')

@section('title', 'Activity Detail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('activity-log.index') }}">Activity Log</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
    @php
        $oldValues = $activity->properties['old'] ?? null;
        $newValues = $activity->properties['attributes'] ?? null;
    @endphp

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Date</div>
                    <div class="fw-semibold">{{ $activity->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Causer</div>
                    <div class="fw-semibold">{{ $activity->causer?->name ?? 'System' }}</div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Event</div>
                    <span class="badge text-bg-{{ match ($activity->event) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'secondary',
                    } }}">
                        {{ ucfirst($activity->event ?? 'n/a') }}
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Subject</div>
                    <div class="fw-semibold">{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::singular($activity->log_name ?? '')) }} #{{ $activity->subject_id }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Description</div>
                    <div>{{ $activity->description }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($oldValues || $newValues)
        <div class="card">
            <div class="card-header bg-white"><h5 class="mb-0">Changed Values</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Field</th>
                                @if ($oldValues)<th>Old Value</th>@endif
                                @if ($newValues)<th>New Value</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_keys(($newValues ?? []) + ($oldValues ?? [])) as $field)
                                <tr>
                                    <td class="fw-semibold">{{ \Illuminate\Support\Str::headline($field) }}</td>
                                    @if ($oldValues)
                                        <td class="text-danger">{{ is_scalar($oldValues[$field] ?? null) ? $oldValues[$field] : json_encode($oldValues[$field] ?? null) }}</td>
                                    @endif
                                    @if ($newValues)
                                        <td class="text-success">{{ is_scalar($newValues[$field] ?? null) ? $newValues[$field] : json_encode($newValues[$field] ?? null) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <a href="{{ route('activity-log.index') }}" class="btn btn-outline-secondary mt-3"><i class="ti ti-arrow-left me-1"></i>Back to Activity Log</a>
@endsection
