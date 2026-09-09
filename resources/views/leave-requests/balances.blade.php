@extends('layouts.admin')

@section('title', 'Leave Balances')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
    <li class="breadcrumb-item active">Balances</li>
@endsection

@section('content')
    <x-filter-bar :action="route('leave-requests.balances')">
        @if ($users->count() > 1)
            <div class="col-md-4">
                <label class="form-label">Employee</label>
                <select name="user_id" class="form-select">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($subject->id === $user->id)>
                            {{ $user->name }} @if ($user->employee_id) ({{ $user->employee_id }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="{{ $year }}">
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header">
            {{ $subject->name }} &mdash; {{ $year }}
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th class="text-end">Entitled</th>
                        <th class="text-end">Carried Forward</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Taken</th>
                        <th class="text-end">Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @php($fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.'))
                    @forelse ($balances as $balance)
                        <tr>
                            <td>
                                {{ $balance->leave_type->name }}
                                @unless ($balance->leave_type->is_paid)
                                    <span class="badge text-bg-secondary">Unpaid</span>
                                @endunless
                            </td>
                            <td class="text-end">{{ $fmt($balance->entitled) }}</td>
                            <td class="text-end">{{ $fmt($balance->carried_forward) }}</td>
                            <td class="text-end">{{ $fmt($balance->total) }}</td>
                            <td class="text-end">{{ $fmt($balance->used) }}</td>
                            <td class="text-end fw-semibold {{ $balance->remaining < 0 ? 'text-danger' : '' }}">
                                {{ $fmt($balance->remaining) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No active leave types configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer small text-muted">
            Taken counts approved requests only, summed from the requests themselves rather than a stored total,
            so it always agrees with the list. A negative remaining balance means leave was approved beyond the entitlement.
        </div>
    </div>
@endsection
