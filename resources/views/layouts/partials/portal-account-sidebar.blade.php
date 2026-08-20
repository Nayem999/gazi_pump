<div class="list-group shadow-sm mb-4 mb-lg-0">
    <a href="{{ route('portal.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
        <i class="ti ti-layout-dashboard me-2 text-primary"></i>Dashboard
    </a>
    <a href="{{ route('portal.purchases.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('portal.purchases.*') ? 'active' : '' }}">
        <i class="ti ti-shopping-cart me-2 text-success"></i>Purchases
    </a>
    <a href="{{ route('portal.profile.edit') }}" class="list-group-item list-group-item-action {{ request()->routeIs('portal.profile.*') ? 'active' : '' }}">
        <i class="ti ti-user-circle me-2 text-info"></i>My Profile
    </a>
    <a href="{{ route('portal.inquiries.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('portal.inquiries.*') ? 'active' : '' }}">
        <i class="ti ti-message-circle me-2 text-warning"></i>My Inquiries
    </a>
    <a href="{{ route('portal.visit-requests.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('portal.visit-requests.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-event me-2 text-purple"></i>My Visit Requests
    </a>
    <form method="POST" action="{{ route('portal.logout') }}">
        @csrf
        <button type="submit" class="list-group-item list-group-item-action text-danger">
            <i class="ti ti-logout-2 me-2"></i>Logout
        </button>
    </form>
</div>
