@extends('layouts.portal')

@section('title', 'Contact Us')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Contact Us</h1>
        <div class="row">
            <div class="col-lg-7">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($product ?? null)
                    <div class="alert alert-info">Inquiring about: <strong>{{ $product->name }}</strong></div>
                @endif

                <form method="POST" action="{{ route('portal.contact.store') }}">
                    @csrf
                    @if ($product ?? null)
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', auth('customer')->user()?->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', auth('customer')->user()?->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', auth('customer')->user()?->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject', ($product ?? null) ? 'Inquiry about '.$product->name : '') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="ti ti-send me-1"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
