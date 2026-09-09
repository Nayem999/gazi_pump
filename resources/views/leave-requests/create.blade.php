@extends('layouts.admin')

@section('title', 'Request Leave')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
    <li class="breadcrumb-item active">New Request</li>
@endsection

@section('content')
    @if ($balances->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">Your balance this year</div>
            <div class="card-body d-flex flex-wrap gap-3">
                @foreach ($balances as $balance)
                    <div class="border rounded px-3 py-2">
                        <div class="small text-muted">{{ $balance->leave_type->name }}</div>
                        <div class="fw-semibold {{ $balance->remaining < 0 ? 'text-danger' : '' }}">
                            {{ rtrim(rtrim(number_format($balance->remaining, 1), '0'), '.') }} left
                        </div>
                        <div class="small text-muted">of {{ rtrim(rtrim(number_format($balance->total, 1), '0'), '.') }} day(s)</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-requests.store') }}">
                @include('leave-requests._form')
            </form>
        </div>
    </div>
@endsection
