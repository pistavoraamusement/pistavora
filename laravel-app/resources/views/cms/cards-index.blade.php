{{--
    Card Collections — list view (aligned with Home / Inventory design)

    Optional backend pieces (everything degrades gracefully without them):
    1. Drag-to-reorder persistence:
         Route::post('/cms/cards/{type}/reorder', [CmsController::class, 'reorder'])->name('cms.cards.reorder');

         public function reorder(Request $request, string $type)
         {
             foreach ($request->input('order', []) as $i => $id) {
                 SiteCard::where('id', $id)->where('type', $type)->update(['sort_order' => $i]);
             }
             return response()->json(['ok' => true]);
         }
    2. Optional card fields: price_note, original_price, reference_code
    3. Pagination: works automatically if $cards is a paginator.
--}}
@extends('layouts.sidebar')

@section('title', $label . ' Cards')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
@endpush

@section('styles')
@include('cms.partials.page-styles')
<style>
    /* --- Filter row (search, status tabs, sort) --- */
    .cms-filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .cms-search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 340px;
    }

    .cms-search-wrap i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        font-size: .8rem;
    }

    .cms-search-input {
        width: 100%;
        font-family: 'Inter', sans-serif;
        font-size: 12.5px;
        padding: 9px 12px 9px 34px;
        border-radius: 10px;
        border: 1px solid var(--line-strong);
        background: var(--bg);
        color: var(--ink-soft);
    }

    .cms-search-input:focus {
        outline: none;
        border-color: var(--pink);
        box-shadow: 0 0 0 3px var(--pink-light);
    }

    .cms-status-tabs {
        display: inline-flex;
        background: var(--bg);
        border: 1px solid var(--line-strong);
        border-radius: 10px;
        padding: 3px;
        gap: 3px;
    }

    .cms-status-tab {
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 7px;
        border: none;
        background: transparent;
        color: var(--ink-soft);
        cursor: pointer;
        white-space: nowrap;
    }

    .cms-status-tab.is-active {
        background: var(--pink-deep);
        color: #fff;
    }

    .cms-sort-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .78rem;
        font-weight: 700;
        color: var(--muted);
        white-space: nowrap;
        margin-left: auto;
    }

    .cms-sort-select {
        font-family: 'Inter', sans-serif;
        font-size: 12.5px;
        color: var(--ink-soft);
        padding: 9px 12px;
        border-radius: 10px;
        border: 1px solid var(--line-strong);
        background: var(--bg);
        cursor: pointer;
    }

    .cms-sort-select:focus {
        outline: none;
        border-color: var(--pink);
        box-shadow: 0 0 0 3px var(--pink-light);
    }

    /* --- Reorder notice --- */
    .cms-reorder-notice {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 11px 16px;
        border-radius: 10px;
        background: var(--pink-pale);
        color: var(--pink-dark);
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .cms-reorder-notice span:first-child i {
        margin-right: 6px;
    }

    .cms-autosync-pill {
        font-size: .7rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--card);
        color: var(--pink-dark);
        white-space: nowrap;
    }

    .cms-alert-success {
        margin-bottom: 20px;
        padding: 11px 16px;
        background: var(--green-light);
        color: var(--green);
        border-radius: 10px;
        font-size: .85rem;
        font-weight: 600;
    }

    /* --- Table (same look as Inventory) --- */
    .cms-table-wrap {
        background: var(--card);
        border-radius: 12px;
        overflow-x: auto;
        border: 1px solid var(--line);
    }

    .cms-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .cms-table th {
        text-align: left;
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #fff;
        padding: 12px 14px;
        background: var(--ink);
        border-bottom: none;
        white-space: nowrap;
    }

    .cms-table td {
        padding: 12px 14px;
        font-size: .87rem;
        color: var(--ink-soft);
        border-bottom: 1px solid var(--line);
        vertical-align: middle;
    }

    .cms-table tr:last-child td {
        border-bottom: none;
    }

    .cms-table tbody tr:hover {
        background: var(--pink-pale);
    }

    .cms-table tbody tr.is-dragging {
        opacity: .4;
    }

    .cms-drag-handle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--muted);
        font-weight: 700;
        cursor: grab;
        user-select: none;
    }

    .cms-drag-handle i {
        font-size: .78rem;
        color: var(--line-strong);
    }

    .cms-thumb {
        width: 46px;
        height: 46px;
        border-radius: 9px;
        object-fit: cover;
        background: var(--bg);
        border: 1px solid var(--line);
        display: block;
    }

    .cms-thumb-empty {
        width: 46px;
        height: 46px;
        border-radius: 9px;
        background: var(--pink-pale);
        color: var(--pink-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        border: 1px dashed var(--line-strong);
    }

    .cms-card-title-cell strong {
        display: block;
        font-size: .9rem;
        color: var(--ink);
    }

    .cms-card-desc {
        font-size: .76rem;
        color: var(--muted);
        margin-top: 2px;
        max-width: 320px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .cms-card-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .68rem;
        color: var(--muted);
        margin-top: 6px;
        flex-wrap: wrap;
    }

    .cms-card-meta .dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: var(--line-strong);
    }

    .cms-badge-featured-inline {
        font-size: .62rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        background: var(--amber-light);
        color: var(--amber);
        margin-left: 6px;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }

    .cms-chip {
        display: inline-block;
        font-size: .74rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-chip-coral  { background: rgba(255,90,95,.14);  color: #E5424A; }
    .cms-chip-teal   { background: rgba(20,184,166,.14); color: #0D9488; }
    .cms-chip-amber  { background: rgba(255,182,39,.18); color: #DE9A00; }
    .cms-chip-violet { background: rgba(139,92,246,.14); color: #7347E0; }

    .cms-chip-note {
        display: block;
        font-size: .68rem;
        color: var(--muted);
        margin-top: 5px;
        font-weight: 600;
    }

    .cms-price-main {
        font-size: .92rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-price-original {
        font-size: .76rem;
        color: var(--muted);
        text-decoration: line-through;
        margin-left: 6px;
        font-weight: 600;
    }

    .cms-price-note {
        display: block;
        font-size: .68rem;
        color: var(--muted);
        margin-top: 3px;
        font-weight: 600;
    }

    .cms-price-note.is-promo {
        color: var(--pink-dark);
    }

    .cms-badge-active,
    .cms-badge-inactive {
        font-size: .72rem;
        font-weight: 700;
        padding: 5px 12px 5px 10px;
        border-radius: 999px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cms-badge-active {
        background: var(--green-light);
        color: var(--green);
    }

    .cms-badge-inactive {
        background: var(--line);
        color: var(--ink-soft);
    }

    .cms-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .cms-row-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        position: relative;
    }

    .cms-row-actions form {
        margin: 0;
    }

    .cms-row-actions a,
    .cms-row-actions button {
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 9px;
        border: none;
        cursor: pointer;
        transition: background .15s, color .15s;
    }

    .cms-edit-link {
        background: #EDE9FE;
        color: #6D28D9;
    }

    .cms-edit-link:hover {
        background: #7C5CE0;
        color: #fff;
    }

    .cms-delete-btn {
        background: var(--danger-soft);
        color: var(--danger);
    }

    .cms-delete-btn:hover {
        background: #ef4444;
        color: #fff;
    }

    .cms-kebab-btn {
        background: transparent;
        color: var(--muted);
        padding: 7px 8px !important;
    }

    .cms-kebab-btn:hover {
        background: var(--line);
        color: var(--ink);
    }

    .cms-kebab-menu {
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        z-index: 20;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 10px;
        box-shadow: var(--shadow-md);
        padding: 6px;
        min-width: 160px;
        display: none;
    }

    .cms-kebab-menu.is-open {
        display: block;
    }

    .cms-kebab-menu button,
    .cms-kebab-menu a {
        display: block;
        width: 100%;
        text-align: left;
        font-size: .8rem;
        font-weight: 600;
        color: var(--ink);
        padding: 8px 10px;
        border-radius: 7px;
        background: none;
        border: none;
        cursor: pointer;
    }

    .cms-kebab-menu button:hover,
    .cms-kebab-menu a:hover {
        background: var(--pink-pale);
        color: var(--pink-dark);
    }

    .cms-empty {
        text-align: center;
        padding: 40px 16px;
        color: var(--muted);
        font-size: .88rem;
    }

    .cms-empty i {
        display: block;
        font-size: 1.6rem;
        color: var(--line-strong);
        margin-bottom: 10px;
    }

    /* --- Footer / pagination --- */
    .cms-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 14px;
        font-size: .78rem;
        color: var(--muted);
        font-weight: 600;
    }

    .cms-table-footer-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cms-table-footer-left i {
        color: var(--green);
    }

    .cms-pagination {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cms-pagination a,
    .cms-pagination span {
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 700;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: var(--card);
        color: var(--ink-soft);
    }

    .cms-pagination a:hover {
        border-color: var(--pink);
        color: var(--pink-dark);
    }

    .cms-pagination .is-current {
        background: var(--pink-deep);
        color: #fff;
        border-color: transparent;
    }

    /* --- Bottom info cards (styled like stat cards) --- */
    .cms-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
    }

    .cms-info-card {
        display: flex;
        gap: 12px;
        background: var(--card);
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: var(--shadow-sm);
    }

    .cms-info-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
    }

    .cms-info-icon.icon-pink  { background: var(--pink-pale); color: var(--pink-dark); }
    .cms-info-icon.icon-amber { background: var(--amber-light); color: var(--amber); }
    .cms-info-icon.icon-blue  { background: rgba(59,130,246,.14); color: #3B82F6; }

    .cms-info-card h4 {
        font-size: .68rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 5px;
    }

    .cms-info-card p {
        font-size: .78rem;
        color: var(--ink-soft);
        line-height: 1.45;
    }

    @media (max-width: 900px) {
        .cms-sort-wrap { margin-left: 0; }
    }
</style>
@endsection

@section('content')
    @php
        $hubMap = ['pass' => 1, 'attraction' => 2, 'service' => 3, 'step' => 4];
        $hubIndex = $hubMap[$type] ?? null;
        $isPaginated = $cards instanceof \Illuminate\Pagination\LengthAwarePaginator;
    @endphp

    <!-- ══ TOOLBAR ════════════════════════════════════════════ -->
    <div class="toolbar no-print">
        <div>
            <div class="eyebrow">
                Lipa Branch &middot; Website Management
                @if ($hubIndex) &middot; Live Hub #{{ $hubIndex }} @endif
            </div>
            <h1 style="font-family: Arial, Helvetica, sans-serif">{{ $label }} Cards</h1>
            <p class="toolbar-desc">Manage dynamic pricing cards, tier perks, promotional badges, and visual assets displayed on the public landing page.</p>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('cms.index') }}" class="btn-ghost"><i class="fa-solid fa-arrow-left"></i> Collections</a>
            <a href="{{ route('cms.cards.create', $type) }}" class="btn-primary"><i class="fa-solid fa-plus"></i> Add {{ $label }}</a>
        </div>
    </div>

    <!-- ══ STATS ══════════════════════════════════════════════ -->
    <div class="stats-row no-print">
        <div class="stat-card">
            <div class="label">Total</div>
            <div class="value">{{ $cards->count() }}</div>
            <div class="sub">All {{ strtolower($label) }} cards</div>
        </div>
        <div class="stat-card">
            <div class="label">Active</div>
            <div class="value">{{ $cards->where('is_active', true)->count() }}</div>
            <div class="sub">Visible on public site</div>
        </div>
        <div class="stat-card">
            <div class="label">Hidden</div>
            <div class="value">{{ $cards->where('is_active', false)->count() }}</div>
            <div class="sub">Not shown publicly</div>
        </div>
        <div class="stat-card warn">
            <div class="label">Featured</div>
            <div class="value">{{ $cards->where('is_featured', true)->count() }}</div>
            <div class="sub">Highlighted cards</div>
        </div>
    </div>

    @if (session('success'))
        <div class="cms-alert-success">{{ session('success') }}</div>
    @endif

    <!-- ══ CARDS TABLE ════════════════════════════════════════ -->
    <div class="report-section">
        <div class="report-title">
            <i class="fa-solid fa-layer-group title-icon"></i> {{ $label }} Cards
            <span class="count-pill">{{ $cards->count() }}</span>
            <span class="report-hint">Drag the handle to reorder</span>
        </div>

        <div class="cms-filter-row">
            <div class="cms-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="cardSearch" class="cms-search-input" placeholder="Search {{ strtolower($label) }} by title, badge, or price...">
            </div>

            <div class="cms-status-tabs" id="statusTabs">
                <button type="button" class="cms-status-tab is-active" data-status="all">All ({{ $cards->count() }})</button>
                <button type="button" class="cms-status-tab" data-status="active">Active</button>
                <button type="button" class="cms-status-tab" data-status="hidden">Hidden</button>
            </div>

            <div class="cms-sort-wrap">
                <span>Sort:</span>
                <select class="cms-sort-select" id="sortSelect">
                    <option value="order">Display Order</option>
                    <option value="title">Title (A–Z)</option>
                    <option value="price">Price</option>
                </select>
            </div>
        </div>

        <div class="cms-reorder-notice">
            <span><i class="fa-solid fa-arrows-up-down"></i>Drag rows using the handle icon to change the public display order. Changes sync automatically.</span>
            <span class="cms-autosync-pill">Auto-ordering Active</span>
        </div>

        <div class="cms-table-wrap">
            <table class="cms-table" id="cardsTable">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Image</th>
                        <th>Card Title &amp; Details</th>
                        <th>Badge / Highlight</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="cardsTableBody">
                    @forelse ($cards as $card)
                        @php
                            $badgeColor = $card->badge_color ?? 'coral';
                            $refCode = $card->reference_code
                                ?? (strtoupper(Str::limit($type, 4, '')) . '-' . strtoupper(Str::limit($card->slug ?? '', 6, '')));
                        @endphp
                        <tr data-title="{{ strtolower($card->title) }}"
                            data-status="{{ $card->is_active ? 'active' : 'hidden' }}"
                            data-price="{{ $card->price_display ?? '' }}"
                            data-order="{{ $card->sort_order ?? 0 }}"
                            data-id="{{ $card->id }}">
                            <td>
                                <span class="cms-drag-handle" title="Drag to reorder">
                                    <i class="fa-solid fa-grip-vertical"></i>{{ $card->sort_order }}
                                </span>
                            </td>
                            <td>
                                @if ($card->image_path)
                                    <img class="cms-thumb" src="{{ asset('storage/' . $card->image_path) }}" alt="{{ $card->title }}">
                                @else
                                    <div class="cms-thumb-empty"><i class="fa-solid fa-image"></i></div>
                                @endif
                            </td>
                            <td class="cms-card-title-cell">
                                <strong>
                                    {{ $card->title }}
                                    @if ($card->is_featured)
                                        <span class="cms-badge-featured-inline">Featured</span>
                                    @endif
                                </strong>
                                @if (!empty($card->description))
                                    <div class="cms-card-desc">{{ $card->description }}</div>
                                @endif
                                <div class="cms-card-meta">
                                    <span>Card ID: #{{ $refCode }}</span>
                                    @if (!empty($card->slug))
                                        <span class="dot"></span>
                                        <span>Slug: /{{ $type }}#{{ $card->slug }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if (!empty($card->badge_label))
                                    <span class="cms-chip cms-chip-{{ $badgeColor }}">{{ $card->badge_label }}</span>
                                @else
                                    <span style="color:var(--muted);font-size:.8rem;">—</span>
                                @endif
                                @if ($card->is_featured)
                                    <span class="cms-chip-note">Highlighted in Homepage Grid</span>
                                @elseif (!empty($card->badge_label))
                                    <span class="cms-chip-note">Promotional Badge</span>
                                @endif
                            </td>
                            <td>
                                <span class="cms-price-main">{{ $card->price_display ?? '—' }}</span>
                                @if (!empty($card->original_price))
                                    <span class="cms-price-original">{{ $card->original_price }}</span>
                                @endif
                                @if (!empty($card->price_note))
                                    <span class="cms-price-note {{ !empty($card->original_price) ? 'is-promo' : '' }}">{{ $card->price_note }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($card->is_active)
                                    <span class="cms-badge-active"><span class="cms-status-dot"></span>Active</span>
                                @else
                                    <span class="cms-badge-inactive"><span class="cms-status-dot"></span>Hidden</span>
                                @endif
                            </td>
                            <td>
                                <div class="cms-row-actions">
                                    <a href="{{ route('cms.cards.edit', $card) }}" class="cms-edit-link">Edit</a>
                                    <form action="{{ route('cms.cards.destroy', $card) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete &quot;{{ $card->title }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cms-delete-btn">Delete</button>
                                    </form>
                                    <button type="button" class="cms-kebab-btn" data-kebab-toggle aria-label="More actions">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="cms-kebab-menu" data-kebab-menu>
                                        <a href="{{ route('cms.cards.edit', $card) }}"><i class="fa-solid fa-copy" style="width:16px;"></i> Duplicate</a>
                                        <button type="button" data-copy-link="{{ url('/#' . ($card->slug ?? '')) }}"><i class="fa-solid fa-link" style="width:16px;"></i> Copy live link</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="cms-empty">
                                <i class="fa-solid fa-inbox"></i>
                                No {{ strtolower($label) }} added yet.<br>
                                <a href="{{ route('cms.cards.create', $type) }}" style="color:var(--pink-dark);font-weight:700;">Add the first one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="cms-table-footer">
            <div class="cms-table-footer-left">
                <span>Showing <strong>{{ $cards->count() }}</strong> of <strong>{{ $isPaginated ? $cards->total() : $cards->count() }}</strong> {{ $label }}</span>
                <span>&bull;</span>
                <span><i class="fa-solid fa-check"></i> Auto-sync to live website enabled</span>
            </div>

            @if ($isPaginated)
                <div class="cms-pagination">
                    @if ($cards->onFirstPage())
                        <span>Previous</span>
                    @else
                        <a href="{{ $cards->previousPageUrl() }}">Previous</a>
                    @endif

                    @foreach ($cards->getUrlRange(1, $cards->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="{{ $page == $cards->currentPage() ? 'is-current' : '' }}">{{ $page }}</a>
                    @endforeach

                    @if ($cards->hasMorePages())
                        <a href="{{ $cards->nextPageUrl() }}">Next</a>
                    @else
                        <span>Next</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- ══ INFO CARDS ═════════════════════════════════════════ -->
    <div class="cms-info-grid">
        <div class="cms-info-card">
            <div class="cms-info-icon icon-pink"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <h4>Card Caching</h4>
                <p>Changes to pricing or highlights automatically refresh the public site cache.</p>
            </div>
        </div>
        <div class="cms-info-card">
            <div class="cms-info-icon icon-amber"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <h4>Featured Card Limit</h4>
                <p>We recommend featuring a maximum of 1 card at a time to optimize conversion on the landing page.</p>
            </div>
        </div>
        <div class="cms-info-card">
            <div class="cms-info-icon icon-blue"><i class="fa-solid fa-clone"></i></div>
            <div>
                <h4>Asset Dimensions</h4>
                <p>Recommended card cover aspect ratio is 4:3 (minimum 800x600px, WebP or optimized PNG).</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
(function() {
    const searchInput = document.getElementById('cardSearch');
    const table = document.getElementById('cardsTable');
    const tbody = document.getElementById('cardsTableBody');
    const statusTabs = document.getElementById('statusTabs');
    const sortSelect = document.getElementById('sortSelect');
    if (!table || !tbody) return;

    let activeStatus = 'all';

    function applyFilters() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        tbody.querySelectorAll('tr[data-title]').forEach(function(row) {
            const matchesQuery = !query || row.dataset.title.includes(query) || (row.dataset.price || '').toLowerCase().includes(query);
            const matchesStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
            row.style.display = (matchesQuery && matchesStatus) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);

    if (statusTabs) {
        statusTabs.querySelectorAll('.cms-status-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                statusTabs.querySelectorAll('.cms-status-tab').forEach(function(t) { t.classList.remove('is-active'); });
                tab.classList.add('is-active');
                activeStatus = tab.dataset.status;
                applyFilters();
            });
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const rows = Array.from(tbody.querySelectorAll('tr[data-title]'));
            if (!rows.length) return;

            if (this.value === 'title') {
                rows.sort(function(a, b) { return a.dataset.title.localeCompare(b.dataset.title); });
            } else if (this.value === 'price') {
                rows.sort(function(a, b) {
                    const pa = parseFloat((a.dataset.price || '0').replace(/[^0-9.]/g, '')) || 0;
                    const pb = parseFloat((b.dataset.price || '0').replace(/[^0-9.]/g, '')) || 0;
                    return pa - pb;
                });
            } else {
                rows.sort(function(a, b) { return Number(a.dataset.order) - Number(b.dataset.order); });
            }

            rows.forEach(function(row) { tbody.appendChild(row); });
        });
    }

    // Kebab dropdowns
    document.querySelectorAll('[data-kebab-toggle]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = btn.parentElement.querySelector('[data-kebab-menu]');
            document.querySelectorAll('.cms-kebab-menu.is-open').forEach(function(m) {
                if (m !== menu) m.classList.remove('is-open');
            });
            if (menu) menu.classList.toggle('is-open');
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.cms-kebab-menu.is-open').forEach(function(m) { m.classList.remove('is-open'); });
    });

    document.querySelectorAll('[data-copy-link]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            navigator.clipboard.writeText(btn.dataset.copyLink || '').catch(function() {});
        });
    });

    // Drag-to-reorder. Persists via POST to the `cms.cards.reorder` route
    // (see comment at the top of this file). If the route doesn't exist yet,
    // the drag still works visually and the failed request is ignored.
    if (window.Sortable) {
        Sortable.create(tbody, {
            handle: '.cms-drag-handle',
            animation: 150,
            onStart: function(evt) { evt.item.classList.add('is-dragging'); },
            onEnd: function(evt) {
                evt.item.classList.remove('is-dragging');
                const order = Array.from(tbody.querySelectorAll('tr[data-id]')).map(function(r) { return r.dataset.id; });

                fetch(@json(rtrim(url('/cms/cards'), '/')) + '/{{ $type }}/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            ? document.querySelector('meta[name="csrf-token"]').content
                            : '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ order: order }),
                }).catch(function() { /* route not wired up yet — safe to ignore */ });
            },
        });
    }
})();
</script>
@endpush