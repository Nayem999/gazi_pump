@extends('layouts.portal-account')

@section('title', 'Request a Visit')

@section('content')
    <h1 class="mb-4">Request a Visit</h1>
    <div class="row">
        <div class="col-lg-8">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('portal.visit-requests.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Preferred Date</label>
                    <input type="date" name="preferred_date" class="form-control" value="{{ old('preferred_date') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="3" class="form-control" required>{{ old('address') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message (optional)</label>
                    <textarea name="message" rows="3" class="form-control">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-send me-1"></i>Submit Request
                </button>
                <a href="{{ route('portal.visit-requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
