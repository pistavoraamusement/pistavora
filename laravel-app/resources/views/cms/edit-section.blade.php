@extends('layouts.sidebar')

@section('title', ucfirst($section) . ' Section')

@section('styles')
<style>
    .cms-page { display: flex; flex-direction: column; gap: 18px; }

    /* ---------- breadcrumb ---------- */
    .cms-crumb { display: flex; align-items: center; gap: 8px; font-size: .78rem; color: var(--muted); }
    .cms-crumb a { color: var(--muted); text-decoration: none; }
    .cms-crumb a:hover { color: var(--pink-dark); }
    .cms-crumb i { font-size: .6rem; color: var(--line-strong); }
    .cms-crumb .is-current { color: var(--ink-soft); font-weight: 600; }

    /* ---------- header ---------- */
    .cms-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .cms-top h1 { font-size: 1.5rem; font-weight: 800; color: var(--ink); margin: 2px 0 6px; }
    .cms-top p { font-size: .86rem; color: var(--muted); margin: 0; max-width: 60ch; }
    .cms-top-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

    .cms-btn-ghost, .cms-btn-solid {
        display: inline-flex; align-items: center; gap: 7px; font-family: inherit;
        font-size: .82rem; font-weight: 700; padding: 9px 16px; border-radius: 9px;
        cursor: pointer; white-space: nowrap;
    }
    .cms-btn-ghost { background: var(--card); color: var(--ink-soft); border: 1.5px solid var(--line-strong); }
    .cms-btn-ghost:hover { border-color: var(--pink); color: var(--pink-dark); }
    .cms-btn-solid { background: var(--pink); color: #fff; border: none; }
    .cms-btn-solid:hover { background: var(--pink-dark); }

    /* ---------- alerts ---------- */
    .cms-alert { padding: 12px 16px; border-radius: 10px; font-size: .85rem; font-weight: 600; }
    .cms-alert--success { background: var(--green-light); color: var(--green); }
    .cms-alert--error { background: var(--pink-light); color: var(--pink-deep); }

    /* ---------- config panel ---------- */
    .cms-panel { background: var(--card); border-radius: 16px; border: 1px solid var(--line); overflow: hidden; }
    .cms-panel-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        flex-wrap: wrap; padding: 16px 24px; border-bottom: 1px solid var(--line);
    }
    .cms-panel-title { display: inline-flex; align-items: center; gap: 8px; font-size: .74rem; font-weight: 800; letter-spacing: .05em; color: var(--ink); }
    .cms-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--pink); flex-shrink: 0; box-shadow: 0 0 0 3px rgba(255,92,133,.18); }
    .cms-panel-hint { font-size: .76rem; color: var(--muted); }
    .cms-panel form { padding: 24px; }

    .cms-fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px 18px; }
    @media (max-width: 640px) { .cms-fields-grid { grid-template-columns: 1fr; } }

    .cms-field { margin: 0; }
    .cms-field-span { grid-column: 1 / -1; }
    .cms-field label { display: block; font-size: .8rem; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
    .cms-field-hint { font-size: .74rem; color: var(--muted); margin: 6px 0 0; }

    .cms-input, .cms-textarea {
        width: 100%; font-family: inherit; font-size: .9rem; color: var(--ink);
        padding: 10px 12px; border-radius: 9px; border: 1.5px solid var(--line-strong);
        background: var(--bg); outline: none; transition: border-color .15s ease, box-shadow .15s ease;
    }
    .cms-textarea { min-height: 90px; resize: vertical; line-height: 1.5; }
    .cms-input:focus, .cms-textarea:focus { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(255,92,133,.12); }

    /* ---------- rich text editor ---------- */
    .richtext-wrap { border: 1.5px solid var(--line-strong); border-radius: 9px; overflow: hidden; background: var(--bg); }
    .richtext-wrap:focus-within { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(255,92,133,.12); }
    .richtext-toolbar { display: flex; align-items: center; gap: 3px; padding: 6px 7px; border-bottom: 1.5px solid var(--line-strong); background: var(--card); flex-wrap: wrap; }
    .richtext-btn { width: 28px; height: 28px; border: none; background: transparent; border-radius: 6px; color: var(--ink-soft); font-size: .8rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .richtext-btn:hover { background: var(--line); color: var(--ink); }
    .richtext-btn.italic i { font-style: italic; }
    .richtext-sep { width: 1px; height: 18px; background: var(--line-strong); margin: 0 3px; }
    .richtext-size, .richtext-font { font-family: inherit; font-size: .76rem; font-weight: 600; color: var(--ink); padding: 4px 7px; border-radius: 6px; border: 1px solid var(--line-strong); background: var(--bg); cursor: pointer; }
    .richtext-font { max-width: 112px; }
    .richtext-color-label { width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--line-strong); overflow: hidden; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0; }
    .richtext-color { width: 36px; height: 36px; border: none; padding: 0; margin: 0; cursor: pointer; background: none; }
    .richtext-editor { min-height: 110px; padding: 12px 13px; font-size: .95rem; line-height: 1.5; color: var(--ink); outline: none; }

    .cms-file-input { width: 100%; font-family: inherit; font-size: .82rem; padding: 10px 12px; border-radius: 9px; border: 1.5px dashed var(--line-strong); background: var(--bg); color: var(--ink-soft); }

    /* ---------- panel footer ---------- */
    .cms-panel-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--line); }
    .cms-lastsaved { font-size: .78rem; color: var(--muted); display: inline-flex; align-items: center; gap: 6px; }
    .cms-foot-actions { display: flex; align-items: center; gap: 10px; }

    /* ---------- live preview section ---------- */
    .cms-preview-section { display: flex; flex-direction: column; gap: 14px; }
    .cms-preview-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .cms-live-badge { display: inline-flex; align-items: center; gap: 10px; font-size: .78rem; font-weight: 800; color: var(--pink-dark); background: var(--pink-light); padding: 6px 14px; border-radius: 999px; }
    .cms-dot--pink { background: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,.35); }
    .cms-viewport-label { font-weight: 700; color: var(--ink); margin-left: 4px; padding-left: 10px; border-left: 1px solid rgba(0,0,0,.12); }
    .cms-viewport-toggle { display: inline-flex; background: var(--card); border: 1.5px solid var(--line-strong); border-radius: 10px; padding: 3px; gap: 3px; }
    .cms-viewport-toggle button { display: inline-flex; align-items: center; gap: 6px; font-family: inherit; font-size: .76rem; font-weight: 700; padding: 7px 12px; border-radius: 7px; border: none; background: transparent; color: var(--ink-soft); cursor: pointer; }
    .cms-viewport-toggle button.is-active { background: var(--bg); color: var(--pink-dark); }

    /* The preview is the REAL landing page (landing.css) inside an iframe */
    .cms-preview-frame {
        width: 100%; margin: 0 auto; border: 1px solid var(--line);
        border-radius: 16px; overflow: hidden; background: #fff;
        transition: max-width .2s ease;
    }
    .cms-preview-frame[data-viewport="mobile"] { max-width: 390px; }
    .cms-preview-iframe { display: block; width: 100%; height: 680px; border: 0; }
    .cms-preview-frame[data-viewport="mobile"] .cms-preview-iframe { height: 760px; }

    .cms-preview-footnote { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; font-size: .76rem; color: var(--muted); }

    @media (max-width: 900px) { .cms-top { flex-direction: column; } }
</style>
@endsection

@section('content')
    <div class="cms-page">

        <div class="cms-crumb">
            <span>{{ config('app.name', 'Pistavora') }} Lipa Branch</span>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route('cms.index') }}">Website Management</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span class="is-current">{{ ucfirst($section) }}</span>
        </div>

        <div class="cms-top">
            <div>
                <h1>{{ ucfirst($section) }} CMS</h1>
                <p>Edit the content shown in this part of the site. Changes appear in the live preview below.</p>
            </div>
            <div class="cms-top-actions">
                <button type="button" class="cms-btn-ghost" id="scrollToPreviewTop">
                    <i class="fa-regular fa-eye"></i> Live Preview
                </button>
                <button type="submit" form="cmsSectionForm" class="cms-btn-solid">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="cms-alert cms-alert--success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="cms-alert cms-alert--error">There are errors in the form — please check the fields.</div>
        @endif

        <div class="cms-panel">
            <div class="cms-panel-head">
                <span class="cms-panel-title"><span class="cms-dot"></span> {{ strtoupper($section) }} CONTENT CONFIGURATION</span>
                <span class="cms-panel-hint">Changes appear in the live preview below</span>
            </div>

            <form action="{{ route('cms.section.update', $section) }}" method="POST" enctype="multipart/form-data" id="cmsSectionForm">
                @csrf
                @method('PUT')

                <div class="cms-fields-grid">
                    @foreach ($fields as $key => $meta)
                        @php $type = $meta['type'] ?? 'text'; @endphp

                        <div class="cms-field {{ in_array($type, ['richtext', 'textarea']) ? 'cms-field-span' : '' }}">
                            <label for="{{ $key }}">{{ $meta['label'] }}</label>

                            @if ($type === 'video')
                                <input type="file" name="{{ $key }}" id="{{ $key }}" accept="video/mp4,video/webm,video/quicktime" class="cms-file-input" data-video-input="{{ $key }}">
                                <p class="cms-field-hint">MP4, WebM, or MOV. Max 50MB. Leave blank to keep the current video.</p>
                            @elseif ($type === 'richtext')
                                <div class="richtext-wrap" data-richtext-wrap>
                                    <div class="richtext-toolbar">
                                        <button type="button" class="richtext-btn" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
                                        <button type="button" class="richtext-btn italic" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
                                        <button type="button" class="richtext-btn" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
                                        <div class="richtext-sep"></div>
                                        <select class="richtext-font" data-fontfamily title="Font style">
                                            <option value="">Font</option>
                                            <option value="Arial, sans-serif">Arial</option>
                                            <option value="Georgia, serif">Georgia</option>
                                            <option value="'Times New Roman', serif">Times New Roman</option>
                                            <option value="'Courier New', monospace">Courier New</option>
                                            <option value="Verdana, sans-serif">Verdana</option>
                                            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                                        </select>
                                        <select class="richtext-size" data-fontsize title="Font size">
                                            <option value="">Size</option>
                                            <option value="14px">Small</option>
                                            <option value="18px" selected>Normal</option>
                                            <option value="24px">Large</option>
                                            <option value="32px">X-Large</option>
                                            <option value="44px">Huge</option>
                                        </select>
                                        <div class="richtext-sep"></div>
                                        <label class="richtext-color-label" title="Font color">
                                            <input type="color" class="richtext-color" data-fontcolor value="#1a1a1a">
                                        </label>
                                    </div>
                                    <div class="richtext-editor" contenteditable="true" data-richtext-editor id="{{ $key }}_editor">{!! old($key, $values[$key] ?? '') !!}</div>
                                </div>
                                <input type="hidden" name="{{ $key }}" id="{{ $key }}" value="{{ old($key, $values[$key] ?? '') }}" data-richtext-input>
                                <p class="cms-field-hint">Select text, then use the toolbar for bold, italic, underline, font, size, or color.</p>
                            @elseif ($type === 'textarea')
                                <textarea name="{{ $key }}" id="{{ $key }}" class="cms-textarea" data-plain-field="{{ $key }}">{{ old($key, $values[$key] ?? '') }}</textarea>
                            @else
                                <input type="text" name="{{ $key }}" id="{{ $key }}" class="cms-input" value="{{ old($key, $values[$key] ?? '') }}" data-plain-field="{{ $key }}">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="cms-panel-foot">
                    @isset($lastSaved)
                        <span class="cms-lastsaved"><i class="fa-regular fa-clock"></i> Last saved: {{ $lastSaved }}</span>
                    @else
                        <span></span>
                    @endisset
                    <div class="cms-foot-actions">
                        <button type="button" class="cms-btn-ghost" id="scrollToPreviewBottom">
                            <i class="fa-solid fa-arrow-down"></i> Scroll to Preview
                        </button>
                        <button type="submit" class="cms-btn-solid">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="cms-preview-section" id="livePreviewSection">
            <div class="cms-preview-topbar">
                <span class="cms-live-badge">
                    <span class="cms-dot cms-dot--pink"></span> LIVE PREVIEW
                    <span class="cms-viewport-label">Customer Facing Viewport</span>
                </span>
                <div class="cms-viewport-toggle">
                    <button type="button" class="is-active" data-viewport="desktop"><i class="fa-solid fa-desktop"></i> Desktop</button>
                    <button type="button" data-viewport="mobile"><i class="fa-solid fa-mobile-screen-button"></i> Mobile</button>
                </div>
            </div>

            <div class="cms-preview-frame" id="previewCanvas" data-viewport="desktop">
                <iframe id="previewFrame" class="cms-preview-iframe" title="Live preview"></iframe>
            </div>

            <div class="cms-preview-footnote">
                <span>✨ Real-time preview using the actual landing page styles.</span>
                <span>Target: Public Landing Page — {{ ucfirst($section) }} Section</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@php
    $previewValues = collect($fields)->map(function ($meta, $k) use ($values) {
        $v = old($k, $values[$k] ?? '');
        return is_scalar($v) ? (string) $v : '';
    })->all();
    $previewTypes = collect($fields)->map(fn ($meta) => $meta['type'] ?? 'text')->all();
@endphp
<script>
(function () {
    const frame  = document.getElementById('previewFrame');
    const canvas = document.getElementById('previewCanvas');

    const SECTION  = @json($section);
    const VALUES   = @json($previewValues);
    const TYPES    = @json($previewTypes);
    const CSS_URL  = @json(asset('css/landing.css'));
    const LOGO     = @json(asset('images/pistavora-logo-horizontal.svg'));
    const STORAGE  = @json(asset('storage') . '/');

    // Same copy as resources/views/landing.blade.php (used when a field is empty)
    const D = {
        title: 'Rides, Roller Skating<br>and a DinoLakbay.',
        description: 'Pistavora has three zones for the whole family. Reserve your slot and pay online, or walk in.',
        price_teaser: '₱149',
        video_path: @json(asset('videos/landing.mp4')),
        tag: 'Plan Your Day',
        location_tag: 'Lima Technology Center, Lipa City / Malvar',
        location: 'Lima Technology Center, Lipa City / Malvar, Batangas',
        hours_weekday: '11:00 AM to 9:00 PM',
        hours_weekend: '10:00 AM to 9:00 PM',
        zones: 'PiyestaRides, Skataya, DinoLakbay',
        tagline: 'Rides, roller skating and a dinosaur playground. A day out for the whole family.'
    };
    D.title_split = 'Make it a full day out';
    D.description_split = 'Pistavora sits inside the Lima Technology Center complex, so a visit pairs easily with a longer family day.';

    const state = Object.assign({}, VALUES);

    /* ---------- helpers ---------- */
    const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const chips = s => String(s).split(/\s*[·,|•]\s*/).filter(Boolean).map(z => `<span>${esc(z)}</span>`).join('');
    const videoSrc = v => /^(https?:|blob:|\/)/.test(v) ? v : STORAGE + v;

    function get(k) {
        const v = state[k];
        if (v !== undefined && v !== null && String(v).trim() !== '') return v;
        if (SECTION === 'split' && k === 'title') return D.title_split;
        if (SECTION === 'split' && k === 'description') return D.description_split;
        return D[k] !== undefined ? D[k] : '';
    }

    function mode(k, force) {
        return force || (TYPES[k] === 'richtext' ? 'html' : 'text');
    }

    // element bound to a CMS field
    function bound(tag, k, cls, force, extra) {
        const m = mode(k, force);
        const v = get(k);
        const inner = m === 'html' ? v : (m === 'chips' ? chips(v) : esc(v));
        return `<${tag}${cls ? ` class="${cls}"` : ''}${extra ? ' ' + extra : ''} data-k="${k}" data-t="${m}">${inner}</${tag}>`;
    }

    /* ---------- section templates (real landing markup) ---------- */
    const zonesLine = `
        <p class="wp-zones-line">
            <span><i style="background:var(--wp-coral)"></i>PiyestaRides</span>
            <span><i style="background:var(--wp-teal)"></i>Skataya</span>
            <span><i style="background:var(--wp-amber)"></i>DinoLakbay</span>
        </p>`;

    function heroHTML() {
        const tick = ['Open Daily', 'Including holidays', 'Weekends 10:00 AM to 9:00 PM', 'Weekdays 11:00 AM to 9:00 PM', 'PiyestaRides', 'Skataya', 'DinoLakbay', 'Walk-ins welcome'];
        const group = (hidden) => `<div class="wp-tick-group"${hidden ? ' aria-hidden="true"' : ''}>` +
            tick.map((t, i) => `<span class="${i === 0 ? 'open' : ''}">${t}</span><i></i>`).join('') + '</div>';

        return `
        <header>
            <nav>
                <div class="logo"><img src="${LOGO}" alt="Pistavora" style="height:46px;vertical-align:middle;margin:0;mix-blend-mode:multiply;"></div>
                <div class="nav-links"><a>Day Passes</a><a>Attractions</a><a>Services</a><a>Visit Us</a></div>
                <div style="display:flex;gap:10px;"><a class="btn btn-outline btn-sm">Sign In</a><a class="btn btn-primary btn-sm">Book Now</a></div>
            </nav>
        </header>
        <section class="hero">
            <video class="hero-bg-video" data-k="video_path" data-t="video" autoplay muted loop playsinline src="${esc(videoSrc(get('video_path')))}"></video>
            <div class="hero-video-overlay"></div>
            <div class="wrap">
                <div class="hero-grid">
                    <div class="hero-copy">
                        <div class="wp-open"><b><i></i>Open Daily</b><span>Holidays too</span></div>
                        ${bound('h1', 'title', '')}
                        <p>${bound('span', 'description', '')} Passes start at
                            ${bound('a', 'price_teaser', '', null, 'style="font-weight:700;color:var(--amber);text-decoration:underline;text-underline-offset:3px;"')}.</p>
                        <div class="hero-cta">
                            <a class="btn btn-primary">Reserve a Slot</a>
                            <a class="btn btn-outline-light">See Attractions</a>
                        </div>
                        ${zonesLine}
                    </div>
                </div>
            </div>
            <div class="wp-ticker"><div class="wp-tick-track">${group(false)}${group(true)}${group(true)}${group(true)}</div></div>
            <div class="hero-video-meta">
                <span class="hero-video-badge"><span class="dot"></span>At Pistavora</span>
            </div>
        </section>`;
    }

    function splitHTML() {
        return `
        <section>
            <div class="wrap">
                <div class="section-head">
                    ${bound('span', 'tag', 'ticket-label violet')}
                    ${bound('h2', 'title', '')}
                    ${bound('p', 'description', '')}
                    <div class="wp-chips" style="justify-content:center;margin-top:18px;">${bound('span', 'location_tag', '')}</div>
                </div>
            </div>
        </section>`;
    }

    function contactHTML() {
        const mapSrc = 'https://maps.google.com/maps?q=' + encodeURIComponent(get('location')) + '&output=embed';
        return `
        <section id="contact">
            <div class="wrap">
                <div class="section-head">
                    <h2>Plan your visit</h2>
                    <p>Group bookings and school trips are set up in advance with our reservations team.</p>
                </div>
                <div class="wp-visit-grid">
                    <div class="wp-tile wp-status open">
                        <div>
                            <h3>Right now</h3>
                            <div class="big"><i></i><span>Open now</span></div>
                            <p class="sub">Open until 9:00 PM today.</p>
                        </div>
                        <a>Book a slot</a>
                    </div>
                    <div class="wp-tile wp-hours">
                        <h3>Hours</h3>
                        <dl>
                            <div class="row today"><dt>Weekdays</dt><dd style="margin:0">${bound('span', 'hours_weekday', '')}</dd></div>
                            <div class="row"><dt>Weekends</dt><dd style="margin:0">${bound('span', 'hours_weekend', '')}</dd></div>
                        </dl>
                        <p class="note">Open every day, holidays included.</p>
                    </div>
                    <div class="wp-tile wp-map">
                        <iframe title="Map" loading="lazy" src="${mapSrc}"></iframe>
                        <div class="addr">${bound('span', 'location', '')}<a>Get directions</a></div>
                    </div>
                    <div class="wp-tile wp-good">
                        <dl style="margin:0;display:grid;gap:18px;">
                            <div><dt>Zones</dt><dd>${bound('div', 'zones', 'wp-chips', 'chips')}</dd></div>
                            <div><dt>Tickets</dt><dd>Reserve online or walk in, depending on capacity.</dd></div>
                            <div><dt>Pay with</dt><dd><div class="wp-chips"><span>Cash</span><span>Cards</span><span>GCash</span><span>Maya</span><span>Klook</span><span>StarDeals</span></div></dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </section>`;
    }

    function footerHTML() {
        return `
        <footer class="wp-foot">
            <div class="wp-foot-grid">
                <div class="wp-foot-brand">
                    <p class="name">Pistavora</p>
                    ${bound('p', 'tagline', '')}
                    <a class="btn btn-primary btn-sm">Book Now</a>
                </div>
                <div>
                    <h4>Explore</h4>
                    <ul><li><a>Day Passes</a></li><li><a>Attractions</a></li><li><a>Services</a></li><li><a>How it works</a></li><li><a>Visit Us</a></li></ul>
                </div>
                <div>
                    <h4>Zones</h4>
                    <ul>
                        <li><i style="background:var(--wp-coral)"></i>PiyestaRides</li>
                        <li><i style="background:var(--wp-teal)"></i>Skataya</li>
                        <li><i style="background:var(--wp-amber)"></i>DinoLakbay</li>
                        <li><a>Sign In</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Find us</h4>
                    <ul>
                        <li>Lima Technology Center, Lipa City / Malvar, Batangas</li>
                        <li>Weekdays 11 AM to 9 PM<br>Weekends 10 AM to 9 PM</li>
                        <li><div class="chips"><span>Cash</span><span>Cards</span><span>GCash</span><span>Maya</span></div></li>
                    </ul>
                </div>
            </div>
            <div class="wp-foot-bar">© ${new Date().getFullYear()} Pistavora. All rights reserved.</div>
        </footer>`;
    }

    function genericHTML() {
        const rows = Object.keys(TYPES).map(k =>
            `<li><b>${esc(k)}</b> ${bound('span', k, '')}</li>`).join('');
        return `<section><div class="wrap"><ul style="list-style:none;display:grid;gap:12px;">${rows}</ul></div></section>`;
    }

    const BUILDERS = { hero: heroHTML, split: splitHTML, contact: contactHTML, footer: footerHTML };

    function render() {
        const body = (BUILDERS[SECTION] || genericHTML)();
        frame.srcdoc = `<!DOCTYPE html><html lang="en"><head>
            <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="${CSS_URL}">
            <style>body{pointer-events:none;} header{position:static;}</style>
            </head><body>${body}</body></html>`;
    }

    /* ---------- live updates (no iframe reload, so the video keeps playing) ---------- */
    function push(key) {
        const doc = frame.contentDocument;
        if (!doc) return;
        doc.querySelectorAll('[data-k="' + key + '"]').forEach(function (el) {
            const v = get(key);
            const t = el.dataset.t;
            if (t === 'video')      el.src = videoSrc(v);
            else if (t === 'html')  el.innerHTML = v;
            else if (t === 'chips') el.innerHTML = chips(v);
            else                    el.textContent = v;
        });
    }

    function setField(key, value) {
        state[key] = value;
        push(key);
    }

    /* ---------- rich text editor ---------- */
    document.querySelectorAll('[data-richtext-wrap]').forEach(function (wrap) {
        const editor = wrap.querySelector('[data-richtext-editor]');
        const hiddenInput = wrap.parentElement.querySelector('[data-richtext-input]');
        const sizeSelect = wrap.querySelector('[data-fontsize]');
        const fontSelect = wrap.querySelector('[data-fontfamily]');
        const colorInput = wrap.querySelector('[data-fontcolor]');

        function sync() {
            if (hiddenInput) {
                hiddenInput.value = editor.innerHTML;
                setField(hiddenInput.id, editor.innerHTML);
            }
        }

        wrap.querySelectorAll('[data-cmd]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                editor.focus();
                document.execCommand(btn.dataset.cmd, false, null);
                sync();
            });
        });

        if (fontSelect) {
            fontSelect.addEventListener('change', function () {
                if (!this.value) return;
                editor.focus();
                document.execCommand('fontName', false, this.value);
                this.value = '';
                sync();
            });
        }

        if (sizeSelect) {
            sizeSelect.addEventListener('change', function () {
                if (!this.value) return;
                editor.focus();
                // execCommand fontSize only accepts 1-7: use marker size 7,
                // then swap <font size="7"> for a span with the real px size.
                document.execCommand('fontSize', false, '7');
                editor.querySelectorAll('font[size="7"]').forEach(function (f) {
                    const span = document.createElement('span');
                    span.style.fontSize = sizeSelect.value;
                    span.innerHTML = f.innerHTML;
                    f.replaceWith(span);
                });
                this.value = '';
                sync();
            });
        }

        if (colorInput) {
            colorInput.addEventListener('input', function () {
                editor.focus();
                document.execCommand('foreColor', false, this.value);
                sync();
            });
        }

        editor.addEventListener('input', sync);
        editor.addEventListener('blur', sync);
    });

    /* ---------- plain text / textarea ---------- */
    document.querySelectorAll('[data-plain-field]').forEach(function (field) {
        field.addEventListener('input', function () { setField(field.id, field.value); });
    });

    /* ---------- video upload ---------- */
    document.querySelectorAll('[data-video-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                setField(input.dataset.videoInput, URL.createObjectURL(this.files[0]));
            }
        });
    });

    /* ---------- make sure rich text is saved on submit ---------- */
    const form = document.getElementById('cmsSectionForm');
    if (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('[data-richtext-wrap]').forEach(function (wrap) {
                const editor = wrap.querySelector('[data-richtext-editor]');
                const hiddenInput = wrap.parentElement.querySelector('[data-richtext-input]');
                if (hiddenInput) hiddenInput.value = editor.innerHTML;
            });
        });
    }

    /* ---------- desktop / mobile toggle (iframe width = real media queries) ---------- */
    document.querySelectorAll('.cms-viewport-toggle button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cms-viewport-toggle button').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
            });
            if (canvas) canvas.dataset.viewport = btn.dataset.viewport;
        });
    });

    /* ---------- scroll to preview ---------- */
    ['scrollToPreviewTop', 'scrollToPreviewBottom'].forEach(function (id) {
        const btn = document.getElementById(id);
        const target = document.getElementById('livePreviewSection');
        if (btn && target) {
            btn.addEventListener('click', function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    });

    render();
})();
</script>
@endpush