/* ═══════════════════════════════════════════════════════════
   inventory.js
   - Two tables: Stock Items (tracked stock) and Tickets (no stock)
   - Shared filters (search / zone / category) apply to both tables
   - Each table has its own pagination
   - Loading overlay, toast stack, low-stock preview panel
═══════════════════════════════════════════════════════════ */

/* ─── CONFIG / STATE ───────────────────────────────────────── */
var CAN_MANAGE        = !!window.INVENTORY_CAN_MANAGE;
var ROWS_PER_PAGE     = 10;
var DEFAULT_THRESHOLD = 20;

var TABLES = {
    stock:  { table: 'stockTable',  pager: 'stockPager',  count: 'stockCount',  empty: 'stockEmpty',  page: 1 },
    ticket: { table: 'ticketTable', pager: 'ticketPager', count: 'ticketCount', empty: 'ticketEmpty', page: 1 }
};

var lowStockItemsCache = [];
var pendingDeleteId    = null;
var addCatOpenedFrom   = null;

/* ─── LOADING OVERLAY ──────────────────────────────────────── */
function showLoading(text) {
    var overlay = document.getElementById('loadingOverlay');
    var textEl  = document.getElementById('loadingText');
    if (textEl) textEl.textContent = text || 'Processing…';
    if (overlay) overlay.classList.add('active');
}
function hideLoading() {
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('active');
}

/* ─── ESCAPE HELPER ────────────────────────────────────────── */
function escHtml(str) {
    return String(str === null || str === undefined ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/* ─── TOAST ────────────────────────────────────────────────── */
function showToast(msg, type) {
    var stack = document.getElementById('toastStack');
    if (!stack) return;

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + (type === 'error' ? 'error' : 'success');

    var iconClass = type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check';
    var title     = type === 'error' ? 'Error' : 'Success';

    toast.innerHTML =
        '<span class="toast-icon"><i class="fas ' + iconClass + '"></i></span>' +
        '<span class="toast-body">' +
            '<span class="toast-title">' + title + '</span>' +
            '<span class="toast-msg">' + escHtml(msg) + '</span>' +
        '</span>' +
        '<button type="button" class="toast-close">&times;</button>' +
        '<span class="toast-bar"></span>';

    stack.appendChild(toast);

    function remove() {
        toast.classList.add('hide');
        setTimeout(function () { toast.remove(); }, 280);
    }
    toast.querySelector('.toast-close').addEventListener('click', remove);
    setTimeout(remove, 3000);
}

/* ─── POST HELPER ──────────────────────────────────────────── */
function post(url, data, callback, loadingMsg) {
    showLoading(loadingMsg || 'Processing…');
    var fd = new FormData();
    Object.keys(data).forEach(function (k) { fd.append(k, data[k] === null ? '' : data[k]); });
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    fetch(url, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) { hideLoading(); callback(res); })
        .catch(function () { hideLoading(); showToast('Server error. Check your connection.', 'error'); });
}

/* ─── FORMAT HELPERS ───────────────────────────────────────── */
function formatPeso(n) {
    return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function statusBadge(stock, threshold) {
    var t = parseInt(threshold, 10) || DEFAULT_THRESHOLD;
    return parseInt(stock, 10) <= t
        ? '<span class="status-badge status-low">Low Stock</span>'
        : '<span class="status-badge status-good">In Stock</span>';
}

/* ─── TABLE ACCESS HELPERS ─────────────────────────────────── */
function getBody(key) {
    return document.querySelector('#' + TABLES[key].table + ' tbody');
}
function getRows(key) {
    return Array.prototype.slice.call(getBody(key).querySelectorAll('tr'));
}
function findRow(id) {
    return document.querySelector('#stockTable tbody tr[data-id="' + id + '"], #ticketTable tbody tr[data-id="' + id + '"]');
}
function keyOfRow(row) {
    return row.closest('table').id === TABLES.stock.table ? 'stock' : 'ticket';
}
function isMatched(row) {
    return row.dataset.match !== '0';
}

/* ─── ROW BUILDER (used by Add / Edit) ─────────────────────── */
/* item = { id, name, categoryId, cat, zone, tracked, stock, price, threshold } */
function actionButtons(item) {
    var label = item.tracked ? 'product' : 'ticket';
    var html  = '<div class="row-actions">';
    if (item.tracked) {
        html += '<button type="button" class="action-btn restock-btn" data-action="restock" data-tip="Restock" aria-label="Restock ' + escHtml(item.name) + '"><i class="fas fa-plus-circle"></i></button>';
    }
    html += '<button type="button" class="action-btn edit-btn" data-action="edit" data-tip="Edit ' + label + '" aria-label="Edit ' + escHtml(item.name) + '"><i class="fas fa-pen"></i></button>';
    html += '<button type="button" class="action-btn delete-btn" data-action="delete" data-tip="Delete ' + label + '" aria-label="Delete ' + escHtml(item.name) + '"><i class="fas fa-trash-can"></i></button>';
    html += '</div>';
    return html;
}

function buildRow(item) {
    var tr = document.createElement('tr');

    tr.dataset.id         = item.id;
    tr.dataset.name       = item.name;
    tr.dataset.categoryId = item.categoryId;
    tr.dataset.cat        = item.cat;
    tr.dataset.zone       = item.zone;
    tr.dataset.tracked    = item.tracked ? '1' : '0';
    tr.dataset.stock      = item.tracked ? item.stock : '';
    tr.dataset.price      = item.price;
    tr.dataset.threshold  = item.threshold;
    tr.dataset.match      = '1';

    var html =
        '<td>' + escHtml(item.name) + '</td>' +
        '<td>' + escHtml(item.cat) + ' <span class="zone-tag">' + escHtml(item.zone) + '</span></td>';

    if (item.tracked) {
        html += '<td class="cell-stock">' + parseInt(item.stock, 10) + '</td>';
    }

    html += '<td class="cell-price">' + formatPeso(item.price) + '</td>';

    if (item.tracked) {
        html += '<td class="cell-status">' + statusBadge(item.stock, item.threshold) + '</td>';
    }

    if (CAN_MANAGE) {
        html += '<td>' + actionButtons(item) + '</td>';
    }

    tr.innerHTML = html;
    return tr;
}

/* Insert a row in the same order the server uses: zone → category → name */
function sortKey(row) {
    return (row.dataset.zone + '|' + row.dataset.cat + '|' + row.dataset.name).toLowerCase();
}
function insertSorted(tbody, row) {
    var key    = sortKey(row);
    var before = null;
    var rows   = tbody.children;
    for (var i = 0; i < rows.length; i++) {
        if (sortKey(rows[i]) > key) { before = rows[i]; break; }
    }
    if (before) tbody.insertBefore(row, before);
    else tbody.appendChild(row);
}

/* ─── FILTERS ──────────────────────────────────────────────── */
function computeMatches() {
    var searchVal   = document.getElementById('searchInput').value.toLowerCase();
    var zoneVal     = document.getElementById('zoneFilter').value;
    var categoryVal = document.getElementById('categoryFilter').value;

    ['stock', 'ticket'].forEach(function (key) {
        getRows(key).forEach(function (r) {
            var text  = r.textContent.toLowerCase();
            var zone  = r.dataset.zone || '';
            var catId = r.dataset.categoryId || '';

            var matchSearch   = text.indexOf(searchVal) !== -1;
            var matchZone     = zoneVal === 'all' || zone === zoneVal;
            var matchCategory = categoryVal === 'all' || catId === categoryVal;

            r.dataset.match = (matchSearch && matchZone && matchCategory) ? '1' : '0';
        });
    });
}

function resetFilters() {
    document.getElementById('searchInput').value    = '';
    document.getElementById('zoneFilter').value     = 'all';
    document.getElementById('categoryFilter').value = 'all';
}

/* ─── PAGINATION (per table) ───────────────────────────────── */
function renderTable(key) {
    var cfg     = TABLES[key];
    var matched = [];

    getRows(key).forEach(function (r) {
        if (isMatched(r)) matched.push(r);
        else r.style.display = 'none';
    });

    var totalPages = Math.max(1, Math.ceil(matched.length / ROWS_PER_PAGE));
    if (cfg.page > totalPages) cfg.page = totalPages;
    if (cfg.page < 1) cfg.page = 1;

    var start = (cfg.page - 1) * ROWS_PER_PAGE;
    var end   = start + ROWS_PER_PAGE;

    matched.forEach(function (r, idx) {
        r.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    document.getElementById(cfg.count).textContent = matched.length;
    document.getElementById(cfg.empty).style.display = matched.length === 0 ? 'block' : 'none';

    renderPagination(key, matched.length, totalPages);
}

function renderPagination(key, matchedCount, totalPages) {
    var cfg       = TABLES[key];
    var container = document.getElementById(cfg.pager);
    if (!container) return;
    container.innerHTML = '';

    if (matchedCount === 0) { container.style.display = 'none'; return; }
    container.style.display = 'flex';

    var start = ((cfg.page - 1) * ROWS_PER_PAGE) + 1;
    var end   = Math.min(cfg.page * ROWS_PER_PAGE, matchedCount);

    var info = document.createElement('span');
    info.className   = 'pagination-info';
    info.textContent = 'Showing ' + start + '–' + end + ' of ' + matchedCount;
    container.appendChild(info);

    var nav = document.createElement('div');
    nav.className = 'pagination-nav';

    var prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.disabled = cfg.page === 1;
    prevBtn.addEventListener('click', function () { goToPage(key, cfg.page - 1); });
    nav.appendChild(prevBtn);

    getPageNumbers(cfg.page, totalPages, 5).forEach(function (p) {
        if (p === '...') {
            var span = document.createElement('span');
            span.className = 'page-ellipsis';
            span.textContent = '…';
            nav.appendChild(span);
        } else {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'page-btn' + (p === cfg.page ? ' active' : '');
            btn.textContent = p;
            btn.addEventListener('click', function () { goToPage(key, p); });
            nav.appendChild(btn);
        }
    });

    var nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = cfg.page === totalPages;
    nextBtn.addEventListener('click', function () { goToPage(key, cfg.page + 1); });
    nav.appendChild(nextBtn);

    container.appendChild(nav);
}

function getPageNumbers(current, total, maxButtons) {
    var arr = [], i;
    if (total <= maxButtons) {
        for (i = 1; i <= total; i++) arr.push(i);
        return arr;
    }
    var pages = [1];
    var left  = Math.max(2, current - 1);
    var right = Math.min(total - 1, current + 1);
    if (left > 2) pages.push('...');
    for (var j = left; j <= right; j++) pages.push(j);
    if (right < total - 1) pages.push('...');
    pages.push(total);
    return pages;
}

function goToPage(key, p) {
    TABLES[key].page = p;
    renderTable(key);
}

/* ─── METRICS + LOW STOCK LIST ─────────────────────────────── */
function loadMetrics() {
    var stockRows  = getRows('stock').filter(isMatched);
    var ticketRows = getRows('ticket').filter(isMatched);
    var units      = 0;
    var lowItems   = [];

    stockRows.forEach(function (r) {
        var s         = parseInt(r.dataset.stock, 10) || 0;
        var threshold = parseInt(r.dataset.threshold, 10) || DEFAULT_THRESHOLD;
        units += s;

        if (s <= threshold) {
            lowItems.push({
                id:    r.dataset.id,
                name:  r.dataset.name,
                cat:   r.dataset.cat,
                zone:  r.dataset.zone,
                stock: s
            });
        }
    });

    document.getElementById('statItems').textContent    = stockRows.length;
    document.getElementById('statTickets').textContent  = ticketRows.length;
    document.getElementById('lowStocks').textContent    = lowItems.length;
    document.getElementById('totalStocks').textContent  = units.toLocaleString();

    lowItems.sort(function (a, b) { return a.stock - b.stock; });
    lowStockItemsCache = lowItems;
    renderLowStockPreview();
}

function refreshAll() {
    computeMatches();
    renderTable('stock');
    renderTable('ticket');
    loadMetrics();
}

/* Jump to a row: finds its table + page and highlights it.
   keepFilters = true keeps the current filters (used after Edit). */
function goToRow(id, keepFilters) {
    if (!keepFilters) resetFilters();
    computeMatches();

    var row = findRow(id);
    if (!row) return;

    if (isMatched(row)) {
        var key     = keyOfRow(row);
        var matched = getRows(key).filter(isMatched);
        TABLES[key].page = Math.floor(matched.indexOf(row) / ROWS_PER_PAGE) + 1;
    }

    refreshAll();

    if (!isMatched(row)) return;
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    row.classList.add('row-highlight');
    setTimeout(function () { row.classList.remove('row-highlight'); }, 1500);
}

/* ─── LOW STOCK PREVIEW PANEL ──────────────────────────────── */
function renderLowStockPreview() {
    var list = document.getElementById('lowStockPreviewList');
    if (!list) return;

    if (lowStockItemsCache.length === 0) {
        list.innerHTML = '<div class="low-stock-preview-empty">🎉 No low stock items right now.</div>';
        return;
    }

    list.innerHTML = lowStockItemsCache.map(function (item) {
        return '<div class="low-stock-preview-item" data-jump-id="' + escHtml(item.id) + '">' +
                    '<span class="lsp-name">' + escHtml(item.name) +
                        '<span class="lsp-cat">' + escHtml(item.cat) + ' · ' + escHtml(item.zone) + '</span>' +
                    '</span>' +
                    '<span class="lsp-qty">' + item.stock + ' left</span>' +
                '</div>';
    }).join('');
}

function toggleLowStockPreview(forceOpen) {
    var panel = document.getElementById('lowStockPreview');
    var card  = document.getElementById('lowStockCard');
    var open  = typeof forceOpen === 'boolean' ? forceOpen : !panel.classList.contains('active');

    panel.classList.toggle('active', open);
    card.classList.toggle('open', open);
}

document.getElementById('lowStockCard').addEventListener('click', function () {
    toggleLowStockPreview();
});

document.getElementById('closeLowStockPreview').addEventListener('click', function (e) {
    e.stopPropagation();
    toggleLowStockPreview(false);
});

document.getElementById('lowStockPreviewList').addEventListener('click', function (e) {
    var item = e.target.closest('[data-jump-id]');
    if (!item) return;
    toggleLowStockPreview(false);
    goToRow(item.dataset.jumpId);
});

/* ─── FILTER EVENTS ────────────────────────────────────────── */
function resetPages() {
    TABLES.stock.page  = 1;
    TABLES.ticket.page = 1;
}

document.getElementById('searchInput').addEventListener('input', function () {
    resetPages(); refreshAll();
});
document.getElementById('zoneFilter').addEventListener('change', function () {
    resetPages(); refreshAll();
});
document.getElementById('categoryFilter').addEventListener('change', function () {
    resetPages(); refreshAll();
});

/* ─── IMPORT CSV ───────────────────────────────────────────── */
document.getElementById('openImportBtn').addEventListener('click', function () {
    document.getElementById('importFileInput').click();
});

document.getElementById('importFileInput').addEventListener('change', function (event) {
    var file = event.target.files[0];
    if (!file) return;

    showLoading('Importing CSV…');

    var formData = new FormData();
    formData.append('action', 'import_products');
    formData.append('import_file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('/inventory/import', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === 'success') {
                showToast(data.message);
                // Keep the overlay visible until the page reloads for a smooth transition
                window.setTimeout(function () { window.location.reload(); }, 1000);
            } else {
                hideLoading();
                showToast(data.message, 'error');
            }
        })
        .catch(function () {
            hideLoading();
            showToast('Upload failed. Check your connection.', 'error');
        })
        .finally(function () { event.target.value = ''; });
});

/* ─── EXPORT (form submit, not fetch) ──────────────────────── */
document.getElementById('exportForm').addEventListener('submit', function () {
    showLoading('Preparing export…');
    // The browser handles the file download; this timeout is only a safety net
    setTimeout(hideLoading, 4000);
});

/* ─── MODAL OPEN / CLOSE ───────────────────────────────────── */
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

['addProductModal', 'editProductModal', 'deleteConfirmModal', 'restockModal', 'addCategoryModal'].forEach(function (id) {
    document.getElementById(id).addEventListener('click', function (e) {
        if (e.target === this) closeModal(id);
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal('addProductModal');
        closeModal('editProductModal');
        closeModal('deleteConfirmModal');
        closeModal('restockModal');
        closeModal('addCategoryModal');
        toggleLowStockPreview(false);
    }
});

/* ─── TOGGLE STOCK FIELD (based on selected category) ──────── */
function toggleStockField(selectId, inputId, noteId) {
    var select = document.getElementById(selectId);
    var input  = document.getElementById(inputId);
    var note   = document.getElementById(noteId);

    // No category chosen yet → keep the field usable and hide the note
    if (!select.value) {
        input.disabled     = false;
        input.placeholder  = 'e.g. 100';
        note.style.display = 'none';
        return;
    }

    var opt       = select.options[select.selectedIndex];
    var isTracked = opt && opt.dataset.tracked === '1';

    if (!isTracked) {
        input.value        = '';
        input.disabled     = true;
        input.placeholder  = 'Not tracked';
        note.style.display = 'inline';
    } else {
        input.disabled     = false;
        input.placeholder  = 'e.g. 100';
        note.style.display = 'none';
    }
}

document.getElementById('addCategory').addEventListener('change', function () {
    toggleStockField('addCategory', 'addStock', 'addStockNote');
});
document.getElementById('editProductCategory').addEventListener('change', function () {
    toggleStockField('editProductCategory', 'editProductStock', 'editStockNote');
});

/* ─── ADD ITEM ─────────────────────────────────────────────── */
document.getElementById('openAddBtn').addEventListener('click', function () {
    document.getElementById('addName').value     = '';
    document.getElementById('addCategory').value = '';
    document.getElementById('addStock').value    = '';
    document.getElementById('addPrice').value    = '';
    toggleStockField('addCategory', 'addStock', 'addStockNote');
    openModal('addProductModal');
    document.getElementById('addName').focus();
});

document.getElementById('closeAddBtn').addEventListener('click',  function () { closeModal('addProductModal'); });
document.getElementById('cancelAddBtn').addEventListener('click', function () { closeModal('addProductModal'); });

document.getElementById('submitAddBtn').addEventListener('click', function () {
    var name       = document.getElementById('addName').value.trim();
    var catSelect  = document.getElementById('addCategory');
    var categoryId = catSelect.value;
    var opt        = catSelect.options[catSelect.selectedIndex];
    var isTracked  = !!(opt && opt.dataset.tracked === '1');
    var stock      = document.getElementById('addStock').value.trim();
    var price      = document.getElementById('addPrice').value.trim() || '0';
    var stockVal   = isTracked ? stock : null;

    if (!name || !categoryId || (isTracked && !stock)) {
        showToast('Please fill in all required fields!', 'error');
        return;
    }

    post('/inventory/add', { action: 'add_product', name: name, category_id: categoryId, stock: stockVal, price: price },
        function (data) {
            if (data.status !== 'success') {
                showToast(data.message, 'error');
                return;
            }

            showToast(data.message);

            var cat     = data.category;
            var tracked = !!cat.is_stock_tracked;

            var item = {
                id:         data.id,
                name:       name,
                categoryId: cat.id,
                cat:        cat.name,
                zone:       cat.zone,
                tracked:    tracked,
                stock:      tracked ? (parseInt(stock, 10) || 0) : null,
                price:      (parseFloat(price) || 0).toFixed(2),
                threshold:  cat.low_stock_threshold || DEFAULT_THRESHOLD
            };

            insertSorted(getBody(tracked ? 'stock' : 'ticket'), buildRow(item));
            closeModal('addProductModal');
            goToRow(data.id);
        },
        'Adding item…'
    );
});

/* ─── EDIT ITEM ────────────────────────────────────────────── */
document.getElementById('closeEditBtn').addEventListener('click',  function () { closeModal('editProductModal'); });
document.getElementById('cancelEditBtn').addEventListener('click', function () { closeModal('editProductModal'); });

document.getElementById('submitEditBtn').addEventListener('click', function () {
    var id         = document.getElementById('editProductId').value;
    var name       = document.getElementById('editProductName').value.trim();
    var catSelect  = document.getElementById('editProductCategory');
    var categoryId = catSelect.value;
    var opt        = catSelect.options[catSelect.selectedIndex];
    var isTracked  = !!(opt && opt.dataset.tracked === '1');
    var stock      = document.getElementById('editProductStock').value.trim();
    var price      = document.getElementById('editProductPrice').value.trim() || '0';
    var stockVal   = isTracked ? stock : null;

    if (!name || !categoryId || (isTracked && !stock)) {
        showToast('Please fill in all required fields!', 'error');
        return;
    }

    post('/inventory/update', { action: 'edit_product', id: id, name: name, category_id: categoryId, stock: stockVal === null ? 0 : stockVal, price: price },
        function (data) {
            if (data.status !== 'success') {
                showToast(data.message, 'error');
                return;
            }

            showToast(data.message);

            var oldRow    = findRow(id);
            var threshold = oldRow ? oldRow.dataset.threshold : DEFAULT_THRESHOLD;
            var cat       = data.category;
            var tracked   = !!cat.is_stock_tracked;

            var item = {
                id:         id,
                name:       name,
                categoryId: cat.id,
                cat:        cat.name,
                zone:       cat.zone,
                tracked:    tracked,
                stock:      tracked ? (parseInt(stock, 10) || 0) : null,
                price:      (parseFloat(price) || 0).toFixed(2),
                threshold:  cat.low_stock_threshold || threshold
            };

            // Rebuild the row. If the category changed from tracked → not tracked
            // (or the other way around), it moves to the other table automatically.
            if (oldRow) oldRow.remove();
            insertSorted(getBody(tracked ? 'stock' : 'ticket'), buildRow(item));

            closeModal('editProductModal');
            goToRow(id, true);
        },
        'Saving changes…'
    );
});

/* ─── DELETE ITEM ──────────────────────────────────────────── */
document.getElementById('closeDeleteBtn').addEventListener('click',  function () { closeModal('deleteConfirmModal'); });
document.getElementById('cancelDeleteBtn').addEventListener('click', function () { closeModal('deleteConfirmModal'); });

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!pendingDeleteId) return;
    var delId = pendingDeleteId;

    post('/inventory/delete', { action: 'delete_product', id: delId },
        function (data) {
            if (data.status === 'success') {
                showToast(data.message);
                var row = findRow(delId);
                if (row) row.remove();
                refreshAll();
                closeModal('deleteConfirmModal');
                pendingDeleteId = null;
            } else {
                showToast(data.message, 'error');
            }
        },
        'Deleting…'
    );
});

/* ─── RESTOCK ──────────────────────────────────────────────── */
document.getElementById('closeRestockBtn').addEventListener('click',  function () { closeModal('restockModal'); });
document.getElementById('cancelRestockBtn').addEventListener('click', function () { closeModal('restockModal'); });

document.getElementById('submitRestockBtn').addEventListener('click', function () {
    var id  = document.getElementById('restockProductId').value;
    var qty = document.getElementById('restockQty').value.trim();

    if (!qty || parseInt(qty, 10) <= 0) {
        showToast('Please enter a valid quantity!', 'error');
        return;
    }

    post('/inventory/restock', { action: 'restock_product', id: id, qty: qty }, function (data) {
        if (data.status === 'success') {
            showToast(data.message);

            var row = findRow(id);
            if (row) {
                var newStock = data.new_stock;
                row.dataset.stock = newStock;
                row.querySelector('.cell-stock').textContent  = newStock;
                row.querySelector('.cell-status').innerHTML   = statusBadge(newStock, row.dataset.threshold);
            }
            refreshAll();
            closeModal('restockModal');
        } else {
            showToast(data.message, 'error');
        }
    }, 'Updating stock…');
});

// ─── SAVE PHYSICAL COUNT ───
document.addEventListener('change', async (e) => {
    const inp = e.target.closest('.count-input');
    if (!inp || inp.value === '') return;

    const cell = document.getElementById('var-' + inp.dataset.id);

    try {
        const res = await fetch('/inventory/count', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: inp.dataset.id, actual_count: inp.value })
        });
        const d = await res.json();

        if (d.status !== 'success') {
            cell.textContent = d.message || 'Error';
            return;
        }

        const v = d.variance;
        cell.innerHTML = v === 0
            ? '<span style="color:#1FAE9E">Balanced</span>'
            : v < 0
                ? `<span style="color:#DC2626">Short ${Math.abs(v)}</span>`
                : `<span style="color:#D97706">Over ${v}</span>`;
    } catch (err) {
        cell.textContent = 'Failed to save';
    }
});

/* ─── TABLE CLICK DELEGATION (both tables) ─────────────────── */
function handleTableClick(e) {
    var btn = e.target.closest('[data-action]');
    if (!btn) return;

    var row    = btn.closest('tr');
    var id     = row.dataset.id;
    var name   = row.dataset.name;
    var catId  = row.dataset.categoryId;
    var stock  = row.dataset.stock;
    var price  = row.dataset.price;
    var action = btn.dataset.action;

    if (action === 'edit') {
        document.getElementById('editProductId').value       = id;
        document.getElementById('editProductName').value     = name;
        document.getElementById('editProductCategory').value = catId;
        document.getElementById('editProductStock').value    = stock;
        document.getElementById('editProductPrice').value    = price;
        toggleStockField('editProductCategory', 'editProductStock', 'editStockNote');
        openModal('editProductModal');
        document.getElementById('editProductName').focus();
    }

    if (action === 'delete') {
        pendingDeleteId = id;
        document.getElementById('deleteProductName').textContent = name;
        openModal('deleteConfirmModal');
    }

    if (action === 'restock') {
        document.getElementById('restockProductId').value          = id;
        document.getElementById('restockProductName').textContent  = name;
        document.getElementById('restockCurrentStock').textContent = stock;
        document.getElementById('restockQty').value                = '';
        openModal('restockModal');
        document.getElementById('restockQty').focus();
    }
}

document.getElementById(TABLES.stock.table).addEventListener('click', handleTableClick);
document.getElementById(TABLES.ticket.table).addEventListener('click', handleTableClick);

/* ─── ADD NEW CATEGORY ─────────────────────────────────────── */
function openAddCategoryModal(from) {
    addCatOpenedFrom = from;
    document.getElementById('newCatName').value        = '';
    document.getElementById('newCatZone').value        = '';
    document.getElementById('newCatType').value        = 'merchandise';
    document.getElementById('newCatTracked').checked   = true;
    document.getElementById('addCatError').textContent = '';
    openModal('addCategoryModal');
    document.getElementById('newCatName').focus();
}

document.getElementById('openAddCatFromAdd').addEventListener('click',  function () { openAddCategoryModal('add'); });
document.getElementById('openAddCatFromEdit').addEventListener('click', function () { openAddCategoryModal('edit'); });
document.getElementById('closeAddCatBtn').addEventListener('click',     function () { closeModal('addCategoryModal'); });
document.getElementById('cancelAddCatBtn').addEventListener('click',    function () { closeModal('addCategoryModal'); });

document.getElementById('submitAddCatBtn').addEventListener('click', function () {
    var name    = document.getElementById('newCatName').value.trim();
    var zone    = document.getElementById('newCatZone').value.trim();
    var type    = document.getElementById('newCatType').value;
    var tracked = document.getElementById('newCatTracked').checked;

    if (!name || !zone) {
        document.getElementById('addCatError').textContent = 'Category name and zone are required.';
        return;
    }

    post('/inventory/add-category', {
        action: 'add_category',
        category_name: name,
        zone: zone,
        category_type: type,
        is_stock_tracked: tracked ? 1 : 0
    }, function (data) {
        if (data.status !== 'success') {
            document.getElementById('addCatError').textContent = data.message;
            return;
        }

        showToast(data.message);
        var cat = data.category;

        // Add the new category to both modal dropdowns
        [document.getElementById('addCategory'), document.getElementById('editProductCategory')].forEach(function (select) {
            var group = Array.prototype.slice.call(select.querySelectorAll('optgroup')).find(function (g) { return g.label === cat.zone; });
            if (!group) {
                group = document.createElement('optgroup');
                group.label = cat.zone;
                select.appendChild(group);
            }
            var option = document.createElement('option');
            option.value           = cat.category_id;
            option.textContent     = cat.category_name;
            option.dataset.tracked = cat.is_stock_tracked ? '1' : '0';
            option.dataset.zone    = cat.zone;
            group.appendChild(option);
        });

        // Add the zone to the zone filter if it is new
        var zoneFilter = document.getElementById('zoneFilter');
        var zoneExists = Array.prototype.slice.call(zoneFilter.options).some(function (o) { return o.value === cat.zone; });
        if (!zoneExists) {
            var zOpt = document.createElement('option');
            zOpt.value = cat.zone;
            zOpt.textContent = cat.zone;
            zoneFilter.appendChild(zOpt);
        }

        // Add the category to the category filter
        var catFilter = document.getElementById('categoryFilter');
        var fGroup = Array.prototype.slice.call(catFilter.querySelectorAll('optgroup')).find(function (g) { return g.label === cat.zone; });
        if (!fGroup) {
            fGroup = document.createElement('optgroup');
            fGroup.label = cat.zone;
            catFilter.appendChild(fGroup);
        }
        var fOpt = document.createElement('option');
        fOpt.value = cat.category_id;
        fOpt.textContent = cat.category_name;
        fOpt.dataset.zone = cat.zone;
        fGroup.appendChild(fOpt);

        // Auto-select the new category in whichever modal opened this one
        if (addCatOpenedFrom === 'add') {
            document.getElementById('addCategory').value = cat.category_id;
            toggleStockField('addCategory', 'addStock', 'addStockNote');
        } else if (addCatOpenedFrom === 'edit') {
            document.getElementById('editProductCategory').value = cat.category_id;
            toggleStockField('editProductCategory', 'editProductStock', 'editStockNote');
        }

        closeModal('addCategoryModal');
    }, 'Saving category…');
});

/* ─── INITIAL RENDER ───────────────────────────────────────── */
refreshAll();