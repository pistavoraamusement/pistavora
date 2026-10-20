@extends('layouts.sidebar')

@section('title', 'Booking & Pricing')

@section('styles')
<style>
    :root { --surface-2: #F8FAFC; --ring: 0 0 0 3px rgba(79,70,229,.14); }

    /* ---------- header ---------- */
    .bp-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; padding:4px 0 18px; }
    .bp-head h1 { font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.02em; }
    .bp-head p { margin:0; font-size:.82rem; color:var(--ink-soft); }
    .bp-stats { margin-top:6px; font-size:.75rem; color:var(--muted); }
    .bp-stats strong { color:var(--ink); }
    .bp-stats .is-custom strong { color:var(--pink-deep); }
    .bp-actions { display:flex; align-items:center; gap:14px; }

    .cms-dirty-flag { display:inline-flex; align-items:center; gap:6px; font-size:.72rem; font-weight:700; color:var(--pink-dark); opacity:0; transition:opacity .18s; pointer-events:none; white-space:nowrap; }
    .cms-dirty-flag.is-visible { opacity:1; }
    .cms-dirty-dot { width:6px; height:6px; border-radius:50%; background:var(--pink-deep); animation:cms-pulse 1.6s ease-in-out infinite; }
    @keyframes cms-pulse { 0%,100%{opacity:1} 50%{opacity:.35} }

    .cms-btn-solid { display:inline-flex; align-items:center; gap:8px; font-family:inherit; font-size:.82rem; font-weight:700; padding:10px 20px; border-radius:10px; border:none; cursor:pointer; color:#fff; background:var(--pink-deep); transition:background .15s, opacity .15s; }
    .cms-btn-solid:hover { background:var(--pink-dark); }
    .cms-btn-solid:disabled { opacity:.55; cursor:default; }
    .cms-btn-solid .fa-spinner { animation:cms-spin .7s linear infinite; }
    @keyframes cms-spin { to { transform:rotate(360deg); } }

    .cms-alert { padding:11px 16px; border-radius:10px; font-size:.82rem; font-weight:600; margin-bottom:14px; }
    .cms-alert--success { background:var(--green-light); color:var(--green); }
    .cms-alert--error { background:var(--danger-soft); color:var(--danger); }

    /* ---------- tabs (underline) ---------- */
    .booking-service-tabs { display:flex; gap:28px; border-bottom:1px solid var(--line); overflow-x:auto; }
    .booking-service-tab { display:inline-flex; align-items:center; gap:8px; padding:10px 2px 12px; background:none; border:none; border-bottom:2px solid transparent; margin-bottom:-1px; font-family:inherit; font-size:.86rem; font-weight:700; color:var(--muted); cursor:pointer; white-space:nowrap; transition:color .15s, border-color .15s; }
    .booking-service-tab:hover { color:var(--ink); }
    .booking-service-tab.is-active { color:var(--ink); border-bottom-color:var(--pink-deep); }
    .booking-service-tab .svc-tab-badge { font-size:.62rem; font-weight:800; background:var(--pink-light); color:var(--pink-deep); border-radius:999px; padding:2px 8px; }
    .booking-service-panel[hidden] { display:none; }

    /* ---------- layout ---------- */
    .booking-layout { display:grid; grid-template-columns:minmax(0,1fr) 260px; gap:20px; align-items:start; margin-top:20px; }
    .booking-aside { position:sticky; top:16px; }
    @media (max-width:1024px){ .booking-layout{grid-template-columns:1fr} .booking-aside{position:static} }

    .cms-panel { background:var(--card); border:1px solid var(--line); border-radius:14px; }
    .cms-panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--line); }
    .cms-panel-title { font-size:.8rem; font-weight:800; color:var(--ink); }
    .cms-panel-hint { font-size:.72rem; color:var(--muted); }

    /* ---------- category + package rows ---------- */
    .booking-category-label { margin:0; padding:18px 20px 8px; font-size:.66rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); }

    .booking-package-card { display:flex; flex-wrap:wrap; align-items:center; gap:12px 20px; padding:14px 20px; border-top:1px solid var(--line); }
    .booking-package-info { flex:1; min-width:200px; }
    .booking-package-info h3 { margin:0 0 2px; font-size:.88rem; font-weight:700; color:var(--ink); }
    .booking-package-info p { margin:0; font-size:.74rem; color:var(--muted); line-height:1.4; }
    .booking-package-tools { display:flex; align-items:center; gap:8px; }

    .booking-toggle-btn { font-family:inherit; font-size:.72rem; font-weight:700; color:var(--ink-soft); background:none; border:none; padding:6px 8px; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
    .booking-toggle-btn:hover { background:var(--surface-2); color:var(--ink); }
    .booking-toggle-btn i { font-size:.6rem; transition:transform .18s; }
    .booking-package-card.is-expanded .booking-toggle-btn i { transform:rotate(180deg); }

    .booking-reset-btn { font-family:inherit; font-size:.7rem; font-weight:700; color:var(--pink-deep); background:var(--pink-pale); border:none; border-radius:8px; padding:6px 10px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; position:relative; }
    .booking-reset-btn:hover { background:var(--pink-deep); color:#fff; }
    .booking-reset-btn:disabled { opacity:.6; cursor:default; }

    /* tiers */
    .booking-tier-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(104px,1fr)); gap:10px; flex-basis:100%; }
    .booking-package-card[data-category="solo"] .booking-tier-grid { flex-basis:auto; }
    .booking-package-card[data-category="solo"].is-expanded .booking-tier-grid { flex-basis:100%; }
    .booking-package-card[data-category="solo"]:not(.is-expanded) .booking-tier-field:not([data-pax="1"]) { display:none; }
    .booking-package-card[data-category="solo"]:not(.is-expanded) .booking-tier-grid { grid-template-columns:130px; }

    .booking-tier-field label { display:block; font-size:.66rem; font-weight:700; color:var(--muted); margin-bottom:4px; }
    .booking-tier-field[data-pax="1"] label { color:var(--pink-dark); }
    .booking-solo-note { display:block; font-size:.62rem; color:var(--muted); margin-top:3px; }
    .booking-package-card.is-expanded .booking-solo-note { display:none; }

    .booking-price-input-wrap { position:relative; display:flex; align-items:center; }
    .booking-price-input-wrap span { position:absolute; left:10px; font-size:.74rem; color:var(--muted); font-weight:700; pointer-events:none; }
    .booking-price-input { width:100%; font-family:inherit; font-size:.84rem; font-weight:700; color:var(--ink); padding:8px 8px 8px 24px; border-radius:8px; border:1px solid var(--line-strong); background:var(--card); outline:none; transition:border-color .15s, box-shadow .15s; }
    .booking-price-input:focus { border-color:var(--pink); box-shadow:var(--ring); }
    .booking-price-input.is-overridden { border-color:var(--pink); background:var(--pink-pale); }
    .booking-price-input.is-auto-filled { background:var(--green-light); border-color:var(--green); }
    .booking-price-input.is-dirty { border-color:var(--amber); }
    .booking-overridden-tag { display:block; font-size:.6rem; font-weight:800; color:var(--pink-dark); text-transform:uppercase; letter-spacing:.04em; margin-top:2px; }

    /* ---------- add-ons ---------- */
    .booking-addon-row { padding:12px 20px; border-top:1px solid var(--line); }
    .booking-addon-row:first-child { border-top:none; }
    .booking-addon-top { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px; }
    .booking-addon-name { font-size:.8rem; font-weight:700; color:var(--ink); }
    .booking-aside-empty { font-size:.75rem; color:var(--muted); text-align:center; padding:20px; margin:0; }

    .cms-confirm-pop { position:absolute; top:calc(100% + 6px); right:0; z-index:30; background:var(--card,#fff); border:1px solid var(--line-strong); border-radius:12px; box-shadow:0 10px 28px -8px rgba(15,23,42,.35); padding:10px; width:180px; font-size:.7rem; color:var(--ink-soft); text-align:left; font-weight:400; }
    .cms-confirm-pop p { margin:0 0 8px; font-weight:600; line-height:1.35; }
    .cms-confirm-pop-actions { display:flex; gap:6px; }
    .cms-confirm-pop-actions button { flex:1; font-family:inherit; font-size:.68rem; font-weight:700; border:none; border-radius:7px; padding:6px 0; cursor:pointer; }
    .cms-confirm-yes { background:var(--pink-deep); color:#fff; }
    .cms-confirm-no { background:var(--surface-2); color:var(--ink-soft); }

    .cms-panel-foot { display:flex; justify-content:flex-end; margin-top:20px; padding-top:16px; border-top:1px solid var(--line); }
</style>
@endsection

@section('content')
    @php
        $categoryLabels = ['solo' => 'Solo (per guest)', 'bundle' => 'Bundle', 'packages' => 'Packages'];

        $totalPackages = 0; $totalAddons = 0; $totalOverridesCount = 0;
        $overridesPerService = [];
        foreach ($packages as $svcCode => $svcPackages) {
            $totalPackages += count($svcPackages);
            $pkgOverrideCount = collect($overrides['packages'][$svcCode] ?? [])->sum(fn ($tiers) => count($tiers));
            $overridesPerService[$svcCode] = $pkgOverrideCount + count($overrides['addons'][$svcCode] ?? []);
            $totalOverridesCount += $overridesPerService[$svcCode];
        }
        foreach ($addons as $svcAddons) { $totalAddons += count($svcAddons); }
    @endphp

    <div class="bp-head">
        <div>
            <h1>Booking & Pricing</h1>
            <p>Edit package prices and add-on fees. Changes go live on the booking page right away.</p>
            <div class="bp-stats">
                <strong>{{ $totalPackages }}</strong> packages · <strong>{{ $totalAddons }}</strong> add-ons ·
                <span class="is-custom"><strong>{{ $totalOverridesCount }}</strong> custom {{ Str::plural('price', $totalOverridesCount) }}</span>
            </div>
        </div>
        <div class="bp-actions">
            <span class="cms-dirty-flag" id="cmsDirtyFlag"><span class="cms-dirty-dot"></span> Unsaved changes</span>
            <button type="submit" form="bookingPricingForm" class="cms-btn-solid" id="cmsSaveTop">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="cms-alert cms-alert--success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="cms-alert cms-alert--error">There are errors in the form — please check the fields.</div>
    @endif

    <div class="booking-service-tabs" id="bookingServiceTabs" role="tablist">
        @foreach ($services as $svcCode => $svc)
            <button type="button" class="booking-service-tab {{ $loop->first ? 'is-active' : '' }}"
                data-service-tab="{{ $svcCode }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                {{ $svc['name'] }}
                @if (($overridesPerService[$svcCode] ?? 0) > 0)
                    <span class="svc-tab-badge">{{ $overridesPerService[$svcCode] }} custom</span>
                @endif
            </button>
        @endforeach
    </div>

    <form action="{{ route('cms.booking.pricing.update') }}" method="POST" id="bookingPricingForm">
        @csrf
        @method('PUT')

        @foreach ($services as $svcCode => $svc)
            <div class="booking-service-panel" data-service-panel="{{ $svcCode }}" role="tabpanel" {{ $loop->first ? '' : 'hidden' }}>
                <div class="booking-layout">

                    {{-- MAIN: Packages --}}
                    <div class="cms-panel">
                        <div class="cms-panel-head">
                            <span class="cms-panel-title">Packages & Tiers</span>
                            <span class="cms-panel-hint">Solo: edit 1 PAX, the rest auto-fill</span>
                        </div>

                        @php
                            $servicePackages = $packages[$svcCode] ?? [];
                            $categoriesInService = collect($servicePackages)->pluck('category')->unique()->values()->all();
                        @endphp

                        @foreach ($categoriesInService as $cat)
                            <p class="booking-category-label">{{ $categoryLabels[$cat] ?? ucfirst($cat) }}</p>

                            @foreach ($servicePackages as $pkgCode => $package)
                                @continue($package['category'] !== $cat)
                                @php $overriddenTiers = $overrides['packages'][$svcCode][$pkgCode] ?? []; @endphp

                                <div class="booking-package-card" data-category="{{ $package['category'] }}">
                                    <div class="booking-package-info">
                                        <h3>{{ $package['name'] }}</h3>
                                        <p>{{ $package['desc'] }}</p>
                                    </div>

                                    <div class="booking-package-tools">
                                        @if (count($overriddenTiers) > 0)
                                            <button type="button" class="booking-reset-btn" data-reset-package
                                                data-url="{{ route('cms.booking.pricing.reset-package', [$svcCode, $pkgCode]) }}"
                                                data-confirm-label="{{ $package['name'] }}">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </button>
                                        @endif
                                        @if ($package['category'] === 'solo')
                                            <button type="button" class="booking-toggle-btn" data-toggle-tiers>
                                                All tiers <i class="fa-solid fa-chevron-down"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="booking-tier-grid">
                                        @foreach ($package['tiers'] as $pax => $price)
                                            @php $isOverridden = isset($overriddenTiers[$pax]); @endphp
                                            <div class="booking-tier-field" data-pax="{{ $pax }}">
                                                <label for="tier_{{ $svcCode }}_{{ $pkgCode }}_{{ $pax }}">{{ $pax }} PAX</label>
                                                <div class="booking-price-input-wrap">
                                                    <span>₱</span>
                                                    <input type="number" step="0.01" min="0"
                                                        name="tiers[{{ $svcCode }}][{{ $pkgCode }}][{{ $pax }}]"
                                                        id="tier_{{ $svcCode }}_{{ $pkgCode }}_{{ $pax }}"
                                                        value="{{ old('tiers.'.$svcCode.'.'.$pkgCode.'.'.$pax, $price) }}"
                                                        data-original="{{ $price }}"
                                                        class="booking-price-input {{ $isOverridden ? 'is-overridden' : '' }}">
                                                </div>
                                                @if ($isOverridden)
                                                    <span class="booking-overridden-tag">Custom</span>
                                                @endif
                                                @if ($package['category'] === 'solo' && (string) $pax === '1')
                                                    <span class="booking-solo-note">Others auto-fill</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    {{-- ASIDE: Add-ons --}}
                    <div class="booking-aside">
                        @php $serviceAddons = $addons[$svcCode] ?? []; @endphp
                        <div class="cms-panel">
                            <div class="cms-panel-head">
                                <span class="cms-panel-title">Add-ons</span>
                                <span class="cms-panel-hint">{{ count($serviceAddons) }} {{ Str::plural('item', count($serviceAddons)) }}</span>
                            </div>

                            @forelse ($serviceAddons as $addonCode => $addon)
                                @php $isAddonOverridden = isset($overrides['addons'][$svcCode][$addonCode]); @endphp
                                <div class="booking-addon-row">
                                    <div class="booking-addon-top">
                                        <span class="booking-addon-name">{{ $addon['name'] }}</span>
                                        @if ($isAddonOverridden)
                                            <button type="button" class="booking-reset-btn" data-reset-addon
                                                data-url="{{ route('cms.booking.pricing.reset-addon', [$svcCode, $addonCode]) }}"
                                                data-confirm-label="{{ $addon['name'] }}">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="booking-price-input-wrap">
                                        <span>₱</span>
                                        <input type="number" step="0.01" min="0"
                                            name="addons[{{ $svcCode }}][{{ $addonCode }}]"
                                            value="{{ old('addons.'.$svcCode.'.'.$addonCode, $addon['price']) }}"
                                            data-original="{{ $addon['price'] }}"
                                            class="booking-price-input {{ $isAddonOverridden ? 'is-overridden' : '' }}">
                                    </div>
                                </div>
                            @empty
                                <p class="booking-aside-empty">No add-ons for this service yet.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        @endforeach

        <div class="cms-panel-foot">
            <button type="submit" class="cms-btn-solid" id="cmsSaveBottom">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // Service tabs
    document.addEventListener('click', function (e) {
        var tab = e.target.closest('#bookingServiceTabs [data-service-tab]');
        if (!tab) return;
        document.querySelectorAll('#bookingServiceTabs [data-service-tab]').forEach(function (t) {
            var active = t === tab;
            t.classList.toggle('is-active', active);
            t.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        var svc = tab.dataset.serviceTab;
        document.querySelectorAll('[data-service-panel]').forEach(function (p) {
            p.hidden = p.dataset.servicePanel !== svc;
        });
    });

    // "All tiers" toggle for solo packages
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-toggle-tiers]');
        if (!btn) return;
        btn.closest('.booking-package-card').classList.toggle('is-expanded');
    });

    // Dirty flag
    var dirtyFlag = document.getElementById('cmsDirtyFlag');
    var isDirty = false;
    function markFormDirty() {
        if (isDirty) return;
        isDirty = true;
        if (dirtyFlag) dirtyFlag.classList.add('is-visible');
    }
    window.addEventListener('beforeunload', function (e) {
        if (!isDirty) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // Solo auto-scale (1 PAX × pax) + dirty highlight
    document.addEventListener('input', function (e) {
        var input = e.target;
        if (!input.classList || !input.classList.contains('booking-price-input')) return;

        var original = input.dataset.original;
        if (original !== undefined) {
            input.classList.toggle('is-dirty', String(parseFloat(original)) !== String(parseFloat(input.value || 0)));
        }
        markFormDirty();

        var field = input.closest('.booking-tier-field');
        var card = input.closest('.booking-package-card[data-category="solo"]');
        if (!field || !card) return;

        if (field.dataset.pax === '1') {
            var perGuest = parseFloat(input.value);
            if (isNaN(perGuest) || perGuest < 0) return;
            card.querySelectorAll('.booking-tier-field').forEach(function (f) {
                var pax = parseInt(f.dataset.pax, 10);
                if (!pax || pax === 1) return;
                var scaled = f.querySelector('.booking-price-input');
                if (!scaled) return;
                scaled.value = (perGuest * pax).toFixed(2);
                scaled.classList.add('is-auto-filled');
            });
        } else {
            input.classList.remove('is-auto-filled');
        }
    });

    // Save feedback
    var form = document.getElementById('bookingPricingForm');
    if (form) {
        form.addEventListener('submit', function () {
            isDirty = false;
            ['cmsSaveTop', 'cmsSaveBottom'].forEach(function (id) {
                var btn = document.getElementById(id);
                if (!btn) return;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner"></i> Saving…';
            });
        });
    }

    // Reset confirm popover
    function closeAnyOpenPopover() {
        var open = document.querySelector('.cms-confirm-pop');
        if (open) open.remove();
    }
    document.addEventListener('click', function (e) {
        var resetBtn = e.target.closest('[data-reset-package], [data-reset-addon]');
        if (!resetBtn) {
            if (!e.target.closest('.cms-confirm-pop')) closeAnyOpenPopover();
            return;
        }
        if (resetBtn.disabled) return;
        var already = resetBtn.querySelector(':scope > .cms-confirm-pop');
        closeAnyOpenPopover();
        if (already) return;

        var label = resetBtn.dataset.confirmLabel || 'this item';
        var pop = document.createElement('div');
        pop.className = 'cms-confirm-pop';
        pop.innerHTML =
            '<p>Revert "' + label.replace(/</g, '&lt;') + '" to its default price(s)?</p>' +
            '<div class="cms-confirm-pop-actions">' +
                '<button type="button" class="cms-confirm-no">Cancel</button>' +
                '<button type="button" class="cms-confirm-yes">Reset</button>' +
            '</div>';
        resetBtn.appendChild(pop);

        pop.querySelector('.cms-confirm-no').addEventListener('click', function (ev) { ev.stopPropagation(); pop.remove(); });
        pop.querySelector('.cms-confirm-yes').addEventListener('click', function (ev) {
            ev.stopPropagation();
            resetBtn.disabled = true;
            submitReset(resetBtn.dataset.url);
        });
    });

    function submitReset(url) {
        var f = document.createElement('form');
        f.method = 'POST';
        f.action = url;
        f.style.display = 'none';
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        var meta = document.querySelector('meta[name="csrf-token"]');
        csrf.value = meta ? meta.content : '{{ csrf_token() }}';
        f.appendChild(csrf);
        document.body.appendChild(f);
        f.submit();
    }
})();
</script>
@endpush