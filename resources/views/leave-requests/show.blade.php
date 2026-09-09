@extends('layouts.admin')

@section('title', 'Leave Request')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
    <li class="breadcrumb-item active">#{{ $leaveRequest->id }}</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Request #{{ $leaveRequest->id }}</span>
                    <span class="badge text-bg-{{ $leaveRequest->status->badgeColor() }}">{{ $leaveRequest->status->label() }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Employee</dt>
                        <dd class="col-sm-8">
                            {{ $leaveRequest->user?->name }}
                            <span class="text-muted small">{{ $leaveRequest->user?->employee_id }}</span>
                        </dd>

                        <dt class="col-sm-4">Leave Type</dt>
                        <dd class="col-sm-8">
                            {{ $leaveRequest->leaveType?->name }}
                            @unless ($leaveRequest->leaveType?->is_paid)
                                <span class="badge text-bg-secondary">Unpaid</span>
                            @endunless
                        </dd>

                        <dt class="col-sm-4">Dates</dt>
                        <dd class="col-sm-8">
                            {{ $leaveRequest->from_date->format('d M Y') }}
                            &rarr; {{ $leaveRequest->to_date->format('d M Y') }}
                        </dd>

                        <dt class="col-sm-4">Working Days</dt>
                        <dd class="col-sm-8">
                            {{ rtrim(rtrim(number_format((float) $leaveRequest->days, 1), '0'), '.') }}
                            @if ($leaveRequest->is_half_day) <span class="badge text-bg-info">Half day</span> @endif
                            <div class="form-text">Weekends and holidays in the range are excluded.</div>
                        </dd>

                        <dt class="col-sm-4">Reason</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->reason }}</dd>

                        <dt class="col-sm-4">Submitted</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->created_at?->format('d M Y, h:i A') }}</dd>

                        @if ($leaveRequest->approved_at)
                            <dt class="col-sm-4">Decided</dt>
                            <dd class="col-sm-8">
                                {{ $leaveRequest->approved_at->format('d M Y, h:i A') }}
                                @if ($leaveRequest->approver) by {{ $leaveRequest->approver->name }} @endif
                            </dd>
                        @endif

                        @if ($leaveRequest->decision_remarks)
                            <dt class="col-sm-4">Decision Remarks</dt>
                            <dd class="col-sm-8">{{ $leaveRequest->decision_remarks }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">{{ $leaveRequest->leaveType?->name }} balance, {{ $balance->year }}</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7">Already taken</dt>
                        <dd class="col-5 text-end">{{ rtrim(rtrim(number_format($balance->used, 1), '0'), '.') }}</dd>
                        <dt class="col-7">Remaining</dt>
                        <dd class="col-5 text-end fw-semibold {{ $balance->remaining < 0 ? 'text-danger' : '' }}">
                            {{ rtrim(rtrim(number_format($balance->remaining, 1), '0'), '.') }}
                        </dd>
                    </dl>
                    @if ($balance->remaining < 0)
                        <div class="small text-danger mt-2">
                            This person is already beyond their entitlement for this type.
                        </div>
                    @elseif ($leaveRequest->isPending() && $balance->remaining < (float) $leaveRequest->days)
                        {{-- Shown, not enforced: approving beyond entitlement is a
                             decision a manager is entitled to make, so the number
                             is put in front of them rather than blocking them. --}}
                        <div class="small text-warning mt-2">
                            Approving this would take them past their remaining balance.
                        </div>
                    @endif
                </div>
            </div>

            @if ($leaveRequest->isPending())
                @can('approve', $leaveRequest)
                    <div class="card mb-3">
                        <div class="card-header">Decision</div>
                        <div class="card-body d-flex flex-column gap-3">
                            <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}"
                                  data-confirm data-confirm-title="Approve this leave?"
                                  data-confirm-text="The dates will be marked as leave in attendance.">
                                @csrf
                                @method('PATCH')
                                <label class="form-label small">Remarks (optional)</label>
                                <input type="text" name="decision_remarks" class="form-control mb-2" maxlength="500">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ti ti-check me-1"></i>Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('leave-requests.reject', $leaveRequest) }}"
                                  data-confirm data-confirm-title="Reject this leave?">
                                @csrf
                                @method('PATCH')
                                <label class="form-label small">Reason for rejection <span class="text-danger">*</span></label>
                                <input type="text" name="decision_remarks" class="form-control mb-2" maxlength="500" required>
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="ti ti-x me-1"></i>Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            @endif

            @can('cancel', $leaveRequest)
                @if ($leaveRequest->isPending() || ($leaveRequest->isApproved() && ! $leaveRequest->from_date->isPast()))
                    <form method="POST" action="{{ route('leave-requests.cancel', $leaveRequest) }}"
                          data-confirm data-confirm-title="Withdraw this request?"
                          data-confirm-text="Any leave already written into attendance for these dates is removed.">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="ti ti-arrow-back-up me-1"></i>Withdraw Request
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
@endsection
