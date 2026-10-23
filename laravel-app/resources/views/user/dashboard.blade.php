{{--
    resources/views/user/dashboard.blade.php
    Route: GET  /app/dashboard  ->  user.dashboard
           POST /app/account    ->  user.account.update
    Controller: App\Http\Controllers\User\DashboardController@index / update

    Expects: $user (App\Models\User), $upcomingCount (int)
--}}
@extends('layouts.user')

@section('title', 'Account')
@section('page-title', 'Account & Profile')
@section('page-subtitle', 'Manage your Pistavora account')
@section('hide-page-title', '1')

@section('body-class', 'page-uniform')

@push('styles')
<style>
    /* ===== Page hero (identical on every page) ===== */
    .pg-hero{
        --pg-a:var(--pink-deep); --pg-b:#F2762E;
        position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap;
        margin:0 0 18px; padding:28px 30px; border-radius:26px; color:#fff; font-family:inherit;
        background:linear-gradient(135deg,var(--pg-a) 0%,var(--pg-b) 100%);
        border:1px solid rgba(255,255,255,.18);
        box-shadow:0 18px 40px -22px var(--pg-a);
    }
    [data-theme="dark"] .pg-hero{ --pg-a:#8E2347; --pg-b:#B5501C; }
    .pg-hero::before{ content:""; position:absolute; inset:0; pointer-events:none; opacity:.55;
        background-image:radial-gradient(rgba(255,255,255,.22) 1.4px, transparent 1.6px); background-size:18px 18px;
        -webkit-mask-image:linear-gradient(115deg, transparent 20%, #000 100%); mask-image:linear-gradient(115deg, transparent 20%, #000 100%); }
    .pg-hero::after{ content:""; position:absolute; right:-70px; top:-90px; width:260px; height:260px; border-radius:50%;
        background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.35), rgba(255,255,255,0) 70%); pointer-events:none; }
    .pg-hero-glow{ position:absolute; left:-60px; bottom:-110px; width:240px; height:240px; border-radius:50%;
        background:radial-gradient(circle, rgba(255,255,255,.18), rgba(255,255,255,0) 70%); pointer-events:none; }
    .pg-deco{ position:absolute; z-index:0; pointer-events:none; user-select:none; line-height:1;
        filter:drop-shadow(0 6px 10px rgba(0,0,0,.18)); animation:pgFloat 6s ease-in-out infinite; }
    .pg-deco.d1{ right:32%; top:14px;    font-size:26px; opacity:.45; animation-delay:0s; }
    .pg-deco.d2{ right:20%; bottom:10px; font-size:34px; opacity:.4;  animation-delay:1.2s; }
    .pg-deco.d3{ right:44%; bottom:14px; font-size:22px; opacity:.35; animation-delay:2.4s; }
    @keyframes pgFloat{ 0%,100%{ transform:translateY(0) rotate(-6deg);} 50%{ transform:translateY(-8px) rotate(6deg);} }
    .pg-hero-text{ position:relative; z-index:1; min-width:0; }
    .pg-hero-badge{ display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; background:rgba(255,255,255,.18); color:#fff;
        font-weight:800; letter-spacing:.1em; font-size:11px; text-transform:uppercase; backdrop-filter:blur(4px); }
    .pg-hero h2{ margin:10px 0 4px; font-family:inherit; font-size:30px; font-weight:800; letter-spacing:-.01em; line-height:1.2; color:#fff; text-shadow:0 2px 12px rgba(0,0,0,.18); }
    .pg-hero p{ margin:0; font-family:inherit; font-size:14px; color:rgba(255,255,255,.9); }
    @media (max-width:720px){
        .pg-hero{ padding:22px 20px; }
        .pg-hero h2{ font-size:26px; }
        .pg-deco.d1{ right:8%; } .pg-deco.d2{ right:4%; } .pg-deco.d3{ display:none; }
    }
    /* ===== Theme toggle inside hero ===== */
    .pg-theme-toggle{
      position:absolute; z-index:2; top:50%; right:22px; transform:translateY(-50%);
      width:44px; height:44px; border-radius:50%; display:grid; place-items:center;
      border:1.5px solid rgba(255,255,255,.55); background:rgba(255,255,255,.16); color:#fff;
      font-size:20px; line-height:1; cursor:pointer; backdrop-filter:blur(4px); padding:0;
      transition:background .2s, transform .2s;
    }
    .pg-theme-toggle:hover{ background:rgba(255,255,255,.28); transform:translateY(-50%) scale(1.08); }
    .pg-theme-toggle:focus-visible{ outline:3px solid rgba(255,255,255,.6); outline-offset:2px; }
    @media (max-width:720px){
      .pg-theme-toggle{ top:18px; right:16px; transform:none; width:40px; height:40px; font-size:18px; }
      .pg-theme-toggle:hover{ transform:scale(1.08); }
      .pg-hero-text{ padding-right:52px; }
    }
    @media (prefers-reduced-motion:reduce){ .pg-deco{ animation:none; } }
</style>
@endpush

@section('content')

    @php
        $avatarChoices = [
            'rose'   => ['#FF5C85', '#B82850'],
            'gold'   => ['#FFC948', '#C77E0E'],
            'teal'   => ['#12B5A6', '#0C8B80'],
            'ink'    => ['#3B3350', '#171126'],
            'blue'   => ['#5B8CFF', '#2E52C7'],
        ];
        $currentAvatar = $user->avatar_theme ?? 'rose';
        [$avatarA, $avatarB] = $avatarChoices[$currentAvatar] ?? $avatarChoices['rose'];
        $hasPhoto = !empty($user->avatar_path) && str_starts_with($user->avatar_path, 'data:');
        $avatarVersion = optional($user->updated_at)->timestamp ?? time();
        $isVerified = !empty($user->email_verified_at);
        $isActive = ($user->status ?? 'active') === 'active';
        $memberSince = optional($user->created_at)->format('M Y');
    @endphp

    {{-- ================= PAGE HERO ================= --}}
    <div class="pg-hero">
        <span class="pg-hero-glow" aria-hidden="true"></span>
        <span class="pg-deco d1" aria-hidden="true">✨</span>
        <span class="pg-deco d2" aria-hidden="true">🎡</span>
        <span class="pg-deco d3" aria-hidden="true">🎟️</span>

        <div class="pg-hero-text">
            <span class="pg-hero-badge">👤 YOUR PROFILE</span>
            <h2>Account &amp; Profile</h2>
            <p>Manage your Pistavora account</p>
        </div>

        <button type="button" class="pg-theme-toggle" id="pgThemeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
            <span class="pg-tt-ico" aria-hidden="true">🌙</span>
        </button>
    </div>

    {{-- Profile banner (avatar + stats) --}}
    <div class="ap-hero">
        @if ($isVerified)
            <span class="ap-hero-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><path d="M20 6L9 17l-5-5"></path></svg>
                Verified Customer
            </span>
        @endif

        <div class="ap-hero-row">
            @if ($hasPhoto)
               <img src="{{ $user->avatar_path }}"
                alt="Profile photo"
                class="ap-hero-avatar"
                id="avatarPreviewImg"
                style="object-fit:cover;">
            @else
                <div class="ap-hero-avatar" id="avatarPreviewFallback">
                    {{ strtoupper(substr($user->name ?? 'G', 0, 1)) }}
                </div>
            @endif

            <div style="flex:1;min-width:180px;">
                <h4 style="margin:0 0 4px;color:#fff;font-size:19px;">Hello, {{ $user->name ?? 'Guest' }} 👋</h4>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="ap-hero-badge">Customer Account</span>
                    @if ($memberSince)
                        <span style="font-size:11.5px;color:rgba(255,255,255,.85);">Member since {{ $memberSince }}</span>
                    @endif
                </div>
            </div>

            <div class="ap-hero-stats">
                <div class="ap-hero-stat">
                    <div class="ap-hero-stat-label">Bookings</div>
                    <div class="ap-hero-stat-value">{{ $upcomingCount }} Upcoming</div>
                </div>
                <div class="ap-hero-stat">
                    <div class="ap-hero-stat-label">Status</div>
                    <div class="ap-hero-stat-value" style="display:flex;align-items:center;gap:5px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $isActive ? '#0D9488' : '#DC2626' }};display:inline-block;"></span>
                        {{ ucfirst($user->status ?? 'active') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="u-grid-2" style="margin-top:16px;">

        {{-- Personal Information --}}
        <div class="u-card" style="margin-top:0;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div>
                    <h4 style="margin:0 0 2px;">Personal Information</h4>
                    <p style="margin:0;font-size:12px;color:var(--muted);">Update your display name and visual theme preferences.</p>
                </div>
                <span class="rb-badge rb-badge-neutral">Profile Data</span>
            </div>

            <form method="POST" action="{{ route('user.account.update') }}" enctype="multipart/form-data" id="accountForm" style="margin-top:16px;">
                @csrf
                <input type="hidden" name="avatar_theme" value="{{ $currentAvatar }}" id="avatarThemeField">

                <p class="u-field-label" style="margin-top:0;">Display name</p>
                <input type="text" name="name" class="u-input" id="displayNameInput" value="{{ old('name', $user->name ?? '') }}" maxlength="60" required>
                <p style="font-size:11px;color:var(--muted);margin:-8px 0 16px;">This name appears on digital tickets, park waivers, and customer service records.</p>

                <p class="u-field-label">Profile photo</p>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                    <label for="avatarPhotoInput" class="u-btn ghost" style="width:auto;display:inline-block;padding:9px 16px;font-size:12px;cursor:pointer;margin:0;">
                        <span id="avatarPhotoLabel">Change photo</span>
                    </label>
                    <input type="file" id="avatarPhotoInput" name="avatar_photo" accept="image/png,image/jpeg,image/webp"
                           form="accountForm" style="display:none;">
                    <span style="font-size:11.5px;color:var(--muted);">JPG, PNG, or WEBP. Max 2MB.</span>
                </div>

                <p class="u-field-label">Avatar accent color</p>
                <p style="font-size:11px;color:var(--muted);margin:-4px 0 10px;">Applied when photo is unassigned.</p>
                <div class="u-swatches" style="margin-bottom:16px;">
                    @foreach ($avatarChoices as $key => $hues)
                        <label class="u-swatch"
                               style="background:linear-gradient(135deg,{{ $hues[0] }},{{ $hues[1] }});"
                               title="{{ ucfirst($key) }}"
                               data-hue-a="{{ $hues[0] }}"
                               data-hue-b="{{ $hues[1] }}">
                            <input type="radio" name="avatar_theme_picker" value="{{ $key }}"
                                   {{ $currentAvatar === $key ? 'checked' : '' }}
                                   onclick="document.getElementById('avatarThemeField').value=this.value; previewAvatarTheme(this.parentElement);">
                        </label>
                    @endforeach
                </div>

                <div class="ap-form-footer">
                    <span style="font-size:11px;color:var(--muted);">
                        @if ($user->updated_at)
                            Last profile update: {{ $user->updated_at->format('M j, Y \a\t g:i A') }}
                        @else
                            No updates yet
                        @endif
                    </span>
                    <div style="display:flex;gap:8px;">
                        <button type="reset" class="u-btn ghost" style="width:auto;padding:9px 16px;font-size:12px;" onclick="document.getElementById('avatarThemeField').value='{{ $currentAvatar }}'; var fb=document.getElementById('avatarPreviewFallback'); if(fb) fb.style.background='linear-gradient(135deg,{{ $avatarA }},{{ $avatarB }})';">Discard</button>
                        <button type="submit" class="u-btn" style="width:auto;padding:9px 18px;font-size:12px;">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Email & Authentication + Account status --}}
        <div>
            <div class="u-card" style="margin-top:0;">
                <h4 style="margin:0 0 2px;">Email &amp; Authentication</h4>
                <p style="margin:0;font-size:12px;color:var(--muted);">Primary login and ticket confirmation contact.</p>

                <p class="u-field-label" style="margin-top:16px;">Registered email</p>
                <div class="ap-static-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--muted);flex-shrink:0;"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                    <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->email ?? 'guest@example.com' }}</span>
                    @if ($isVerified)
                        <span class="rb-badge rb-badge-teal">Verified</span>
                    @else
                        <span class="rb-badge" style="background:var(--amber-pale);color:var(--amber-deep);">Unverified</span>
                    @endif
                </div>
                <p style="font-size:10.5px;color:var(--muted);margin:6px 0 0;">Change of registered email is coming soon.</p>

                <p class="u-field-label" style="margin-top:16px;">Password</p>
                <div class="ap-static-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--muted);flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span style="flex:1;letter-spacing:2px;color:var(--muted);">••••••••••••</span>
                    @if (!empty($user->pending_password_hash))
                        <span class="rb-badge" style="background:var(--amber-pale);color:var(--amber-deep);">Code sent</span>
                    @endif
                </div>

                @if (!empty($user->pending_password_hash))
                    {{-- Step 2: enter the code emailed to confirm the change --}}
                    <div style="margin-top:10px;padding:12px;border:1px solid var(--line);border-radius:12px;background:var(--bg);">
                        <p style="margin:0 0 8px;font-size:11px;color:var(--muted);">
                            We emailed a 6-digit code to confirm your new password. It expires 10 minutes after it was sent.
                        </p>
                        <form method="POST" action="{{ route('user.account.password.confirm') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
                            @csrf
                            <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="6-digit code" required
                                style="flex:1;min-width:120px;padding:8px 10px;border:1px solid var(--line);border-radius:8px;letter-spacing:3px;font-family:'Space Mono',monospace;">
                            <button type="submit" class="rb-badge rb-badge-teal" style="border:none;cursor:pointer;">Confirm change</button>
                        </form>
                        <div style="display:flex;gap:14px;margin-top:8px;">
                            <form method="POST" action="{{ route('user.account.password.resend') }}">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;font-size:10.5px;color:var(--pink-deep);font-weight:700;cursor:pointer;">Resend code</button>
                            </form>
                            <form method="POST" action="{{ route('user.account.password.cancel') }}">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;font-size:10.5px;color:var(--muted);font-weight:700;cursor:pointer;">Cancel</button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Step 1: request the change --}}
                    <details style="margin-top:10px;">
                        <summary style="cursor:pointer;font-size:11.5px;font-weight:700;color:var(--pink-deep);">Change password</summary>
                        <form method="POST" action="{{ route('user.account.password.request') }}" style="margin-top:10px;display:flex;flex-direction:column;gap:8px;">
                            @csrf
                            <input type="password" name="current_password" placeholder="Current password" required
                                style="padding:9px 10px;border:1px solid var(--line);border-radius:8px;">
                            <input type="password" name="new_password" placeholder="New password (min. 8 characters)" required minlength="8"
                                style="padding:9px 10px;border:1px solid var(--line);border-radius:8px;">
                            <input type="password" name="new_password_confirmation" placeholder="Confirm new password" required minlength="8"
                                style="padding:9px 10px;border:1px solid var(--line);border-radius:8px;">
                            <button type="submit" class="rb-badge rb-badge-neutral" style="border:none;cursor:pointer;align-self:flex-start;">Send verification code</button>
                        </form>
                    </details>
                @endif

                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:16px;">
                    <div>
                        <div style="font-size:12.5px;font-weight:700;color:var(--ink);">Two-Factor Authentication</div>
                        <div style="font-size:10.5px;color:var(--muted);">
                            @if ($user->two_factor_enabled ?? false)
                                Enabled — a code is emailed to you at every login.
                            @else
                                Get a one-time code by email each time you log in.
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('user.account.two-factor.toggle') }}">
                        @csrf
                        <button type="submit" class="rb-badge {{ ($user->two_factor_enabled ?? false) ? 'rb-badge-teal' : 'rb-badge-neutral' }}" style="border:none;cursor:pointer;">
                            {{ ($user->two_factor_enabled ?? false) ? 'Enabled — Turn off' : 'Turn on' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="u-card" style="margin-top:14px;background:var(--teal-pale);border-color:var(--teal);">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div class="rb-confirm-icon" style="flex-shrink:0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 3px;">Account Status: {{ $isActive ? 'Good Standing' : ucfirst($user->status ?? 'Active') }}</h4>
                        <p style="margin:0;font-size:12px;color:var(--ink-soft);line-height:1.6;">Your account is {{ $isActive ? 'active and verified' : ($user->status ?? 'active') }}. Digital waivers, e-signatures, and booking history are safely linked to this profile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="only-mobile">
        @csrf
        <button type="submit" class="u-btn ghost">Logout</button>
    </form>

@endsection

@push('scripts')
<script>
    // Instant preview when a color swatch is picked — only matters
    // when no profile photo is set, since a photo always takes visual
    // priority over the accent-color fallback.
    function previewAvatarTheme(swatchLabel) {
        var fallback = document.getElementById('avatarPreviewFallback');
        if (!fallback) return; // a photo is already showing — color swatch won't be visible anyway
        var a = swatchLabel.dataset.hueA;
        var b = swatchLabel.dataset.hueB;
        fallback.style.background = 'linear-gradient(135deg,' + a + ',' + b + ')';
    }

    // Instant client-side preview before the form submits
    document.getElementById('avatarPhotoInput').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        document.getElementById('avatarPhotoLabel').textContent = 'Uploading...';

        var reader = new FileReader();
        reader.onload = function (evt) {
            var existingImg = document.getElementById('avatarPreviewImg');
            var fallback = document.getElementById('avatarPreviewFallback');

            if (existingImg) {
                existingImg.src = evt.target.result;
            } else if (fallback) {
                var img = document.createElement('img');
                img.src = evt.target.result;
                img.id = 'avatarPreviewImg';
                img.alt = 'Profile photo';
                img.className = 'ap-hero-avatar';
                img.style.objectFit = 'cover';
                fallback.replaceWith(img);
            }
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('pgThemeToggle');
    if (!btn) return;
    var ico = btn.querySelector('.pg-tt-ico');
    var OLD = '#themeToggle, #theme-toggle, #darkModeToggle, .theme-toggle, .theme-switch, [data-theme-toggle]';

    function themeEl() { return document.querySelector('[data-theme]') || document.documentElement; }
    function isDark()  { return themeEl().getAttribute('data-theme') === 'dark'; }
    function paint()   { ico.textContent = isDark() ? '\u2600\uFE0F' : '\uD83C\uDF19'; }

    // Hide the old floating toggle (kept in the DOM so it can still be clicked)
    document.querySelectorAll(OLD).forEach(function (el) {
        if (el !== btn) el.style.display = 'none';
    });

    btn.addEventListener('click', function () {
        var old = document.querySelector(OLD);
        if (old && old !== btn) {
            old.click();
        } else {
            var next = isDark() ? 'light' : 'dark';
            themeEl().setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        }
        setTimeout(paint, 50);
    });

    new MutationObserver(paint).observe(themeEl(), { attributes: true, attributeFilter: ['data-theme'] });
    paint();
})();
</script>
@endpush