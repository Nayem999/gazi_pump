@extends('layouts.admin')

@section('title', 'Visit Request Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visit-requests.index') }}">Visit Requests</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Visit Request #{{ $visitRequest->id }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Customer</dt>
                        <dd class="col-sm-9">{{ $visitRequest->customerAccount?->name ?? '—' }}</dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $visitRequest->customerAccount?->email ?? '—' }}</dd>
                        <dt class="col-sm-3">Preferred Date</dt>
                        <dd class="col-sm-9">{{ $visitRequest->preferred_date->format('d M Y') }}</dd>
                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9">{{ $visitRequest->address }}</dd>
                        <dt class="col-sm-3">Submitted</dt>
                        <dd class="col-sm-9">{{ $visitRequest->created_at->format('d M Y, h:i A') }}</dd>
                        <dt class="col-sm-3">Message</dt>
                        <dd class="col-sm-9">{{ $visitRequest->message ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <span class="badge text-bg-{{ $visitRequest->status->badgeColor() }} mb-3">{{ $visitRequest->status->label() }}</span>

                    @can('update', $visitRequest)
                        <form method="POST" action="{{ route('visit-requests.update-status', $visitRequest) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Update Status</label>
                                <select name="status" class="form-select">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($visitRequest->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-check me-1"></i>Save</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
