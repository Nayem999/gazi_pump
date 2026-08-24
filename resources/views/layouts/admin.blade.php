<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
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
<body class="admin-body">
    <div class="app-sidebar d-print-none" id="appSidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand text-decoration-none">
            @if ($companyLogoUrl ?? null)
                <img src="{{ $companyLogoUrl }}" alt="{{ config('app.name') }}" style="height:1.5rem;width:auto">
            @else
                <i class="ti ti-truck fs-4"></i>
            @endif
            <span>{{ config('app.name') }}</span>
        </a>
        <nav class="nav flex-column py-2">
            @include('layouts.partials.admin-sidebar')
        </nav>
    </div>

    <div class="app-content">
        <header class="app-topbar d-print-none d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light border d-lg-none" data-sidebar-toggle>
                    <i class="ti ti-menu-2 fs-5"></i>
                </button>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-light border" data-theme-toggle title="Toggle dark mode">
                    <i class="ti ti-moon fs-5"></i>
                </button>
                @can('menu.notifications')
                    @include('layouts.partials.notification-bell')
                @endcan
                <div class="dropdown">
                    <button class="btn btn-light border d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <i class="ti ti-user-circle fs-5"></i>
                        <span class="d-none d-sm-inline">{{ auth()->user()->name ?? 'Guest' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="ti ti-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="ti ti-logout-2 me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="flex-grow-1 p-3 p-lg-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-print-none" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="text-center text-muted small py-3">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
