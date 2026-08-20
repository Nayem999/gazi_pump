<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('portal.home') }}">
            @if ($companyLogoUrl ?? null)
                <img src="{{ $companyLogoUrl }}" alt="{{ config('app.name') }}" style="height:1.5rem;width:auto" class="me-1">
            @else
                <i class="ti ti-truck me-1"></i>
            @endif
            {{ config('app.name') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="portalNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.products.index') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.dealer-locator') }}">Dealer Locator</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.news.index') }}">News</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('portal.contact') }}">Contact</a></li>
            </ul>
            <div class="d-flex gap-2 ms-lg-3 mt-2 mt-lg-0">
                @guest('customer')
                    <a href="{{ route('portal.login') }}" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="{{ route('portal.register') }}" class="btn btn-primary btn-sm">Register</a>
                @else
                    <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-light btn-sm">
                        <i class="ti ti-user-circle me-1"></i>{{ Auth::guard('customer')->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Logout</button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</nav>
