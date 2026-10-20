{{-- Place at: resources/views/admin/my-account.blade.php --}}
@extends('layouts.sidebar')

@section('title', 'My Account')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
<style>
    /* ── Header card ── */
    .page-head {
        background: var(--card);
        padding: 16px 22px;
        margin-bottom: 14px;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    .ph-left { display: flex; align-items: center; gap: 16px; min-width: 0; }

    /* ── Facebook-style avatar ── */
    .avatar-wrap { position: relative; flex-shrink: 0; width: 112px; height: 112px; margin: -56px auto 12px; z-index: 2; }

    .ph-avatar {
        width: 112px;
        height: 112px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);
        color: #fff;
        font-family: 'Source Serif 4', serif;
        font-weight: 700;
        font-size: 2.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-pink);
        overflow: hidden;
        cursor: pointer;
        border: 4px solid #fff;
        box-shadow: 0 6px 18px rgba(67, 56, 202, .25);
        padding: 0;
        position: relative;
    }
    .ph-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .ph-avatar::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .0);
        transition: background .15s;
    }
    .ph-avatar:hover::after { background: rgba(0, 0, 0, .12); }

    .cam-badge {
        position: absolute;
        right: 4px;
        bottom: 4px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: #E4E6EB;
        color: #1c1e21;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s;
        padding: 0;
    }
    .cam-badge:hover { background: #D8DADF; }

    /* dropdown menu */
    .avatar-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        z-index: 50;
        min-width: 230px;
        padding: 6px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .15);
    }
    .avatar-menu[hidden] { display: none; }

    .avatar-menu button {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 10px;
        border: none;
        background: none;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer;
        text-align: left;
    }
    .avatar-menu button:hover { background: #F0F2F5; }
    .avatar-menu button .ic {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #E4E6EB;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .avatar-menu button.danger { color: #D14343; }
    .avatar-menu button[hidden] { display: none; }

    .page-head .eyebrow {
        font-size: .66rem;
        font-weight: 700;
        color: var(--pink-deep);
        text-transform: uppercase;
        letter-spacing: .09em;
        margin-bottom: 3px;
    }

    .page-head h2 {
        font-family: 'Source Serif 4', serif;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--ink);
        line-height: 1.2;
    }

    .page-head .sub { font-size: 12px; color: var(--muted); margin-top: 3px; }

    .stat-strip { display: flex; align-items: stretch; flex-wrap: wrap; gap: 8px; }

    .stat-pill {
        display: flex;
        flex-direction: column;
        min-width: 104px;
        padding: 7px 14px;
        background: var(--bg);
        border: 1px solid var(--line);
        border-radius: 12px;
    }

    .stat-pill .label {
        font-size: .62rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .stat-pill .value {
        font-family: 'Source Serif 4', serif;
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--ink);
        line-height: 1.3;
        white-space: nowrap;
    }

    .stat-pill .value.is-ok  { color: var(--green); }
    .stat-pill .value.is-off { color: var(--deduct); }

    /* ── Profile card identity ── */
    .profile-cover {
        height: 104px;
        border-radius: 16px 16px 0 0;
        background:
            radial-gradient(circle at 15% 120%, rgba(255,255,255,.22) 0, transparent 45%),
            radial-gradient(circle at 90% -20%, rgba(255,255,255,.18) 0, transparent 40%),
            linear-gradient(120deg, #6366F1 0%, #4338CA 60%, #7C3AED 100%);
    }
    .profile-id { text-align: center; padding: 0 22px 18px; }
    .pid-name {
        font-family: 'Source Serif 4', serif;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--ink);
        line-height: 1.25;
    }
    .pid-sub { font-size: 12.5px; color: var(--muted); margin-top: 3px; }
    .pid-sub .dot { margin: 0 4px; }
    .table-box > .box-head { border-top: 1px solid var(--line); }

    /* ── Photo modal (Facebook style) ── */
    .pm-overlay {
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(15, 23, 42, .55);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .pm-overlay[hidden] { display: none; }

    .pm-card {
        width: 100%;
        max-width: 420px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, .3);
        overflow: hidden;
    }

    .pm-head {
        position: relative;
        padding: 16px 52px;
        text-align: center;
        border-bottom: 1px solid var(--line);
    }
    .pm-head h4 { font-size: 1.05rem; font-weight: 700; color: var(--ink); }
    .pm-x {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 50%;
        background: #E4E6EB;
        cursor: pointer;
        color: #1c1e21;
    }
    .pm-x:hover { background: #D8DADF; }

    .pm-body { padding: 22px 18px 8px; display: flex; flex-direction: column; align-items: center; }

    .pm-circle {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);
        color: #fff;
        font-family: 'Source Serif 4', serif;
        font-weight: 700;
        font-size: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 4px #fff, 0 0 0 5px var(--line);
    }
    .pm-circle img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .pm-text { margin-top: 16px; font-size: 13px; color: var(--ink-soft); text-align: center; line-height: 1.5; }

    .pm-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 18px 18px; }

    .pm-btn {
        height: 36px;
        padding: 0 18px;
        border: none;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .pm-btn.cancel { background: transparent; color: var(--pink-deep); }
    .pm-btn.cancel:hover { background: #F0F2F5; }
    .pm-btn.primary { background: var(--pink-deep); color: #fff; }
    .pm-btn.primary:hover { background: var(--pink-dark); }
    .pm-btn.danger { background: #D14343; color: #fff; }
    .pm-btn.danger:hover { background: #B73636; }
    .pm-btn:disabled { opacity: .5; cursor: not-allowed; }

    /* ── Two cards side by side ── */
    .acct-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 14px;
    }

    .table-box {
        background: var(--card);
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .box-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        padding: 16px 22px 12px;
        border-bottom: 1px solid var(--line);
    }

    .box-head h3 {
        font-family: 'Source Serif 4', serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--ink);
    }

    .box-head .hint { font-size: 12px; color: var(--muted); }

    .box-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 16px 22px 18px;
    }

    .box-body form { display: flex; flex-direction: column; flex: 1; }

    /* ── Fields ── */
    .f-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .f-row.one { grid-template-columns: minmax(0, 1fr); }

    .field { margin-bottom: 12px; min-width: 0; }

    .field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 5px;
    }

    .field label .opt { font-weight: 500; text-transform: none; letter-spacing: 0; opacity: .8; }

    .input { position: relative; }

    .input > .lead {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: var(--muted);
        pointer-events: none;
        transition: color .15s;
    }

    .input.top > .lead { top: 13px; transform: none; }

    .input input,
    .input textarea {
        width: 100%;
        padding: 0 12px 0 34px;
        border: 1px solid var(--line-strong);
        border-radius: 10px;
        font-size: 12.5px;
        font-family: 'Inter', sans-serif;
        color: var(--ink);
        background: var(--bg);
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .input input { height: 38px; }

    .input textarea {
        padding: 10px 12px 10px 34px;
        min-height: 66px;
        resize: vertical;
        line-height: 1.45;
    }

    .input.has-eye input { padding-right: 38px; }

    .input input:hover,
    .input textarea:hover { border-color: var(--pink); }

    .input input:focus,
    .input textarea:focus {
        outline: none;
        border-color: var(--pink-deep);
        background: #fff;
        box-shadow: 0 0 0 3px var(--pink-light);
    }

    .input:focus-within > .lead { color: var(--pink-deep); }

    .input input.is-invalid,
    .input textarea.is-invalid { border-color: #D14343; background: #FFF6F6; }

    .input input[readonly] {
        background: #EEF1F6;
        color: var(--ink-soft);
        border-color: var(--line);
        cursor: not-allowed;
    }
    .input input[readonly]:hover { border-color: var(--line); }

    .eye {
        position: absolute;
        right: 4px;
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        border: none;
        background: none;
        color: var(--muted);
        font-size: 12px;
        cursor: pointer;
        border-radius: 8px;
        transition: background .15s, color .15s;
    }
    .eye:hover { background: var(--line); color: var(--ink); }

    .err {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #B02020;
        font-size: 11.5px;
        font-weight: 500;
        margin-top: 5px;
    }

    .note {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        padding: 9px 12px;
        border-radius: 10px;
        background: var(--bg);
        border: 1px solid var(--line);
        color: var(--ink-soft);
        font-size: 11.5px;
        line-height: 1.5;
        margin-bottom: 14px;
    }
    .note i { color: var(--pink-deep); margin-top: 2px; }

    /* ── Strength meter + rules ── */
    .meter { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
    .meter span { height: 4px; border-radius: 99px; background: var(--line); transition: background .2s; }
    .meter[data-level="1"] span:nth-child(-n+1) { background: #D14343; }
    .meter[data-level="2"] span:nth-child(-n+2) { background: #E0A030; }
    .meter[data-level="3"] span:nth-child(-n+3) { background: #2E9E5B; }
    .meter[data-level="4"] span:nth-child(-n+4) { background: #1F7A45; }

    .meter-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        color: var(--muted);
        margin: 6px 0 10px;
    }
    .meter-row strong { color: var(--ink-soft); font-weight: 700; }

    .rules {
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        margin-bottom: 14px;
    }

    .rules li {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        color: var(--muted);
        transition: color .15s;
    }

    .rules li i {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 1.5px solid var(--line-strong);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .45rem;
        color: transparent;
        transition: background .15s, border-color .15s, color .15s;
    }

    .rules li.met { color: var(--ink); }
    .rules li.met i { background: #2E9E5B; border-color: #2E9E5B; color: #fff; }

    /* ── Buttons & alerts ── */
    .actions { margin-top: auto; padding-top: 2px; display: flex; align-items: center; gap: 10px; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 38px;
        padding: 0 18px;
        border: none;
        border-radius: 10px;
        background: var(--pink-deep);
        color: #fff;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 12.5px;
        cursor: pointer;
        transition: background .15s, transform .08s, opacity .15s;
    }
    .btn:hover:not(:disabled) { background: var(--pink-dark); }
    .btn:active:not(:disabled) { transform: scale(.97); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }

    .alert-ok {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 10px;
        background: #DDF3E6;
        color: #1F7A45;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        max-height: 60px;
        overflow: hidden;
        transition: opacity .3s ease, max-height .35s ease, margin .35s ease, padding .35s ease;
    }

    .alert-ok.hide {
        opacity: 0;
        max-height: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    @media (max-width: 1000px) {
        .acct-grid { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 600px) {
        .page-head { padding: 14px; }
        .stat-strip { width: 100%; }
        .stat-pill { flex: 1 1 calc(50% - 8px); }
        .box-head, .box-body { padding-left: 14px; padding-right: 14px; }
        .f-row { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endsection

@section('content')
    @php
        $roleLabels = [
            'admin'   => 'Administrator',
            'manager' => 'Manager',
            'tl'      => 'Team Lead',
            'cashier' => 'Cashier',
        ];
        $roleLabel = $roleLabels[$user->role] ?? ucfirst((string) $user->role);
        $initial   = strtoupper(mb_substr($user->fullname ?? 'U', 0, 1));
        $since     = !empty($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->format('M d, Y') : '—';
        $status    = strtolower((string) ($user->status ?? 'active'));
        $isActive  = $status === 'active';

        $photoUrl  = !empty($user->photo) ? asset('storage/' . $user->photo) : null;

        // Original values (used by JS to detect changes)
        $original = [
            'fullname'  => (string) $user->fullname,
            'username'  => (string) $user->username,
        ];
    @endphp

    {{-- ── Header: identity + quick facts ── --}}
    <div class="page-head">
        <div class="ph-left">

            <div>
                <div class="eyebrow">Admin &middot; Account</div>
                <h2 style="font-family: Arial, Helvetica, sans-serif">My Account</h2>
                <p class="sub">Manage your profile picture, name and password</p>
            </div>
        </div>

        <div class="stat-strip">
            <div class="stat-pill">
                <span class="label">Role</span>
                <span class="value">{{ $roleLabel }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Status</span>
                <span class="value {{ $isActive ? 'is-ok' : 'is-off' }}">{{ ucfirst($status) }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Member since</span>
                <span class="value">{{ $since }}</span>
            </div>
        </div>
    </div>

    {{-- ── Photo modal (preview before saving / confirm remove) ── --}}
    <div class="pm-overlay" id="pmOverlay" hidden>
        <div class="pm-card" role="dialog" aria-modal="true" aria-labelledby="pmTitle">
            <div class="pm-head">
                <h4 id="pmTitle">Update profile picture</h4>
                <button type="button" class="pm-x" id="pmClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="pm-body">
                <div class="pm-circle" id="pmCircle"></div>
                <p class="pm-text" id="pmText"></p>
            </div>
            <div class="pm-foot">
                <button type="button" class="pm-btn cancel" id="pmCancel">Cancel</button>
                <button type="button" class="pm-btn primary" id="pmSave">Save</button>
            </div>
        </div>
    </div>

    <div class="acct-grid">

        {{-- ── Profile details ── --}}
        <section class="table-box">
            <div class="profile-cover"></div>
            <div class="profile-id">
            {{-- Facebook-style clickable avatar --}}
                <div class="avatar-wrap" id="avatarWrap">
                    <button type="button" class="ph-avatar" id="avatarBtn" aria-label="Change profile picture"
                            data-initial="{{ $initial }}">
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $user->fullname }}">
                        @else
                            {{ $initial }}
                        @endif
                    </button>
                    <button type="button" class="cam-badge" id="camBtn" aria-label="Update profile picture">
                        <i class="fa-solid fa-camera"></i>
                    </button>

                    <div class="avatar-menu" id="avatarMenu" hidden>
                        <button type="button" id="menuChange">
                            <span class="ic"><i class="fa-solid fa-image"></i></span>
                            <span id="menuChangeText">{{ $photoUrl ? 'Change profile picture' : 'Upload profile picture' }}</span>
                        </button>
                        <button type="button" class="danger" id="menuRemove" {{ $photoUrl ? '' : 'hidden' }}>
                            <span class="ic"><i class="fa-regular fa-trash-can"></i></span>
                            Remove profile picture
                        </button>
                    </div>
                </div>

                <h3 class="pid-name">{{ $user->fullname }}</h3>
                <div class="pid-sub">{{ '@' . $user->username }} <span class="dot">&middot;</span> {{ $roleLabel }}</div>
                @error('photo')
                    <div class="err" style="justify-content:center"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="box-head">
                <h3>Profile details</h3>
                <span class="hint">Update your name and username</span>
            </div>

            <div class="box-body">
                @if (session('info_success'))
                    <div class="alert-ok"><i class="fa-solid fa-circle-check"></i> {{ session('info_success') }}</div>
                @endif

                <form method="POST" action="{{ route('my-account.update') }}" id="infoForm"
                      enctype="multipart/form-data"
                      data-original='@json($original)'>
                    @csrf
                    @method('PUT')

                    {{-- Hidden photo fields (controlled by the avatar above) --}}
                    <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" hidden>
                    <input type="hidden" name="remove_photo" id="removePhoto" value="0">

                    <div class="f-row one">
                        <div class="field">
                            <label for="fullname">Full name</label>
                            <div class="input">
                                <i class="fa-regular fa-id-card lead"></i>
                                <input type="text" id="fullname" name="fullname"
                                       class="{{ $errors->has('fullname') ? 'is-invalid' : '' }}"
                                       value="{{ old('fullname', $user->fullname) }}" autocomplete="name" required>
                            </div>
                            @error('fullname')
                                <div class="err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="username">Username</label>
                            <div class="input">
                                <i class="fa-solid fa-at lead"></i>
                                <input type="text" id="username" name="username"
                                       class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                                       value="{{ old('username', $user->username) }}" autocomplete="username" required>
                            </div>
                            @error('username')
                                <div class="err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn" id="infoBtn" disabled>
                            <i class="fa-solid fa-floppy-disk"></i> Save changes
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ── Change password ── --}}
        <section class="table-box">
            <div class="box-head">
                <h3>Change password</h3>
                <span class="hint">Use a password you don't use elsewhere</span>
            </div>

            <div class="box-body">
                @if (session('password_success'))
                    <div class="alert-ok"><i class="fa-solid fa-circle-check"></i> {{ session('password_success') }}</div>
                @endif

                <form method="POST" action="{{ route('my-account.password') }}" id="pwForm" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="f-row one">
                        <div class="field">
                            <label for="current_password">Current password</label>
                            <div class="input has-eye">
                                <i class="fa-solid fa-key lead"></i>
                                <input type="password" id="current_password" name="current_password"
                                       class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                       autocomplete="current-password" required>
                                <button type="button" class="eye" data-eye="current_password" aria-label="Show or hide password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="f-row one">
                        <div class="field">
                            <label for="password">New password</label>
                            <div class="input has-eye">
                                <i class="fa-solid fa-lock lead"></i>
                                <input type="password" id="password" name="password"
                                       class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                       autocomplete="new-password" required>
                                <button type="button" class="eye" data-eye="password" aria-label="Show or hide password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="err"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="f-row one">
                        <div class="field">
                            <label for="password_confirmation">Confirm new password</label>
                            <div class="input has-eye">
                                <i class="fa-solid fa-lock lead"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       autocomplete="new-password" required>
                                <button type="button" class="eye" data-eye="password_confirmation" aria-label="Show or hide password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="meter" id="meter" data-level="0"><span></span><span></span><span></span><span></span></div>
                    <div class="meter-row">
                        <span>Password strength</span>
                        <strong id="meterText">&mdash;</strong>
                    </div>

                    <ul class="rules" id="rules">
                        <li data-rule="length"><i class="fa-solid fa-check"></i> 8+ characters</li>
                        <li data-rule="different"><i class="fa-solid fa-check"></i> Different from current</li>
                        <li data-rule="match"><i class="fa-solid fa-check"></i> Passwords match</li>
                    </ul>

                    <div class="actions">
                        <button type="submit" class="btn" id="pwBtn" disabled>
                            <i class="fa-solid fa-key"></i> Update password
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        // ── Success messages disappear after 2 seconds ──
        document.querySelectorAll('.alert-ok').forEach(function (el) {
            setTimeout(function () {
                el.classList.add('hide');
                setTimeout(function () { el.remove(); }, 400);
            }, 2000);
        });

        // ── Show / hide password ──
        document.querySelectorAll('[data-eye]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.eye);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            });
        });

        // ── Profile form: enable Save only when text fields changed ──
        var info = document.getElementById('infoForm');
        var infoBtn = document.getElementById('infoBtn');
        var original = JSON.parse(info.dataset.original);
        var fields = ['fullname', 'username'];

        function checkInfo() {
            var changed = false;
            fields.forEach(function (id) {
                var el = document.getElementById(id);
                if (el.value.trim() !== (original[id] || '')) changed = true;
            });
            var filled = document.getElementById('fullname').value.trim() !== '' &&
                         document.getElementById('username').value.trim() !== '';
            infoBtn.disabled = !(changed && filled);
        }

        fields.forEach(function (id) {
            document.getElementById(id).addEventListener('input', checkInfo);
        });
        checkInfo();

        // ── Facebook-style profile picture ──
        var avatarBtn = document.getElementById('avatarBtn');
        var camBtn = document.getElementById('camBtn');
        var menu = document.getElementById('avatarMenu');
        var menuChange = document.getElementById('menuChange');
        var menuRemove = document.getElementById('menuRemove');
        var photoInput = document.getElementById('photo');
        var removePhoto = document.getElementById('removePhoto');

        var overlay = document.getElementById('pmOverlay');
        var pmTitle = document.getElementById('pmTitle');
        var pmCircle = document.getElementById('pmCircle');
        var pmText = document.getElementById('pmText');
        var pmSave = document.getElementById('pmSave');
        var pmCancel = document.getElementById('pmCancel');
        var pmClose = document.getElementById('pmClose');
        var initial = avatarBtn.dataset.initial;
        var mode = null; // 'upload' | 'remove'

        function toggleMenu(force) {
            var open = typeof force === 'boolean' ? force : menu.hidden;
            menu.hidden = !open;
        }

        avatarBtn.addEventListener('click', function (e) { e.stopPropagation(); toggleMenu(); });
        camBtn.addEventListener('click', function (e) { e.stopPropagation(); toggleMenu(); });
        document.addEventListener('click', function (e) {
            if (!document.getElementById('avatarWrap').contains(e.target)) toggleMenu(false);
        });

        function openModal() { overlay.hidden = false; }
        function closeModal() {
            overlay.hidden = true;
            // discard any unsaved choice
            photoInput.value = '';
            removePhoto.value = '0';
            mode = null;
        }

        menuChange.addEventListener('click', function () {
            toggleMenu(false);
            photoInput.click();
        });

        photoInput.addEventListener('change', function () {
            var file = photoInput.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('Photo is too large. Maximum size is 2 MB.');
                photoInput.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                pmCircle.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                pmTitle.textContent = 'Update profile picture';
                pmText.textContent = 'This is how your new profile picture will look.';
                pmSave.textContent = 'Save';
                pmSave.className = 'pm-btn primary';
                mode = 'upload';
                removePhoto.value = '0';
                openModal();
            };
            reader.readAsDataURL(file);
        });

        menuRemove.addEventListener('click', function () {
            toggleMenu(false);
            photoInput.value = '';
            pmCircle.innerHTML = avatarBtn.innerHTML; // show the current photo being removed
            pmTitle.textContent = 'Remove profile picture';
            pmText.textContent = 'Are you sure you want to remove your profile picture?';
            pmSave.textContent = 'Remove';
            pmSave.className = 'pm-btn danger';
            mode = 'remove';
            openModal();
        });

        pmSave.addEventListener('click', function () {
            if (mode === 'remove') {
                removePhoto.value = '1';
                photoInput.value = '';
            } else if (mode === 'upload') {
                removePhoto.value = '0';
            } else {
                return;
            }
            pmSave.disabled = true;
            pmSave.textContent = 'Saving…';
            info.requestSubmit();
        });

        pmCancel.addEventListener('click', closeModal);
        pmClose.addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (!overlay.hidden) closeModal();
                toggleMenu(false);
            }
        });

        // ── Password form: strength meter, checklist, enable button ──
        var cur = document.getElementById('current_password');
        var pw = document.getElementById('password');
        var conf = document.getElementById('password_confirmation');
        var pwBtn = document.getElementById('pwBtn');
        var meter = document.getElementById('meter');
        var meterText = document.getElementById('meterText');
        var rules = {
            length: document.querySelector('[data-rule="length"]'),
            different: document.querySelector('[data-rule="different"]'),
            match: document.querySelector('[data-rule="match"]')
        };
        var labels = ['—', 'Weak', 'Fair', 'Good', 'Strong'];

        function setRule(key, ok) { rules[key].classList.toggle('met', !!ok); }

        function checkPassword() {
            var v = pw.value;

            var score = 0;
            if (v.length >= 8) score++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            if (v.length > 0 && v.length < 8) score = Math.min(score, 1);
            if (v.length === 0) score = 0;

            meter.dataset.level = score;
            meterText.textContent = labels[score];

            var okLength = v.length >= 8;
            var okDifferent = v.length > 0 && cur.value !== '' && v !== cur.value;
            var okMatch = v.length > 0 && conf.value === v;

            setRule('length', okLength);
            setRule('different', okDifferent);
            setRule('match', okMatch);

            pwBtn.disabled = !(cur.value !== '' && okLength && okDifferent && okMatch);
        }

        [cur, pw, conf].forEach(function (el) { el.addEventListener('input', checkPassword); });
        checkPassword();
    })();
</script>
@endpush