{{--
    The pinned bottom of the sidebar.

    Settings and Guide live here rather than in the scrolling list because
    they are destinations you reach from anywhere, not steps in a workflow.
    The main menu is long enough that anything at its end is effectively
    hidden.

    Settings appears only if the viewer can actually open something inside
    it - a field executive has no setup screens, and a link that answers
    403 is worse than no link. Guide is shown to everyone, deliberately:
    it is the page that explains why a sidebar looks the way it does, so
    the people seeing fewest menus are the ones who most need it.
--}}
@php
    $settingsAvailable = \App\Support\SettingsNavigation::availableTo(auth()->user());
    $settingsActive = \App\Support\SettingsNavigation::isActive();
@endphp

<div class="sidebar-footer">
    @if ($settingsAvailable)
        <a href="{{ route('settings.index') }}" class="nav-link {{ $settingsActive ? 'active' : '' }}">
            <i class="ti ti-settings icon-cyan"></i> Settings
        </a>
    @endif

    <a href="{{ route('guide.index') }}" class="nav-link {{ request()->routeIs('guide.*') ? 'active' : '' }}">
        <i class="ti ti-book icon-blue"></i> Guide
    </a>
</div>
