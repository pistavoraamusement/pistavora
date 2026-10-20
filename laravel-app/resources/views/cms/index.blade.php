@extends('layouts.sidebar')

@section('title', 'Website Content Management System')

@section('styles')
<style>
    /* ---------- Top bar ---------- */
    .cms-topbar {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--line);
    }

    .cms-topbar h1 {
        font-family: var(--font-display);
        font-size: 1.7rem;
        font-weight: 800;
        color: var(--ink);
        letter-spacing: -.02em;
    }

    .cms-crumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .82rem;
        color: var(--muted);
        white-space: nowrap;
    }

    .cms-crumbs span.sep {
        color: var(--line-strong);
    }

    .cms-crumbs strong {
        color: var(--ink);
        font-weight: 700;
    }

    .cms-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: .76rem;
        font-weight: 700;
        color: var(--green);
        background: var(--green-light);
        padding: 5px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-status i {
        font-size: .5rem;
    }

    .cms-search {
        flex: 1;
        min-width: 180px;
        max-width: 320px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 14px;
        border-radius: 10px;
        border: 1.5px solid var(--line);
        background: var(--card);
        color: var(--muted);
        font-size: .82rem;
    }

    .cms-search:focus-within {
        border-color: var(--pink-deep);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .14);
    }

    .cms-search input {
        border: 0;
        outline: 0;
        background: transparent;
        width: 100%;
        font-size: .82rem;
        color: var(--ink);
    }

    .cms-topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .cms-btn-ghost,
    .cms-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: .84rem;
        font-weight: 700;
        font-family: inherit;
        white-space: nowrap;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .cms-btn-ghost {
        background: var(--card);
        border-color: var(--line);
        color: var(--ink);
    }

    .cms-btn-ghost:hover {
        background: var(--pink-pale);
        border-color: var(--pink-deep);
        color: var(--pink-dark);
    }

    .cms-btn-primary {
        background: var(--pink-deep);
        color: #fff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, .28);
    }

    .cms-btn-primary:hover {
        background: var(--pink-dark);
    }

    /* ---------- Page heading ---------- */
    .cms-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .cms-intro {
        color: var(--ink-soft);
        font-size: .9rem;
        margin-top: 6px;
        max-width: 560px;
        line-height: 1.55;
    }

    .cms-head-meta {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        font-size: .78rem;
        font-weight: 600;
        color: var(--ink-soft);
        white-space: nowrap;
        background: var(--card);
        border: 1px solid var(--line);
        padding: 5px 12px;
        border-radius: 999px;
    }

    .cms-head-meta b {
        color: var(--green);
    }

    /* ---------- Shell ---------- */
    .cms-shell {
        display: grid;
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 28px;
        margin-top: 22px;
        align-items: start;
    }

    @media (max-width: 1000px) {
        .cms-shell {
            grid-template-columns: 1fr;
        }
    }

    /* ---------- Site content panel ---------- */
    .cms-panel {
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 20px;
    }

    .cms-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--line);
    }

    .cms-panel-header h2 {
        font-family: var(--font-display);
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--ink);
    }

    .cms-panel-header p {
        font-size: .8rem;
        color: var(--ink-soft);
        margin-top: 3px;
    }

    .cms-chip {
        font-size: .72rem;
        font-weight: 700;
        color: var(--pink-dark);
        background: var(--pink-pale);
        padding: 4px 11px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-content-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px 0;
    }

    .cms-content-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 13px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: var(--card);
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .cms-content-row:hover,
    .cms-content-row.is-active {
        border-color: var(--pink-light);
        background: var(--pink-pale);
    }

    .cms-content-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        background: var(--pink-light);
        color: var(--pink-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .92rem;
    }

    /* Accent colors rotate per row (same palette as landing) */
    .cms-content-row:nth-child(4n+2) .cms-content-icon { background: #FFE4E9; color: #BE123C; }
    .cms-content-row:nth-child(4n+3) .cms-content-icon { background: #CCFBF1; color: #0F766E; }
    .cms-content-row:nth-child(4n+4) .cms-content-icon { background: #FEF3C7; color: #B45309; }

    .cms-content-body {
        flex: 1;
        min-width: 0;
    }

    .cms-content-body h3 {
        font-size: .9rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-content-body p {
        font-size: .76rem;
        color: var(--ink-soft);
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cms-content-edit {
        flex-shrink: 0;
        font-size: .78rem;
        font-weight: 700;
        color: var(--ink);
        padding: 6px 14px;
        border-radius: 8px;
        border: 1.5px solid var(--line);
        background: var(--card);
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .cms-content-edit:hover {
        background: var(--pink-deep);
        border-color: var(--pink-deep);
        color: #fff;
    }

    .cms-panel-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 14px;
        border-top: 1px solid var(--line);
        font-size: .78rem;
        color: var(--ink-soft);
    }

    .cms-panel-foot span.ok {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--green);
        font-weight: 700;
    }

    /* ---------- Collections ---------- */
    .cms-collections-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .cms-section-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--ink-soft);
    }

    .cms-collections-head h2 {
        font-family: var(--font-display);
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--ink);
        margin-top: 4px;
    }

    .cms-collections-hint {
        font-size: .78rem;
        color: var(--ink-soft);
    }

    .cms-collection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(238px, 1fr));
        gap: 18px;
        align-items: stretch;
    }

    .cms-collection-card {
        position: relative;
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
    }

    .cms-collection-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
        border-color: var(--pink-light);
    }

    .cms-collection-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .cms-collection-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        background: var(--pink-light);
        color: var(--pink-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    /* Accent colors rotate per card */
    .cms-collection-card:nth-child(4n+2) .cms-collection-icon { background: #FFE4E9; color: #BE123C; }
    .cms-collection-card:nth-child(4n+3) .cms-collection-icon { background: #CCFBF1; color: #0F766E; }
    .cms-collection-card:nth-child(4n+4) .cms-collection-icon { background: #FEF3C7; color: #B45309; }

    .cms-collection-meta {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cms-collection-count {
        font-size: .72rem;
        font-weight: 700;
        color: var(--pink-dark);
        background: var(--pink-pale);
        padding: 4px 11px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-collection-delete {
        border: 0;
        background: transparent;
        color: var(--muted);
        font-size: .8rem;
        padding: 6px 8px;
        border-radius: 8px;
        cursor: pointer;
        line-height: 1;
        transition: background .15s ease, color .15s ease;
    }

    .cms-collection-delete:hover {
        background: var(--danger-soft);
        color: var(--danger);
    }

    .cms-collection-card h3 {
        font-family: var(--font-display);
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--ink);
    }

    .cms-collection-card p {
        font-size: .83rem;
        color: var(--ink-soft);
        line-height: 1.6;
        flex: 1;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--line);
    }

    .cms-collection-card a.btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 11px 16px;
        border-radius: 10px;
        background: var(--pink-deep);
        color: #fff;
        font-weight: 700;
        font-size: .86rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, .25);
        transition: background .15s ease;
    }

    .cms-collection-card a.btn:hover {
        background: var(--pink-dark);
    }

    /* ---------- Add collection tile ---------- */
    .cms-collection-add {
        border: 2px dashed var(--line-strong);
        border-radius: 16px;
        background: transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 6px;
        padding: 24px;
        min-height: 190px;
        color: var(--ink-soft);
        cursor: pointer;
        font-family: inherit;
        transition: border-color .15s ease, background .15s ease;
    }

    .cms-collection-add:hover {
        border-color: var(--pink-deep);
        background: var(--pink-pale);
    }

    .cms-collection-add .plus {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: var(--pink-light);
        color: var(--pink-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        transition: background .15s ease, color .15s ease;
    }

    .cms-collection-add:hover .plus {
        background: var(--pink-deep);
        color: #fff;
    }

    .cms-collection-add h3 {
        font-size: .94rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-collection-add p {
        font-size: .78rem;
        line-height: 1.45;
        max-width: 180px;
    }

    /* ---------- Flash + errors ---------- */
    .cms-flash,
    .cms-error {
        margin-top: 18px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: .86rem;
        font-weight: 600;
    }

    .cms-flash {
        background: var(--green-light);
        color: var(--green);
        border: 1px solid #99F6E4;
    }

    .cms-error {
        background: var(--danger-soft);
        color: var(--danger);
        border: 1px solid #FECDD3;
    }

    .cms-error ul {
        margin: 0;
        padding-left: 18px;
    }

    /* ---------- Modal ---------- */
    .cms-modal {
        position: fixed;
        inset: 0;
        z-index: 120;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(3px);
    }

    .cms-modal.is-open {
        display: flex;
    }

    .cms-modal-box {
        width: 100%;
        max-width: 480px;
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-md);
        padding: 26px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .cms-modal-box h2 {
        font-family: var(--font-display);
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--ink);
    }

    .cms-modal-box .sub {
        font-size: .84rem;
        color: var(--ink-soft);
        margin-top: 4px;
        margin-bottom: 18px;
        line-height: 1.5;
    }

    .cms-field {
        margin-bottom: 14px;
    }

    .cms-field label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 6px;
    }

    .cms-field input,
    .cms-field textarea,
    .cms-field select {
        width: 100%;
        padding: 11px 12px;
        border-radius: 10px;
        border: 1.5px solid var(--line);
        background: var(--card);
        color: var(--ink);
        font-size: .86rem;
        font-family: inherit;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .cms-field input:hover,
    .cms-field textarea:hover,
    .cms-field select:hover {
        border-color: var(--line-strong);
    }

    .cms-field input:focus,
    .cms-field textarea:focus,
    .cms-field select:focus {
        border-color: var(--pink-deep);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .14);
    }

    .cms-field textarea {
        resize: vertical;
        min-height: 74px;
    }

    .cms-field .hint {
        font-size: .74rem;
        color: var(--ink-soft);
        margin-top: 5px;
    }

    .cms-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .cms-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
    }

    @media (max-width: 520px) {
        .cms-field-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .cms-topbar-actions {
            margin-left: 0;
            width: 100%;
        }

        .cms-topbar-actions > * {
            flex: 1;
        }

        .cms-modal-actions > * {
            flex: 1;
        }
    }
</style>
@endsection

@section('content')
    <div class="cms-topbar">
        <div>
            <h1>Website Content Management System</h1>
        </div>
        <div class="cms-topbar-actions">
            <a href="{{ url('/') }}" target="_blank" class="cms-btn-ghost">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View public site
            </a>
            <button type="button" class="cms-btn-primary">
                <i class="fa-solid fa-cloud-arrow-up"></i> Publish sync
            </button>
        </div>
    </div>

    <div class="cms-head">
        
        <div class="cms-head-meta">
            <span>Auto-save: <b>Active</b></span>
        </div>
    </div>

    @if (session('success'))
        <div class="cms-flash">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="cms-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cms-shell">
        <div class="cms-panel">
            <div class="cms-panel-header">
                <div>
                    <h2>Site content</h2>
                    <p>Text blocks and copy sections</p>
                </div>
                <span class="cms-chip">4 blocks</span>
            </div>

            <div class="cms-content-list">
                <div class="cms-content-row is-active">
                    <div class="cms-content-icon"><i class="fa-solid fa-house"></i></div>
                    <div class="cms-content-body">
                        <h3>Hero Section</h3>
                        <p>Headline, description, price teaser, background video</p>
                    </div>
                    <a href="{{ route('cms.section.edit', 'hero') }}" class="cms-content-edit">Edit</a>
                </div>

                <div class="cms-content-row">
                    <div class="cms-content-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="cms-content-body">
                        <h3>Contact Info</h3>
                        <p>Address, hours, zone list</p>
                    </div>
                    <a href="{{ route('cms.section.edit', 'contact') }}" class="cms-content-edit">Edit</a>
                </div>

                <div class="cms-content-row">
                    <div class="cms-content-icon"><i class="fa-solid fa-shoe-prints"></i></div>
                    <div class="cms-content-body">
                        <h3>Footer</h3>
                        <p>Tagline shown at the bottom of the page</p>
                    </div>
                    <a href="{{ route('cms.section.edit', 'footer') }}" class="cms-content-edit">Edit</a>
                </div>
            </div>

            <div class="cms-panel-foot">
                <span>All 4 sections in sync</span>
                <span class="ok"><i class="fa-solid fa-circle-check"></i> Healthy</span>
            </div>
        </div>

        <div>
            <div class="cms-collections-head">
                <div>
                    <div class="cms-section-label">Collections</div>
                    <h2>Card collections</h2>
                </div>
            </div>

            <div class="cms-collection-grid">
                @foreach ($collections as $collection)
                    <div class="cms-collection-card">
                        <div class="cms-collection-top">
                            <div class="cms-collection-icon"><i class="{{ $collection->icon }}"></i></div>
                            <div class="cms-collection-meta">
                                <span class="cms-collection-count">
                                    {{ $cardCounts[$collection->slug] ?? 0 }} {{ $collection->unit_label }}
                                </span>
                                @unless ($collection->is_system)
                                    <form method="POST"
                                          action="{{ route('cms.collections.destroy', $collection->slug) }}"
                                          onsubmit="return confirm('Delete the {{ $collection->title }} collection?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cms-collection-delete" title="Delete collection" aria-label="Delete collection">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                        <h3>{{ $collection->title }}</h3>
                        <p>{{ $collection->description }}</p>
                        <a href="{{ route('cms.cards', $collection->slug) }}" class="btn">Manage</a>
                    </div>
                @endforeach

                {{-- Booking & Pricing: a settings page, not a card collection, so it is a fixed tile
                     (no delete button) that links to its own editor. --}}
                <div class="cms-collection-card">
                    <div class="cms-collection-top">
                        <div class="cms-collection-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                        <div class="cms-collection-meta">
                            <span class="cms-collection-count">Settings</span>
                        </div>
                    </div>
                    <h3>Booking &amp; Pricing</h3>
                    <p>Package tier prices and add-on fees for each service, applied to the live booking page.</p>
                    <a href="{{ route('cms.booking.pricing') }}" class="btn">Manage</a>
                </div>

                <button type="button" class="cms-collection-add" data-open-collection-modal>
                    <div class="plus"><i class="fa-solid fa-plus"></i></div>
                    <h3>Add collection</h3>
                    <p>Create an interactive showcase section</p>
                </button>
            </div>
        </div>
    </div>

    {{-- Add collection modal --}}
    <div class="cms-modal {{ $errors->any() && old('title') ? 'is-open' : '' }}" id="collectionModal">
        <div class="cms-modal-box">
            <h2>New collection</h2>
            <p class="sub">Groups a set of cards you can manage together, like Day Passes or Attractions.</p>

            <form method="POST" action="{{ route('cms.collections.store') }}">
                @csrf

                <div class="cms-field">
                    <label for="collection-title">Name</label>
                    <input type="text" id="collection-title" name="title" maxlength="60" required
                           value="{{ old('title') }}" placeholder="Seasonal Events">
                </div>

                <div class="cms-field">
                    <label for="collection-description">Description</label>
                    <textarea id="collection-description" name="description" maxlength="200" required
                              placeholder="What this group of cards covers.">{{ old('description') }}</textarea>
                    <div class="hint">Shown on this dashboard only, not on the public site.</div>
                </div>

                <div class="cms-field-row">
                    <div class="cms-field">
                        <label for="collection-unit">Count label</label>
                        <input type="text" id="collection-unit" name="unit_label" maxlength="20" required
                               value="{{ old('unit_label', 'items') }}" placeholder="events">
                        <div class="hint">Used in the badge: "4 events".</div>
                    </div>

                    <div class="cms-field">
                        <label for="collection-icon">Icon</label>
                        <select id="collection-icon" name="icon">
                            <option value="fa-solid fa-layer-group">Layers</option>
                            <option value="fa-solid fa-calendar-days">Event</option>
                            <option value="fa-solid fa-gift">Gift</option>
                            <option value="fa-solid fa-utensils">Food</option>
                            <option value="fa-solid fa-camera">Photo</option>
                            <option value="fa-solid fa-star">Star</option>
                            <option value="fa-solid fa-map">Map</option>
                        </select>
                    </div>
                </div>

                <div class="cms-modal-actions">
                    <button type="button" class="cms-btn-ghost" data-close-collection-modal>Cancel</button>
                    <button type="submit" class="cms-btn-primary">Create collection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('collectionModal');
            if (!modal) return;

            function openModal() {
                modal.classList.add('is-open');
                var first = modal.querySelector('input[name="title"]');
                if (first) first.focus();
            }

            function closeModal() {
                modal.classList.remove('is-open');
            }

            document.querySelectorAll('[data-open-collection-modal]').forEach(function (el) {
                el.addEventListener('click', openModal);
            });

            document.querySelectorAll('[data-close-collection-modal]').forEach(function (el) {
                el.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeModal();
            });
        })();
    </script>
@endsection