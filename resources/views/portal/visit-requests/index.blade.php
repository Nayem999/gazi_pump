@extends('layouts.portal-account')

@section('title', 'My Visit Requests')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">My Visit Requests</h1>
        <a href="{{ route('portal.visit-requests.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Request a Visit
        </a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Preferred Date</th>
                        <th>Address</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visitRequests as $visitRequest)
                        <tr>
                            <td>{{ $visitRequest->preferred_date->format('d M Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($visitRequest->address, 40) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($visitRequest->message, 40) }}</td>
                            <td><span class="badge text-bg-{{ $visitRequest->status->badgeColor() }}">{{ $visitRequest->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">You haven't requested any visits yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $visitRequests->onEachSide(1)->links() }}</div>
@endsection
