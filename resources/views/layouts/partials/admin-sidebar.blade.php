{{-- Each module appends its own nav-link (and nav-section-title, if it starts a new group)
     here as it is built. Every link is gated by its menu permission.
     Section order: Order Operations, Performance, Field Operations, GIS,
     Reports, Access Control, Organization, Dealer Management, Product
     Management, Customer Portal, Communication, System. --}}

<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="ti ti-gauge icon-blue"></i> Dashboard
</a>

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

@can('menu.cash-handovers')
    <a href="{{ route('cash-handovers.index') }}" class="nav-link {{ request()->routeIs('cash-handovers.*') ? 'active' : '' }}">
        <i class="ti ti-hand-move icon-amber"></i> Cash Handover
    </a>
@endcan

@canany(['menu.targets'])
    <div class="nav-section-title">Performance</div>
@endcanany

@can('menu.targets')
    <a href="{{ route('targets.index') }}" class="nav-link {{ request()->routeIs('targets.*') ? 'active' : '' }}">
        <i class="ti ti-target-arrow icon-orange"></i> Targets
    </a>
@endcan

@canany(['menu.attendance', 'menu.gps-logs', 'menu.live-gps', 'menu.visit-plans', 'menu.visits'])
    <div class="nav-section-title">Field Operations</div>
@endcanany

@can('menu.attendance')
    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-check icon-green"></i> Attendance
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

@canany(['report.attendance', 'report.visits', 'report.order-performance', 'report.collections', 'report.territories', 'report.dealer-ledger'])
    <div class="nav-section-title">Reports</div>

    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <i class="ti ti-report-analytics icon-indigo"></i> Reports
    </a>
@endcanany

@canany(['users.view', 'roles.view', 'permissions.view'])
    <div class="nav-section-title">Access Control</div>
@endcanany

@can('menu.users')
    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <i class="ti ti-users icon-indigo"></i> Users
    </a>
@endcan

@can('menu.roles')
    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
        <i class="ti ti-shield-lock icon-purple"></i> Roles
    </a>
@endcan

@can('menu.permissions')
    <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
        <i class="ti ti-key icon-amber"></i> Permissions
    </a>
@endcan

@canany(['sales-teams.view', 'territories.view'])
    <div class="nav-section-title">Organization</div>
@endcanany

@can('menu.sales-teams')
    <a href="{{ route('sales-teams.index') }}" class="nav-link {{ request()->routeIs('sales-teams.*') ? 'active' : '' }}">
        <i class="ti ti-users-group icon-teal"></i> Sales Teams
    </a>
@endcan

@can('menu.territories')
    <a href="{{ route('territories.index') }}" class="nav-link {{ request()->routeIs('territories.*') ? 'active' : '' }}">
        <i class="ti ti-map-pin-2 icon-orange"></i> Territories
    </a>
@endcan

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

@canany(['menu.inquiries', 'menu.visit-requests', 'menu.news', 'menu.promotions', 'menu.faqs', 'menu.service-centers', 'menu.brochures'])
    <div class="nav-section-title">Customer Portal</div>
@endcanany

@can('menu.inquiries')
    <a href="{{ route('inquiries.index') }}" class="nav-link {{ request()->routeIs('inquiries.*') ? 'active' : '' }}">
        <i class="ti ti-message-2 icon-cyan"></i> Inquiries
    </a>
@endcan

@can('menu.visit-requests')
    <a href="{{ route('visit-requests.index') }}" class="nav-link {{ request()->routeIs('visit-requests.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-event icon-purple"></i> Visit Requests
    </a>
@endcan

@can('menu.news')
    <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}">
        <i class="ti ti-news icon-blue"></i> News
    </a>
@endcan

@can('menu.promotions')
    <a href="{{ route('promotions.index') }}" class="nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}">
        <i class="ti ti-discount-2 icon-pink"></i> Promotions
    </a>
@endcan

@can('menu.faqs')
    <a href="{{ route('faqs.index') }}" class="nav-link {{ request()->routeIs('faqs.*') ? 'active' : '' }}">
        <i class="ti ti-help-circle icon-teal"></i> FAQs
    </a>
@endcan

@can('menu.service-centers')
    <a href="{{ route('service-centers.index') }}" class="nav-link {{ request()->routeIs('service-centers.*') ? 'active' : '' }}">
        <i class="ti ti-building-store icon-orange"></i> Service Centers
    </a>
@endcan

@can('menu.brochures')
    <a href="{{ route('brochures.index') }}" class="nav-link {{ request()->routeIs('brochures.*') ? 'active' : '' }}">
        <i class="ti ti-file-type-pdf icon-red"></i> Brochures
    </a>
@endcan

@canany(['menu.notifications', 'menu.announcements'])
    <div class="nav-section-title">Communication</div>
@endcanany

@can('menu.notifications')
    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="ti ti-bell icon-amber"></i> Notifications
    </a>
@endcan

@can('menu.announcements')
    <a href="{{ route('announcements.index') }}" class="nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
        <i class="ti ti-speakerphone icon-pink"></i> Announcements
    </a>
@endcan

@canany(['menu.activity-log', 'menu.holidays', 'menu.settings'])
    <div class="nav-section-title">System</div>
@endcanany

@can('menu.activity-log')
    <a href="{{ route('activity-log.index') }}" class="nav-link {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
        <i class="ti ti-history icon-indigo"></i> Activity Log
    </a>
@endcan

@can('menu.holidays')
    <a href="{{ route('holidays.index') }}" class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
        <i class="ti ti-calendar-event icon-red"></i> Holidays
    </a>
@endcan

@can('menu.settings')
    <a href="{{ route('settings.edit') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <i class="ti ti-settings icon-cyan"></i> Settings
    </a>
@endcan
