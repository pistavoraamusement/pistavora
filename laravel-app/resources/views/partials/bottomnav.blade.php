{{--
    resources/views/partials/bottomnav.blade.php
    Desktop (>=960px): admin-style dark sidebar (collapsible rail, remembered in
    localStorage under the same key the admin sidebar uses).
    Mobile: bottom tab bar.

    Tab order: Booking -> My Bookings -> Account -> Activity.
    Waiver is not a nav item — it's a mandatory gate shown right after
    login/register (see WaiverController@show).
--}}
@php
    // Who is signed in? Controllers that pass $user win; otherwise fall back to the session.
    $navUser = $user ?? (auth()->check() ? auth()->user() : null);
    if (! $navUser && session('user_id')) {
        $navUser = \App\Models\User::find(session('user_id'));
    }
    $navName    = trim((string) ($navUser->name ?? '')) ?: (trim((string) ($navUser->fullname ?? '')) ?: 'Guest');
    $navInitial = strtoupper(mb_substr($navName, 0, 1));
    $navAvatars = [
        'rose' => ['#FF5C85', '#B82850'],
        'gold' => ['#FFC948', '#C77E0E'],
        'teal' => ['#12B5A6', '#0C8B80'],
        'ink'  => ['#3B3350', '#171126'],
        'blue' => ['#5B8CFF', '#2E52C7'],
    ];
    [$navA, $navB] = $navAvatars[$navUser->avatar_theme ?? 'rose'] ?? $navAvatars['rose'];
    $navPhoto = ! empty($navUser->avatar_path) ? asset('storage/' . $navUser->avatar_path) : null;
@endphp
<script>
    /* apply the saved sidebar state before the nav paints (same key as the admin sidebar) */
    (function () {
        var KEY = 'wp.sidebar.collapsed', root = document.documentElement;
        try { if (localStorage.getItem(KEY) === '1') root.classList.add('sb-collapsed'); } catch (e) {}
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('navToggle');
            if (!btn) return;
            function sync() { btn.setAttribute('aria-expanded', root.classList.contains('sb-collapsed') ? 'false' : 'true'); }
            sync();
            btn.addEventListener('click', function () {
                root.classList.toggle('sb-collapsed');
                try { localStorage.setItem(KEY, root.classList.contains('sb-collapsed') ? '1' : '0'); } catch (e) {}
                sync();
            });
        });
    })();
</script>
<nav class="bottomnav" aria-label="Main menu">
    <div class="nav-brand">
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Collapse or expand menu" aria-expanded="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="18" x2="20" y2="18"></line></svg>
        </button>
        <div class="brand-mark"><img src="{{ asset('images/pistavora-mark.svg') }}" alt="Pistavora"></div>
        <div class="brand-text">
            <span class="brand-name">Pistavora</span>
            <span class="brand-sub">Lipa Branch &middot; Customer</span>
        </div>
    </div>

    <div class="nav-list">
        <span class="nav-section-label only-desktop">My Visit</span>

        <a href="{{ route('user.booking') }}" title="Booking"
           class="bn-item {{ request()->routeIs('user.booking', 'user.booking.store') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path></svg>
            </span>
            <span class="bn-label">Booking</span>
        </a>

        <a href="{{ route('user.bookings') }}" title="My Bookings"
           class="bn-item {{ request()->routeIs('user.bookings*') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><path d="M2 13h20"></path></svg>
            </span>
            <span class="bn-label">My Bookings</span>
        </a>

        <a href="{{ route('user.dashboard') }}" title="Account"
           class="bn-item {{ request()->routeIs('user.account') || request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 4-7 8-7s8 3 8 7"></path></svg>
            </span>
            <span class="bn-label">Account</span>
        </a>

        <a href="{{ route('user.activity') }}" title="Activity"
           class="bn-item {{ request()->routeIs('user.activity') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </span>
            <span class="bn-label">Activity</span>
        </a>
    </div>

    <div class="nav-foot only-desktop">
        <div class="nav-foot-user">
            <span class="nav-foot-avatar" style="--u-avatar-a:{{ $navA }};--u-avatar-b:{{ $navB }};">
                @if ($navPhoto)
                    <img src="{{ $navPhoto }}" alt="">
                @else
                    {{ $navInitial }}
                @endif
            </span>
            <span class="nav-foot-info">
                <span class="nav-foot-name">{{ $navName }}</span>
                <span class="nav-foot-role">Customer</span>
            </span>
            <form method="POST" action="{{ route('logout') }}" class="nav-logout-form" id="userLogoutForm">
                @csrf
                <button type="button" class="nav-foot-logout" title="Log out" aria-label="Log out" onclick="openUserLogoutModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- Logout confirmation modal (customer) --}}
<style>
.ulm-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px}
.ulm-overlay.active{display:flex}
.ulm-card{background:#fff;border-radius:24px;padding:36px 30px 24px;width:100%;max-width:400px;text-align:center;box-shadow:0 24px 60px -12px rgba(15,23,42,.35);animation:ulmPop .18s ease;font-family:inherit}
@keyframes ulmPop{from{transform:scale(.95);opacity:0}to{transform:scale(1);opacity:1}}
.ulm-icon{width:64px;height:64px;margin:0 auto 18px;border-radius:50%;background:#E0E7FF;color:#4F46E5;display:flex;align-items:center;justify-content:center}
.ulm-icon svg{width:28px;height:28px}
.ulm-card h3{font-size:1.35rem;font-weight:800;color:#0F172A;margin:0 0 8px}
.ulm-card p{font-size:.95rem;color:#475569;margin:0 0 6px}
.ulm-sub{font-size:.82rem;color:#64748B;line-height:1.55;margin-bottom:24px}
.ulm-actions{display:flex;gap:10px}
.ulm-actions button{flex:1;padding:12px 16px;border-radius:12px;font-weight:700;font-size:.92rem;cursor:pointer;font-family:inherit;transition:background .14s,transform .08s}
.ulm-actions button:active{transform:scale(.97)}
.ulm-cancel{background:#F8FAFC;color:#475569;border:1px solid #E2E8F0}
.ulm-cancel:hover{background:#E2E8F0}
.ulm-confirm{background:#4F46E5;color:#fff;border:none;box-shadow:0 8px 18px -6px rgba(79,70,229,.55)}
.ulm-confirm:hover{background:#4338CA}
.ulm-hint{margin-top:18px;font-size:.78rem;color:#64748B}
.ulm-kbd{background:#F8FAFC;border:1px solid #CBD5E1;border-radius:5px;padding:1px 7px;font-size:.72rem;font-weight:700;color:#475569;margin:0 2px}
[data-theme="dark"] .ulm-card{background:#1E293B}
[data-theme="dark"] .ulm-card h3{color:#F1F5F9}
[data-theme="dark"] .ulm-card p{color:#CBD5E1}
[data-theme="dark"] .ulm-sub,[data-theme="dark"] .ulm-hint{color:#94A3B8}
[data-theme="dark"] .ulm-icon{background:rgba(79,70,229,.25);color:#A5B4FC}
[data-theme="dark"] .ulm-cancel{background:#0F172A;color:#CBD5E1;border-color:#334155}
[data-theme="dark"] .ulm-kbd{background:#0F172A;border-color:#334155;color:#CBD5E1}
@media (max-width:480px){.ulm-hint{display:none}}
</style>

<div class="ulm-overlay" id="userLogoutOverlay" role="dialog" aria-modal="true" aria-labelledby="ulmTitle">
    <div class="ulm-card">
        <div class="ulm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </div>
        <h3 id="ulmTitle">Confirm Logout</h3>
        <p>Are you sure you want to log out?</p>
        <div class="ulm-sub">Your session, <strong>{{ $navName }}</strong>, will be ended.</div>
        <div class="ulm-actions">
            <button type="button" class="ulm-cancel" onclick="closeUserLogoutModal()">Cancel</button>
            <button type="button" class="ulm-confirm" onclick="confirmUserLogout()">Yes, Logout</button>
        </div>
        <div class="ulm-hint">Press <span class="ulm-kbd">Esc</span> to cancel &bull; <span class="ulm-kbd">Enter</span> to confirm</div>
    </div>
</div>

<script>
function openUserLogoutModal(){
    var o = document.getElementById('userLogoutOverlay');
    if (!o) return;
    o.classList.add('active');
    var c = o.querySelector('.ulm-confirm'); if (c) c.focus();
}
function closeUserLogoutModal(){
    var o = document.getElementById('userLogoutOverlay');
    if (o) o.classList.remove('active');
}
function confirmUserLogout(){
    var f = document.getElementById('userLogoutForm');
    if (f) f.submit();
}
document.getElementById('userLogoutOverlay').addEventListener('click', function(e){
    if (e.target === this) closeUserLogoutModal();
});
document.addEventListener('keydown', function(e){
    var o = document.getElementById('userLogoutOverlay');
    if (!o || !o.classList.contains('active')) return;
    if (e.key === 'Escape') { closeUserLogoutModal(); }
    else if (e.key === 'Enter') {
        if (document.activeElement && document.activeElement.closest('.ulm-actions')) return;
        e.preventDefault(); confirmUserLogout();
    }
});
</script>
