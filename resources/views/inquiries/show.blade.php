@extends('layouts.admin')

@section('title', 'Inquiry Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inquiries.index') }}">Inquiries</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $inquiry->subject }}</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $inquiry->name }}</dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $inquiry->email }}</dd>
                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9">{{ $inquiry->phone ?? '—' }}</dd>
                        <dt class="col-sm-3">Product</dt>
                        <dd class="col-sm-9">{{ $inquiry->product?->name ?? '—' }}</dd>
                        <dt class="col-sm-3">Customer Account</dt>
                        <dd class="col-sm-9">{{ $inquiry->customerAccount?->name ?? 'Guest (not logged in)' }}</dd>
                        <dt class="col-sm-3">Received</dt>
                        <dd class="col-sm-9">{{ $inquiry->created_at->format('d M Y, h:i A') }}</dd>
                        <dt class="col-sm-3">Message</dt>
                        <dd class="col-sm-9">{{ $inquiry->message }}</dd>
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
                    <span class="badge text-bg-{{ $inquiry->status->badgeColor() }} mb-3">{{ $inquiry->status->label() }}</span>

                    @can('update', $inquiry)
                        <form method="POST" action="{{ route('inquiries.update-status', $inquiry) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Update Status</label>
                                <select name="status" class="form-select">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($inquiry->status === $status)>{{ $status->label() }}</option>
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
