@extends('layouts.sidebar')

@section('title', ($card ? 'Edit' : 'Add') . ' ' . $label)

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
@endpush

@section('styles')
@include('cms.partials.page-styles')
<style>
    .cms-form-shell {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 860px) {
        .cms-form-shell {
            grid-template-columns: 1fr;
        }

        .cms-form-side {
            position: static;
        }
    }

    .cms-form-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
        min-width: 0;
    }

    .cms-form-side {
        position: sticky;
        top: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Section panels use the same look as Inventory's .report-section */
    .cms-form-section {
        background: var(--card);
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--line);
    }

    .cms-form-section h2 {
        font-family: 'Source Serif 4', serif;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
        color: var(--ink);
        padding-bottom: 12px;
        margin-bottom: 16px;
        border-bottom: 2px solid var(--pink-light);
    }

    .cms-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 640px) {
        .cms-field-row {
            grid-template-columns: 1fr;
        }
    }

    .cms-field {
        margin-bottom: 18px;
    }

    .cms-field:last-child {
        margin-bottom: 0;
    }

    .cms-field label {
        display: block;
        font-size: .82rem;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 6px;
    }

    .cms-field input[type="text"],
    .cms-field input[type="number"],
    .cms-field select,
    .cms-field textarea {
        width: 100%;
        font-family: 'Inter', sans-serif;
        font-size: .85rem;
        padding: 10px 12px;
        border-radius: 9px;
        border: 1px solid var(--line-strong);
        background: var(--bg);
        color: var(--ink);
        box-sizing: border-box;
    }

    .cms-field textarea {
        min-height: 90px;
        resize: vertical;
    }

    .cms-field input:focus,
    .cms-field select:focus,
    .cms-field textarea:focus {
        outline: none;
        border-color: var(--pink);
        box-shadow: 0 0 0 3px var(--pink-light);
    }

    .cms-field-hint {
        font-size: .74rem;
        color: var(--muted);
        margin-top: 6px;
    }

    .cms-field-error {
        font-size: .74rem;
        color: var(--danger);
        margin-top: 6px;
        font-weight: 600;
    }

    .cms-alert-error {
        margin-bottom: 20px;
        padding: 11px 16px;
        background: var(--danger-soft);
        color: var(--danger);
        border-radius: 10px;
        font-size: .85rem;
        font-weight: 600;
    }

    .cms-image-current {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .cms-image-current img {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--line);
    }

    .cms-image-current span {
        font-size: .78rem;
        color: var(--ink-soft);
    }

    .cms-file-input {
        width: 100%;
        font-family: 'Inter', sans-serif;
        font-size: .84rem;
        padding: 10px 12px;
        border-radius: 9px;
        border: 1px dashed var(--line-strong);
        background: var(--bg);
        color: var(--ink-soft);
    }

    .cms-toggle-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cms-toggle {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: .86rem;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer;
    }

    .cms-toggle input {
        width: 18px;
        height: 18px;
        accent-color: var(--pink-deep);
        cursor: pointer;
    }

    .cms-form-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .cms-form-actions .btn-primary,
    .cms-form-actions .btn-ghost {
        width: 100%;
    }

    /* --- Icon field: pick from library or upload a custom image --- */
    .icon-source-tabs {
        display: inline-flex;
        background: var(--bg);
        border: 1px solid var(--line-strong);
        border-radius: 10px;
        padding: 3px;
        gap: 3px;
        margin-bottom: 12px;
    }

    .icon-source-tab {
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 7px;
        border: none;
        background: transparent;
        color: var(--ink-soft);
        cursor: pointer;
    }

    .icon-source-tab.is-active {
        background: var(--pink-deep);
        color: #fff;
    }

    .icon-source-panel[hidden] {
        display: none;
    }

    .icon-picker-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 10px;
        border: 1px solid var(--line-strong);
        background: var(--bg);
        color: var(--ink);
        font-family: 'Inter', sans-serif;
        font-size: .86rem;
        font-weight: 600;
        cursor: pointer;
    }

    .icon-picker-trigger:hover {
        border-color: var(--pink);
    }

    .icon-picker-preview {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--pink-light);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
        overflow: hidden;
    }

    .icon-picker-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .icon-picker-trigger-text {
        flex: 1;
        text-align: left;
    }

    .icon-picker-popover {
        position: relative;
        margin-top: 8px;
        border: 1px solid var(--line-strong);
        border-radius: 10px;
        background: var(--card);
        box-shadow: var(--shadow-sm);
        padding: 10px;
    }

    .icon-picker-popover[hidden] {
        display: none;
    }

    .icon-picker-search {
        width: 100%;
        font-family: 'Inter', sans-serif;
        font-size: .82rem;
        padding: 8px 11px;
        border-radius: 8px;
        border: 1px solid var(--line-strong);
        background: var(--bg);
        color: var(--ink);
        margin-bottom: 10px;
        box-sizing: border-box;
    }

    .icon-picker-search:focus {
        outline: none;
        border-color: var(--pink);
    }

    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 6px;
        max-height: 220px;
        overflow-y: auto;
    }

    @media (max-width: 480px) {
        .icon-picker-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    .icon-picker-option {
        width: 100%;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        background: var(--bg);
        color: var(--ink-soft);
        font-size: .95rem;
        cursor: pointer;
    }

    .icon-picker-option:hover {
        border-color: var(--pink);
        color: var(--pink-dark);
    }

    .icon-picker-option.is-selected {
        background: var(--pink-light);
        border-color: var(--pink);
        color: var(--pink-deep);
    }

    .icon-picker-empty {
        font-size: .8rem;
        color: var(--muted);
        text-align: center;
        padding: 16px 4px;
        grid-column: 1 / -1;
    }

    /* --- Live preview --- */
    .cms-preview-card {
        background: linear-gradient(160deg, var(--pink-pale), var(--card) 65%);
        border-radius: 16px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 20px;
    }

    .cms-preview-label {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 14px;
    }

    .cms-preview-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .cms-preview-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--pink-light);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        overflow: hidden;
    }

    .cms-preview-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cms-preview-badge {
        font-size: .66rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        background: var(--amber-light);
        color: var(--amber);
        display: none;
    }

    .cms-preview-badge.is-visible {
        display: inline-block;
    }

    .cms-preview-title {
        font-family: 'Source Serif 4', serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 4px;
        word-break: break-word;
    }

    .cms-preview-desc {
        font-size: .8rem;
        color: var(--ink-soft);
        line-height: 1.45;
        margin-bottom: 16px;
    }

    .cms-preview-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .cms-preview-price {
        font-family: 'Source Serif 4', serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--pink-dark);
    }

    .cms-preview-btn {
        font-size: .76rem;
        font-weight: 700;
        padding: 7px 14px;
        border-radius: 8px;
        background: var(--pink-deep);
        color: #fff;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
    <!-- ══ TOOLBAR ════════════════════════════════════════════ -->
    <div class="toolbar no-print">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Website Management &middot; {{ $label }} Cards</div>
            <h1>{{ $card ? 'Edit' : 'Add' }} {{ $label }}</h1>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('cms.cards', $type) }}" class="btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <button type="submit" form="cardForm" class="btn-primary">
                <i class="fa-solid fa-check"></i> {{ $card ? 'Save Changes' : 'Add ' . $label }}
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="cms-alert-error">
            There are errors in the form — please check the fields below.
        </div>
    @endif

    @php $iconType = old('icon_type', $card->icon_type ?? 'fa'); @endphp

    <form action="{{ $card ? route('cms.cards.update', $card) : route('cms.cards.store', $type) }}"
          method="POST" enctype="multipart/form-data" id="cardForm">
        @csrf
        @if ($card)
            @method('PUT')
        @endif

        <div class="cms-form-shell">
            <div class="cms-form-main">
                <div class="cms-form-section">
                    <h2>Basic Info</h2>

                    <div class="cms-field-row">
                        <div class="cms-field">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $card->title ?? '') }}" required data-preview="title">
                            @error('title')
                                <div class="cms-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="cms-field">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $card->slug ?? '') }}" required>
                            <p class="cms-field-hint">Unique identifier, e.g. "dino-adventure".</p>
                            @error('slug')
                                <div class="cms-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="cms-field">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" data-preview="description">{{ old('description', $card->description ?? '') }}</textarea>
                    </div>

                    <div class="cms-field-row">
                        <div class="cms-field">
                            <label for="badge_label">Badge Label</label>
                            <input type="text" name="badge_label" id="badge_label" value="{{ old('badge_label', $card->badge_label ?? '') }}" placeholder="e.g. Best Value" data-preview="badge">
                        </div>

                        <div class="cms-field">
                            <label for="badge_color">Badge Color</label>
                            @php $currentColor = old('badge_color', $card->badge_color ?? 'coral'); @endphp
                            <select name="badge_color" id="badge_color" data-preview="badgeColor">
                                <option value="coral"  {{ $currentColor === 'coral'  ? 'selected' : '' }}>Coral (default)</option>
                                <option value="teal"   {{ $currentColor === 'teal'   ? 'selected' : '' }}>Teal</option>
                                <option value="amber"  {{ $currentColor === 'amber'  ? 'selected' : '' }}>Amber</option>
                                <option value="violet" {{ $currentColor === 'violet' ? 'selected' : '' }}>Violet</option>
                            </select>
                        </div>
                    </div>
                    <p class="cms-field-hint" style="margin:-6px 0 18px;">Badge color controls the pill behind the badge label on the public site — independent from the icon.</p>

                    <div class="cms-field">
                        <label>Icon</label>

                        <div class="icon-source-tabs" role="tablist">
                            <button type="button" class="icon-source-tab {{ $iconType === 'fa' ? 'is-active' : '' }}" data-source="fa">Choose an icon</button>
                            <button type="button" class="icon-source-tab {{ $iconType === 'image' ? 'is-active' : '' }}" data-source="image">Upload image</button>
                        </div>

                        <input type="hidden" name="icon_type" id="icon_type" value="{{ $iconType }}">

                        <div class="icon-source-panel" data-panel="fa" @if ($iconType !== 'fa') hidden @endif>
                            <button type="button" class="icon-picker-trigger" id="iconPickerTrigger">
                                <span class="icon-picker-preview" id="iconPickerPreview">
                                    <i class="fa-solid fa-{{ old('icon', $card->icon ?? 'ticket') }}"></i>
                                </span>
                                <span class="icon-picker-trigger-text">Choose icon</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <input type="hidden" name="icon" id="icon" value="{{ old('icon', $card->icon ?? '') }}">

                            <div class="icon-picker-popover" id="iconPickerPopover" hidden>
                                <input type="text" class="icon-picker-search" id="iconPickerSearch" placeholder="Search icons...">
                                <div class="icon-picker-grid" id="iconPickerGrid"></div>
                            </div>
                            <p class="cms-field-hint">Pick from the icon library, or switch to "Upload image" if the one you want isn't here.</p>
                        </div>

                        <div class="icon-source-panel" data-panel="image" @if ($iconType !== 'image') hidden @endif>
                            @if (!empty($card->icon_image_path ?? null))
                                <div class="cms-image-current">
                                    <img src="{{ asset('storage/' . $card->icon_image_path) }}" alt="Current icon">
                                    <span>Current icon. Upload a new one to replace it.</span>
                                </div>
                            @endif
                            <input type="file" name="icon_image" id="icon_image" accept="image/png,image/jpeg,image/webp" class="cms-file-input">
                            <p class="cms-field-hint">JPG, PNG, or WebP, any size — it's automatically cropped and resized to fit as an icon.</p>
                            @error('icon_image')
                                <div class="cms-field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="cms-form-section">
                    <h2>Pricing &amp; Button</h2>

                    <div class="cms-field-row">
                        <div class="cms-field">
                            <label for="price_display">Price Display</label>
                            <input type="text" name="price_display" id="price_display" value="{{ old('price_display', $card->price_display ?? '') }}" placeholder="e.g. ₱499" data-preview="price">
                        </div>
                        <div class="cms-field">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $card->sort_order ?? 0) }}">
                            <p class="cms-field-hint">Lower numbers appear first.</p>
                        </div>
                    </div>

                    <div class="cms-field-row">
                        <div class="cms-field">
                            <label for="button_text">Button Text</label>
                            <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $card->button_text ?? '') }}" placeholder="e.g. Book Now" data-preview="button">
                        </div>
                        <div class="cms-field">
                            <label for="button_link">Button Link</label>
                            <input type="text" name="button_link" id="button_link" value="{{ old('button_link', $card->button_link ?? '') }}" placeholder="e.g. /reservations">
                        </div>
                    </div>
                </div>

                <div class="cms-form-section">
                    <h2>Bullet Points</h2>

                    <div class="cms-field">
                        <label for="features_text">Features</label>
                        <textarea name="features_text" id="features_text" placeholder="One line per feature">{{ old('features_text', isset($card) && $card && $card->features ? implode("\n", $card->features) : '') }}</textarea>
                        <p class="cms-field-hint">One bullet per line — this appears on the card itself.</p>
                    </div>

                    <div class="cms-field">
                        <label for="modal_list_text">Price-List Modal Items</label>
                        <textarea name="modal_list_text" id="modal_list_text" placeholder="One line per item">{{ old('modal_list_text', isset($card) && $card && $card->modal_list ? implode("\n", $card->modal_list) : '') }}</textarea>
                        <p class="cms-field-hint">One bullet per line — this appears in the "View Price List" popup.</p>
                    </div>
                </div>
            </div>

            <div class="cms-form-side">
                <div class="cms-preview-card">
                    <div class="cms-preview-label">Live Preview</div>
                    <div class="cms-preview-top">
                        <div class="cms-preview-icon" id="previewIcon"
                             @if ($iconType === 'image' && !empty($card->icon_image_path ?? null)) data-existing-image="{{ asset('storage/' . $card->icon_image_path) }}" @endif>
                            @if ($iconType === 'image' && !empty($card->icon_image_path ?? null))
                                <img src="{{ asset('storage/' . $card->icon_image_path) }}" alt="">
                            @else
                                <i class="fa-solid fa-{{ $card->icon ?? 'ticket' }}"></i>
                            @endif
                        </div>
                        <span class="cms-preview-badge" id="previewBadge"></span>
                    </div>
                    <div class="cms-preview-title" id="previewTitle">{{ $card->title ?? 'Card Title' }}</div>
                    <div class="cms-preview-desc" id="previewDesc">{{ $card->description ?? 'Card description will appear here.' }}</div>
                    <div class="cms-preview-bottom">
                        <span class="cms-preview-price" id="previewPrice">{{ $card->price_display ?? '—' }}</span>
                        <span class="cms-preview-btn" id="previewButton">{{ $card->button_text ?? 'Button' }}</span>
                    </div>
                </div>

                <div class="cms-form-section">
                    <h2>Image</h2>

                    @if (!empty($card->image_path ?? null))
                        <div class="cms-image-current">
                            <img src="{{ asset('storage/' . $card->image_path) }}" alt="{{ $card->title }}">
                            <span>Current image. Upload a new one to replace it.</span>
                        </div>
                    @endif

                    <div class="cms-field">
                        <input type="file" name="image" id="image" accept="image/*" class="cms-file-input">
                        <p class="cms-field-hint">JPG, PNG, or WebP. Max 4MB.</p>
                        @error('image')
                            <div class="cms-field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="cms-form-section">
                    <h2>Visibility</h2>

                    <div class="cms-toggle-row">
                        <label class="cms-toggle">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $card->is_active ?? true) ? 'checked' : '' }}>
                            Active (visible on the public site)
                        </label>
                        <label class="cms-toggle">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $card->is_featured ?? false) ? 'checked' : '' }}>
                            Featured
                        </label>
                    </div>
                </div>

                <div class="cms-form-actions">
                    <button type="submit" class="btn-primary">{{ $card ? 'Save Changes' : 'Add ' . $label }}</button>
                    <a href="{{ route('cms.cards', $type) }}" class="btn-ghost">Cancel</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function() {
    const form = document.getElementById('cardForm');
    if (!form) return;

    /* ---------- basic field -> preview sync ---------- */
    const titleInput = form.querySelector('[data-preview="title"]');
    const descInput = form.querySelector('[data-preview="description"]');
    const badgeInput = form.querySelector('[data-preview="badge"]');
    const priceInput = form.querySelector('[data-preview="price"]');
    const buttonInput = form.querySelector('[data-preview="button"]');

    const previewTitle = document.getElementById('previewTitle');
    const previewDesc = document.getElementById('previewDesc');
    const previewBadge = document.getElementById('previewBadge');
    const previewPrice = document.getElementById('previewPrice');
    const previewButton = document.getElementById('previewButton');
    const previewIcon = document.getElementById('previewIcon');

    function sync() {
        if (titleInput) previewTitle.textContent = titleInput.value || 'Card Title';
        if (descInput) previewDesc.textContent = descInput.value || 'Card description will appear here.';
        if (badgeInput) {
            previewBadge.textContent = badgeInput.value;
            previewBadge.classList.toggle('is-visible', badgeInput.value.trim().length > 0);
        }
        if (priceInput) previewPrice.textContent = priceInput.value || '—';
        if (buttonInput) previewButton.textContent = buttonInput.value || 'Button';
    }

    [titleInput, descInput, badgeInput, priceInput, buttonInput].forEach(function(el) {
        if (el) el.addEventListener('input', sync);
    });

    /* ---------- icon: library picker + custom image upload ---------- */
    const iconTypeInput = document.getElementById('icon_type');
    const iconInput = document.getElementById('icon');
    const iconImageInput = document.getElementById('icon_image');
    const iconPickerPreview = document.getElementById('iconPickerPreview');
    const iconPickerTrigger = document.getElementById('iconPickerTrigger');
    const iconPickerPopover = document.getElementById('iconPickerPopover');
    const iconPickerSearch = document.getElementById('iconPickerSearch');
    const iconPickerGrid = document.getElementById('iconPickerGrid');
    const sourceTabs = document.querySelectorAll('.icon-source-tab');

    let iconImageDataUrl = null;

    const ICONS = [
        ['ticket', 'Ticket'], ['map', 'Map'], ['map-pin', 'Pin'], ['compass', 'Compass'],
        ['calendar-days', 'Calendar'], ['clock', 'Clock'], ['users', 'Group'], ['user', 'Person'],
        ['star', 'Star'], ['gift', 'Gift'], ['camera', 'Camera'], ['bus', 'Bus'],
        ['car', 'Car'], ['motorcycle', 'Motorcycle'], ['bicycle', 'Bicycle'], ['ferris-wheel', 'Ferris wheel'],
        ['gamepad', 'Arcade'], ['umbrella-beach', 'Beach'], ['water', 'Water'], ['person-swimming', 'Swimming'],
        ['utensils', 'Dining'], ['mug-hot', 'Cafe'], ['ice-cream', 'Ice cream'], ['pizza-slice', 'Food'],
        ['campground', 'Camping'], ['tree', 'Tree'], ['mountain', 'Mountain'], ['person-hiking', 'Hiking'],
        ['leaf', 'Leaf'], ['paw', 'Animals'], ['dog', 'Dog'], ['fish', 'Fish'],
        ['anchor', 'Anchor'], ['life-ring', 'Life ring'], ['sun', 'Sun'], ['cloud-sun', 'Weather'],
        ['wind', 'Wind'], ['gem', 'Gem'], ['trophy', 'Trophy'], ['shield-halved', 'Shield'],
        ['heart', 'Heart'], ['music', 'Music'], ['palette', 'Art'], ['bed', 'Bed'],
        ['house', 'House'], ['building', 'Building'], ['train', 'Train'], ['plane', 'Plane'],
        ['ship', 'Boat'], ['cart-shopping', 'Shopping'], ['tag', 'Tag'], ['flag', 'Flag'],
        ['birthday-cake', 'Cake'], ['puzzle-piece', 'Puzzle'], ['dice', 'Dice'], ['book', 'Book'],
        ['graduation-cap', 'Education'], ['briefcase', 'Briefcase'], ['wrench', 'Tools'], ['seedling', 'Seedling'],
    ];

    function renderIconGrid(filter) {
        if (!iconPickerGrid) return;
        const query = (filter || '').trim().toLowerCase();
        iconPickerGrid.innerHTML = '';

        const matches = ICONS.filter(function(entry) {
            return !query || entry[0].includes(query) || entry[1].toLowerCase().includes(query);
        });

        if (!matches.length) {
            const empty = document.createElement('p');
            empty.className = 'icon-picker-empty';
            empty.textContent = 'No icons match "' + filter + '" — try "Upload image" instead.';
            iconPickerGrid.appendChild(empty);
            return;
        }

        matches.forEach(function(entry) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'icon-picker-option' + (iconInput.value === entry[0] ? ' is-selected' : '');
            btn.title = entry[1];
            btn.dataset.icon = entry[0];
            btn.innerHTML = '<i class="fa-solid fa-' + entry[0] + '"></i>';

            btn.addEventListener('click', function() {
                selectIcon(entry[0]);
                closePopover();
            });

            iconPickerGrid.appendChild(btn);
        });
    }

    function selectIcon(slug) {
        iconInput.value = slug;
        iconPickerPreview.innerHTML = '<i class="fa-solid fa-' + slug + '"></i>';
        iconImageDataUrl = null;
        syncPreviewIcon();
    }

    function openPopover() {
        iconPickerPopover.hidden = false;
        iconPickerSearch.value = '';
        renderIconGrid('');
        iconPickerSearch.focus();
    }

    function closePopover() {
        iconPickerPopover.hidden = true;
    }

    if (iconPickerTrigger) {
        iconPickerTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            iconPickerPopover.hidden ? openPopover() : closePopover();
        });
    }

    if (iconPickerSearch) {
        iconPickerSearch.addEventListener('input', function() {
            renderIconGrid(this.value);
        });
    }

    document.addEventListener('click', function(e) {
        if (iconPickerPopover && !iconPickerPopover.hidden && !iconPickerPopover.contains(e.target) && e.target !== iconPickerTrigger) {
            closePopover();
        }
    });

    sourceTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            const source = this.dataset.source;
            iconTypeInput.value = source;

            sourceTabs.forEach(function(t) { t.classList.toggle('is-active', t === tab); });
            document.querySelectorAll('.icon-source-panel').forEach(function(panel) {
                panel.hidden = panel.dataset.panel !== source;
            });

            syncPreviewIcon();
        });
    });

    if (iconImageInput) {
        iconImageInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                iconImageDataUrl = e.target.result;
                syncPreviewIcon();
            };
            reader.readAsDataURL(file);
        });
    }

    function syncPreviewIcon() {
        if (!previewIcon) return;

        if (iconTypeInput.value === 'image' && iconImageDataUrl) {
            previewIcon.innerHTML = '<img src="' + iconImageDataUrl + '" alt="">';
        } else if (iconTypeInput.value === 'image' && previewIcon.dataset.existingImage) {
            previewIcon.innerHTML = '<img src="' + previewIcon.dataset.existingImage + '" alt="">';
        } else {
            previewIcon.innerHTML = '<i class="fa-solid fa-' + (iconInput.value || 'ticket') + '"></i>';
        }
    }

    /* ---------- badge color -> preview sync ---------- */
    const badgeColorInput = document.getElementById('badge_color');

    const badgeColorMap = {
        coral:  { bg: 'rgba(255,90,95,.16)',  text: '#E5424A' },
        teal:   { bg: 'rgba(20,184,166,.16)', text: '#0D9488' },
        amber:  { bg: 'rgba(255,182,39,.20)', text: '#DE9A00' },
        violet: { bg: 'rgba(139,92,246,.16)', text: '#7347E0' },
    };

    function syncBadgeColor() {
        if (!badgeColorInput || !previewBadge) return;
        const c = badgeColorMap[badgeColorInput.value] || badgeColorMap.coral;
        previewBadge.style.background = c.bg;
        previewBadge.style.color = c.text;
    }
    if (badgeColorInput) badgeColorInput.addEventListener('change', syncBadgeColor);
    syncBadgeColor();

    sync();
    renderIconGrid('');
    syncPreviewIcon();
})();
</script>
@endpush