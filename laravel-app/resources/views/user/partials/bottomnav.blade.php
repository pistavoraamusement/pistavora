{{--
    resources/views/partials/bottomnav.blade.php
    Bottom tab bar on mobile, left sidebar on desktop.

    Tab order: Booking -> Waiver -> My Bookings -> Account (last).
--}}
<nav class="bottomnav">
    <div class="nav-brand" aria-hidden="true">
        <img src="{{ asset('images/pistavora-logo-dark.svg') }}" alt="Pistavora" class="nav-brand-logo">
    </div>

    <span class="nav-section-label only-desktop">My Visit</span>

    <a href="{{ route('user.booking') }}"
       class="bn-item {{ request()->routeIs('user.booking', 'user.booking.store') ? 'active' : '' }}">
        <span class="ic3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path></svg>
        </span>
        Booking
    </a>

    <a href="{{ route('user.waiver') }}"
       class="bn-item {{ request()->routeIs('user.waiver*') ? 'active' : '' }}">
        <span class="ic3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><path d="M14 3v5h5"></path><path d="M9 13h6"></path><path d="M9 17h4"></path></svg>
        </span>
        Waiver
    </a>

    <a href="{{ route('user.bookings') }}"
       class="bn-item {{ request()->routeIs('user.bookings*') ? 'active' : '' }}">
        <span class="ic3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><path d="M2 13h20"></path></svg>
        </span>
        My Bookings
    </a>

    <a href="{{ route('user.dashboard') }}"
       class="bn-item {{ request()->routeIs('user.account') || request()->routeIs('user.dashboard') ? 'active' : '' }}">
        <span class="ic3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 4-7 8-7s8 3 8 7"></path></svg>
        </span>
        Account
    </a>

    <div class="nav-spacer only-desktop" aria-hidden="true"></div>

    @php
        $u = $user ?? auth()->user();
        $navAvatarThemes = [
            'rose' => ['#FF5C85', '#B82850'],
            'gold' => ['#FFC948', '#C77E0E'],
            'teal' => ['#12B5A6', '#0C8B80'],
            'ink'  => ['#3B3350', '#171126'],
            'blue' => ['#5B8CFF', '#2E52C7'],
        ];
        [$navAvatarA, $navAvatarB] = $navAvatarThemes[$u->avatar_theme ?? 'rose'] ?? $navAvatarThemes['rose'];
    @endphp
    <div class="nav-foot only-desktop">
        <div class="nav-foot-user">
            <span class="nav-foot-avatar" style="--u-avatar-a:{{ $navAvatarA }};--u-avatar-b:{{ $navAvatarB }};">
                {{ strtoupper(substr($u->name ?? 'G', 0, 1)) }}
            </span>
            <span>
                <span class="nav-foot-name" style="display:block;">{{ $u->name ?? 'Guest' }}</span>
                <span class="nav-foot-role">Customer</span>
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-foot-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
                Log out
            </button>
        </form>
    </div>
</nav>