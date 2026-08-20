@extends('layouts.portal-account')

@section('title', 'My Inquiries')

@section('content')
    <h1 class="mb-4">My Inquiries</h1>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inquiries as $inquiry)
                        <tr>
                            <td>{{ $inquiry->created_at->format('M d, Y') }}</td>
                            <td>{{ $inquiry->subject }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($inquiry->message, 60) }}</td>
                            <td><span class="badge text-bg-{{ $inquiry->status->badgeColor() }}">{{ $inquiry->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                You haven't submitted any inquiries yet. <a href="{{ route('portal.contact') }}">Contact us</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $inquiries->onEachSide(1)->links() }}</div>
@endsection
