<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var theme = stored === 'light' || stored === 'dark'
                ? stored
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" style="background: linear-gradient(135deg, #0d5aa7 0%, #0f172a 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            @if ($companyLogoUrl ?? null)
                                <img src="{{ $companyLogoUrl }}" alt="{{ config('app.name') }}" style="height:3rem;width:auto">
                            @else
                                <i class="ti ti-truck display-5 text-primary"></i>
                            @endif
                            <h4 class="mt-2 mb-0">{{ config('app.name') }}</h4>
                            <p class="text-muted small">@yield('subtitle', 'Sign in to continue')</p>
                        </div>

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
