@extends('layouts.portal')

@section('title', 'Customer Login')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="mb-1">Customer Login</h4>
                        <p class="text-muted small mb-4">Sign in to your customer portal account</p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('portal.login.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                <a href="{{ route('portal.password.request') }}" class="small">Forgot your password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-login-2 me-1"></i>Sign In
                            </button>
                        </form>

                        <p class="text-center small text-muted mt-4 mb-0">
                            Don't have an account? <a href="{{ route('portal.register') }}">Register</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
