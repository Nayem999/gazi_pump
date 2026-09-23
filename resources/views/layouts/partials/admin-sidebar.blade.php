{{-- Each module appends its own nav-link (and nav-section-title, if it starts a new group)
     here as it is built. Every link is gated by its menu permission.
     Section order: Order Operations, Performance, Field Operations, GIS,
     Reports, Access Control, Organization, Dealer Management, Product
     Management, Customer Portal, Communication, System. --}}

<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="ti ti-gauge icon-blue"></i> Dashboard
</a>

{{-- Orders and Collection Entry were revived as the live transactional
     backbone for Tally sync (see docs/tally-sfa-integration.md, Phase 2).
     Cash Handover stays retired (a separate, permanent decision) — no role
     holds its menu permission any more except Super Admin (via
     Permission::all()), so that one link naturally disappears for everyone
     else without deleting it here. --}}
@canany(['menu.orders', 'menu.collection-entries', 'menu.cash-handovers'])
    <div class="nav-section-title">Order Operations</div>
@endcanany

@can('menu.orders')
    <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
        <i class="ti ti-receipt icon-green"></i> Orders
    </a>
@endcan

@can('menu.collection-entries')
    <a href="{{ route('collection-entries.index') }}" class="nav-link {{ request()->routeIs('collection-entries.*') ? 'active' : '' }}">
        <i class="ti ti-cash icon-amber"></i> Collection Entry
    </a>
@endcan

@can('menu.deliveries')
    <a href="{{ route('deliveries.index') }}" class="nav-link {{ request()->routeIs('deliveries.*') ? 'active' : '' }}">
        <i class="ti ti-truck-delivery icon-indigo"></i> Deliveries
    </a>
@endcan

@can('menu.sales-returns')
    <a href="{{ route('sales-returns.index') }}" class="nav-link {{ request()->routeIs('sales-returns.*') ? 'active' : '' }}">
        <i class="ti ti-rotate-2 icon-red"></i> Sales Returns
    </a>
@endcan

@can('menu.cash-handovers')
    <a href="{{ route('cash-handovers.index') }}" class="nav-link {{ request()->routeIs('cash-handovers.*') ? 'active' : '' }}">
        <i class="ti ti-hand-move icon-amber"></i> Cash Handover
    </a>
@endcan

@canany(['menu.targets', 'menu.achievements'])
    <div class="nav-section-title">Performance</div>
@endcanany

@can('menu.targets')
    <a href="{{ route('targets.index') }}" class="nav-link {{ request()->routeIs('targets.*') ? 'active' : '' }}">
        <i class="ti ti-target-arrow icon-orange"></i> Targets
    </a>
@endcan

@can('menu.achievements')
    <a href="{{ route('achievements.index') }}" class="nav-link {{ request()->routeIs('achievements.*') ? 'active' : '' }}">
        <i class="ti ti-trophy icon-green"></i> Achievement
    </a>
@endcan

@canany(['menu.attendance', 'menu.gps-logs', 'menu.live-gps', 'menu.visit-plans', 'menu.visits', 'menu.leave-requests'])
    <div class="nav-section-title">Field Operations</div>
@endcanany

@can('menu.attendance')
    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-check icon-green"></i> Attendance
    </a>
@endcan

@can('menu.leave-requests')
    <a href="{{ route('leave-requests.index') }}" class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-off icon-blue"></i> Leave Requests
    </a>
@endcan

@can('menu.gps-logs')
    <a href="{{ route('gps-logs.index') }}" class="nav-link {{ request()->routeIs('gps-logs.*') ? 'active' : '' }}">
        <i class="ti ti-route icon-cyan"></i> GPS Tracking
    </a>
@endcan

@can('menu.live-gps')
    <a href="{{ route('live-gps.index') }}" class="nav-link {{ request()->routeIs('live-gps.*') ? 'active' : '' }}">
        <i class="ti ti-radar-2 icon-red"></i> Live GPS Dashboard
    </a>
@endcan

@can('menu.visit-plans')
    <a href="{{ route('visit-plans.index') }}" class="nav-link {{ request()->routeIs('visit-plans.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-event icon-purple"></i> Visit Plans
    </a>
@endcan

@can('menu.visits')
    <a href="{{ route('visits.index') }}" class="nav-link {{ request()->routeIs('visits.*') ? 'active' : '' }}">
        <i class="ti ti-walk icon-teal"></i> Dealer Visits
    </a>
@endcan

@canany(['menu.territory-map'])
    <div class="nav-section-title">GIS</div>
@endcanany

@can('menu.territory-map')
    <a href="{{ route('territory-map.index') }}" class="nav-link {{ request()->routeIs('territory-map.*') ? 'active' : '' }}">
        <i class="ti ti-map-2 icon-blue"></i> Territory Map
    </a>
@endcan

@canany(['report.attendance', 'report.visits', 'report.achievement-summary', 'report.territories', 'report.target-achievement'])
    <div class="nav-section-title">Reports</div>

    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <i class="ti ti-report-analytics icon-indigo"></i> Reports
    </a>
@endcanany

@canany(['dealers.view', 'retailers.view'])
    <div class="nav-section-title">Dealer Management</div>
@endcanany

@can('menu.dealers')
    <a href="{{ route('dealers.index') }}" class="nav-link {{ request()->routeIs('dealers.*') ? 'active' : '' }}">
        <i class="ti ti-building-store icon-cyan"></i> Dealers
    </a>
@endcan

@can('menu.retailers')
    <a href="{{ route('retailers.index') }}" class="nav-link {{ request()->routeIs('retailers.*') ? 'active' : '' }}">
        <i class="ti ti-building-cottage icon-pink"></i> Retailers
    </a>
@endcan

@canany(['product-categories.view', 'products.view'])
    <div class="nav-section-title">Product Management</div>
@endcanany

@can('menu.product-categories')
    <a href="{{ route('product-categories.index') }}" class="nav-link {{ request()->routeIs('product-categories.*') ? 'active' : '' }}">
        <i class="ti ti-tags icon-pink"></i> Product Categories
    </a>
@endcan

@can('menu.products')
    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
        <i class="ti ti-package icon-blue"></i> Products
    </a>
@endcan

{{-- Inquiries and Visit Requests are incoming customer work, not setup,
     so they stay in the operational menu. The portal CONTENT they relate
     to - news, promotions, FAQs, service centres, brochures - moved to
     Settings > Content, since that is written once and left alone. --}}
@canany(['menu.inquiries', 'menu.visit-requests'])
    <div class="nav-section-title">Customer Requests</div>
@endcanany

@can('menu.inquiries')
    <a href="{{ route('inquiries.index') }}" class="nav-link {{ request()->routeIs('inquiries.*') ? 'active' : '' }}">
        <i class="ti ti-message-question icon-orange"></i> Inquiries
    </a>
@endcan

@can('menu.visit-requests')
    <a href="{{ route('visit-requests.index') }}" class="nav-link {{ request()->routeIs('visit-requests.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-plus icon-teal"></i> Visit Requests
    </a>
@endcan

@canany(['menu.notifications'])
    <div class="nav-section-title">Communication</div>
@endcanany

@can('menu.notifications')
    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="ti ti-bell icon-amber"></i> Notifications
    </a>
@endcan

