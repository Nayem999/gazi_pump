<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', config('app.name') . ' - official customer portal')">
    @if ($companyFaviconUrl ?? null)
        <link rel="icon" href="{{ $companyFaviconUrl }}">
    @endif
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
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    @include('layouts.partials.portal-navbar')

    <main class="flex-grow-1">
        @if (session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('layouts.partials.portal-footer')

    @stack('scripts')
</body>
</html>
