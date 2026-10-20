// ============================================================
//  POS SYSTEM - pos.js
// ============================================================

let cart = [];
let lastAuthorizedBy = null;
let selectedPayment = 'cash';
let selectedService = 'Walk-In';
let currentCategory = 'all';
let discountAmount  = 0;
let discountType    = 'percent';
let discountValue   = 0;
let currentInvoice  = startInvoice;
let pendingVoidId   = null;
let pendingTendered = 0;
let pendingAction   = null;
let specialDiscount = null;

let currentMode = 'skates';
let currentSnackCat = 'sweets';

let pendingTransactions = [];
let activePendingId = null;

const TAX_RATE = 0.12;

const PAYMENT_LABELS = {
  cash:      'Cash',
  card:      'Card',
  gcash:     'GCash',
  maya:      'Maya',
  stardeals: 'StarDeals',
  klook:     "Klook",
  online:   'Online'
};

const ZONE_ROLLER_FEVER   = 'skataya';
const ZONE_FIELD_OF_RIDES = 'piyestarides';
const ZONE_DINO_ADVENTURE = 'dinolakbay';


const CATEGORIES_SKATES = [
  'all','ticket','rentals','billiards','massage','socks'
];

const SKATES_SIDEBAR = [
  { cat: 'all',       icon: 'ti-apps',            label: 'All' },
  { cat: 'ticket',    icon: 'ti-ticket',          label: 'Ticket' },
  { cat: 'rentals',   icon: 'ti-shoe',            label: 'Rentals' },
  { cat: 'billiards', icon: 'ti-disc',            label: 'Billiards' },
  { cat: 'massage',   icon: 'ti-hand-stop',       label: 'Massage' },
  { cat: 'socks',     icon: 'ti-socks',           label: 'Socks' },
];

const CATEGORIES_SNACKBAR = ['sweets','beverages','snacks','noodles','icecream'];

const SNACK_SIDEBAR = [
  { cat: 'sweets',    icon: 'ti-candy',        label: 'Sweets' },
  { cat: 'beverages', icon: 'ti-glass-full',   label: 'Beverages' },
  { cat: 'snacks',    icon: 'ti-cookie',       label: 'Snacks' },
  { cat: 'noodles',   icon: 'ti-bowl',         label: 'Noodles' },
  { cat: 'icecream',  icon: 'ti-ice-cream',    label: 'Ice Cream' },
];

function storageKey(base) {
  const uid = (typeof currentUserId !== 'undefined' && currentUserId) ? currentUserId : 'guest';
  return base + '_' + uid;
}

/* ─── LOADING OVERLAY HELPERS ──────────────────────────────── */
function showLoading(text) {
  const overlay = document.getElementById('loadingOverlay');
  const textEl  = document.getElementById('loadingText');
  if (textEl) textEl.textContent = text || 'Processing…';
  if (overlay) overlay.classList.add('active');
}
function hideLoading() {
  const overlay = document.getElementById('loadingOverlay');
  if (overlay) overlay.classList.remove('active');
}

// ============================================================
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  buildCategoryTabs();
  buildSnackSidebar();
  switchMode('skates');
  updateDateTime();
  setInterval(updateDateTime, 30000);
  checkCutoffStatus(); 

  const saved = localStorage.getItem(storageKey('pos_cart')); 
  if (saved) {
    try {
      cart = JSON.parse(saved);
      if (!Array.isArray(cart)) cart = [];
      renderCart();
      updateTotals();
    } catch (e) {
      cart = [];
    }
  }

  const savedPending = localStorage.getItem(storageKey('pos_pending'));
  if (savedPending) {
    try {
      pendingTransactions = JSON.parse(savedPending);
      if (!Array.isArray(pendingTransactions)) pendingTransactions = [];
      renderPendingPills();
    } catch (e) {
      pendingTransactions = [];
    }
  }
});

function updateDateTime() {
  const el = document.getElementById('topbarTime');
  if (!el) return;
  const now = new Date();
  el.textContent = now.toLocaleDateString('en-PH', {
    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
  }) + '  ' + now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
}

// ============================================================
//  MODE SWITCHING
// ============================================================
function switchMode(mode) {
  currentMode = mode;
  document.querySelectorAll('.mode-tab').forEach(t => {
    t.classList.toggle('active', t.dataset.mode === mode);
  });

  const skatesCatWrap  = document.getElementById('skatesCatWrap');
  const snackbarBody   = document.getElementById('snackbarBody');
  const prodGridSkates = document.getElementById('prodGridSkates');
  const prodGridField  = document.getElementById('prodGridField');
  const prodGridDino   = document.getElementById('prodGridDino');

  if (skatesCatWrap)  skatesCatWrap.style.display = 'none';
  if (snackbarBody)   snackbarBody.style.display = 'none';
  if (prodGridSkates) prodGridSkates.style.display = 'none';
  if (prodGridField)  prodGridField.style.display = 'none';
  if (prodGridDino)   prodGridDino.style.display = 'none';

  if (mode === 'skates') {
    if (skatesCatWrap)  skatesCatWrap.style.display = '';
    if (prodGridSkates) prodGridSkates.style.display = '';
    currentCategory = 'all';
    document.querySelectorAll('#catRow .snack-sidebar-btn').forEach(b => b.classList.toggle('active', b.dataset.cat === 'all'));

  } else if (mode === 'snackbar') {
    if (snackbarBody) snackbarBody.style.display = 'flex';
    currentCategory = currentSnackCat;
    document.querySelectorAll('.snack-sidebar-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.cat === currentSnackCat);
    });

  } else if (mode === 'fieldofrides') {
    if (prodGridField) prodGridField.style.display = '';
    currentCategory = 'all';

  } else if (mode === 'dinoadventure') {
    if (prodGridDino) prodGridDino.style.display = '';
    currentCategory = 'all';
  }

  renderProducts();
}

// ============================================================
//  CATEGORY TABS (Skates) — same style as Snackbar sidebar
// ============================================================
function buildCategoryTabs() {
  const row = document.getElementById('catRow');
  if (!row) return;
  row.innerHTML = '';

  SKATES_SIDEBAR.forEach(item => {
    const btn = document.createElement('button');
    btn.className = 'snack-sidebar-btn' + (item.cat === 'all' ? ' active' : '');
    btn.dataset.cat = item.cat;
    btn.innerHTML = `<i class="ti ${item.icon}"></i><span>${item.label}</span>`;
    btn.onclick = () => {
      currentCategory = item.cat;
      document.querySelectorAll('#catRow .snack-sidebar-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderProducts();
    };
    row.appendChild(btn);
  });
}

// ============================================================
//  SNACKBAR SIDEBAR
// ============================================================
function buildSnackSidebar() {
  const sidebar = document.getElementById('snackSidebar');
  if (!sidebar) return;
  sidebar.innerHTML = '';
  SNACK_SIDEBAR.forEach(item => {
    const btn = document.createElement('button');
    btn.className = 'snack-sidebar-btn' + (item.cat === currentSnackCat ? ' active' : '');
    btn.dataset.cat = item.cat;
    btn.innerHTML = `<i class="ti ${item.icon}"></i><span>${item.label}</span>`;
    btn.onclick = () => {
      currentSnackCat = item.cat;
      currentCategory = item.cat;
      document.querySelectorAll('.snack-sidebar-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderProducts();
    };
    sidebar.appendChild(btn);
  });
}

// ============================================================
//  RENDER PRODUCTS
// ============================================================
function renderProducts() {
  const grid = currentMode === 'skates'
    ? document.getElementById('prodGridSkates')
    : currentMode === 'snackbar'
      ? document.getElementById('prodGrid')
      : currentMode === 'fieldofrides'
        ? document.getElementById('prodGridField')
        : document.getElementById('prodGridDino');

  const query = document.getElementById('searchInput').value.toLowerCase().trim();
  let filtered;

  if (currentMode === 'fieldofrides') {
    filtered = phpProducts.filter(p =>
      (p.zone || '') === ZONE_FIELD_OF_RIDES &&
      p.name.toLowerCase().includes(query)
    );

  } else if (currentMode === 'dinoadventure') {
    filtered = phpProducts.filter(p =>
      (p.zone || '') === ZONE_DINO_ADVENTURE &&
      p.name.toLowerCase().includes(query)
    );

  } else if (currentMode === 'skates') {
    filtered = phpProducts.filter(p => {
      const inZone   = (p.zone || '') === ZONE_ROLLER_FEVER;
      const inCat = CATEGORIES_SKATES.includes((p.category || '').toLowerCase());
      const matchCat = currentCategory === 'all' ? true : p.category === currentCategory;
      const matchQ   = p.name.toLowerCase().includes(query);
      return inZone && inCat && matchCat && matchQ;
    });

  } else {
    filtered = phpProducts.filter(p => {
      const inZone   = (p.zone || '') === ZONE_ROLLER_FEVER;
      const inCat = CATEGORIES_SNACKBAR.includes((p.category || '').toLowerCase());
      const matchCat = currentCategory === 'all' ? true : p.category === currentCategory;
      const matchQ   = p.name.toLowerCase().includes(query);
      return inZone && inCat && matchCat && matchQ;
    });
  }

  if (filtered.length === 0) {
    grid.innerHTML = '<div style="color:#aaa;font-size:13px;padding:20px;grid-column:1/-1">No products found.</div>';
    return;
  }

  grid.innerHTML = filtered.map(p => {
    const stockNum   = parseInt(p.stock);
    const outOfStock = p.tracked && p.stock !== null && p.stock !== '' && stockNum <= 0;
    const catClass   = p.category ? p.category.replace(/\s+/g, '') : 'default';
    return `
      <div class="prod-card cat-${catClass} ${outOfStock ? 'out-of-stock' : ''}"
           ${outOfStock ? '' : `onclick="addToCart(${p.id})"`}>
        <div class="pname">${escHtml(p.name)}</div>
        <div class="pprice">${outOfStock ? '' : '₱' + parseFloat(p.price).toFixed(2)}</div>
        ${p.tracked
          ? `<div class="pstock">${outOfStock ? '❌ Out of Stock' : 'Stocks: ' + p.stock}</div>`
          : ''}
      </div>
    `;
  }).join('');
}
// ============================================================
//  PWD / SENIOR CITIZEN DISCOUNT
// ============================================================
function openPwdSeniorDiscount() {
  if (cart.length === 0) {
    alert('Cart is empty. Add items first.');
    return;
  }

  // Prepaid voucher = bayad na, walang discount na pwedeng ilagay
  if (cart.some(c => c.voucher)) {
    alert('Discounts cannot be applied to a prepaid online voucher.');
    return;
  }

  document.getElementById('pwdSeniorType').value   = specialDiscount?.type || 'Senior Citizen';
  document.getElementById('pwdSeniorName').value   = specialDiscount?.name || '';
  document.getElementById('pwdSeniorIdNum').value  = specialDiscount?.idNumber || '';
  document.getElementById('pwdSeniorError').textContent = '';

  const list = document.getElementById('pwdSeniorItemsList');
  const checkedIds = specialDiscount?.itemIds || [];

  // Isang (1) piraso lang bawat item ang covered ng PWD/Senior discount,
  // kahit ilan ang quantity na binili. Kaya per-unit price ang ipinapakita
  // dito, hindi yung buong line total, para malinaw sa cashier.
  list.innerHTML = cart.map(item => `
    <label style="display:flex;align-items:center;gap:8px;padding:6px 4px;font-size:13px;cursor:pointer;">
      <input type="checkbox" class="pwd-item-check" value="${item.id}"
             ${checkedIds.includes(item.id) ? 'checked' : ''}>
      <span style="flex:1">
        ${escHtml(item.name)}
        ${item.qty > 1 ? `<br><span style="font-size:11px;color:#aaa">1 of ${item.qty} pcs get discounts</span>` : ''}
      </span>
      <span style="color:#888">₱${item.price.toFixed(2)}</span>
    </label>
  `).join('');

  document.getElementById('pwdSeniorModal').classList.add('show');
}

function confirmPwdSeniorDiscount() {
  const name    = document.getElementById('pwdSeniorName').value.trim();
  const idNum   = document.getElementById('pwdSeniorIdNum').value.trim();
  const type    = document.getElementById('pwdSeniorType').value;
  const checked = Array.from(document.querySelectorAll('.pwd-item-check:checked')).map(c => parseInt(c.value));

  if (!name) {
    document.getElementById('pwdSeniorError').textContent = 'Please enter the ID holder\'s name.';
    return;
  }
  if (!idNum) {
    document.getElementById('pwdSeniorError').textContent = 'Please enter the ID number.';
    return;
  }
  if (checked.length === 0) {
    document.getElementById('pwdSeniorError').textContent = 'Please select at least one item.';
    return;
  }

  specialDiscount = { type, name, idNumber: idNum, itemIds: checked };
  closeModal('pwdSeniorModal');
  updateTotals();
}

function removePwdSeniorDiscount() {
  specialDiscount = null;
  updateTotals();
}

// ============================================================
//  PENDING TRANSACTIONS
// ============================================================
function holdTransaction() {
  if (cart.length === 0) {
    startFreshTransaction();
    return;
  }

  const id = Date.now();
  const custName = document.getElementById('custName').value.trim();
  pendingTransactions.push({
    id,
    cart:          JSON.parse(JSON.stringify(cart)),
    custName,
    service:       selectedService,
    payment:       selectedPayment,
    discountType,
    discountValue,
    discountAmount,
    mode:          currentMode,
  });

  persistPending();
  renderPendingPills();
  startFreshTransaction();

  const btn = document.getElementById('holdBtn');
  if (btn) {
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="ti ti-check"></i> Held!';
    btn.style.background = '#1D9E75';
    setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; }, 1200);
  }
}

function loadPending(id) {
  if (cart.length > 0 && activePendingId !== id) {
    if (!confirm('You have items in cart. Hold current transaction and load the selected one?')) return;
    holdTransaction();
  }

  const txn = pendingTransactions.find(t => t.id === id);
  if (!txn) return;

  pendingTransactions = pendingTransactions.filter(t => t.id !== id);
  persistPending();
  renderPendingPills();

  cart            = JSON.parse(JSON.stringify(txn.cart));
  discountType    = txn.discountType;
  discountValue   = txn.discountValue;
  discountAmount  = txn.discountAmount;
  activePendingId = null;

  document.getElementById('custName').value = txn.custName || '';
  selectedService = txn.service || 'Walk-In';
  selectedPayment = txn.payment || 'cash';

  document.querySelectorAll('.qs-pill').forEach(p => {
    p.classList.toggle('active', p.textContent.trim() === selectedService);
  });

  document.querySelectorAll('.pbtn').forEach(b => {
    const m = b.getAttribute('onclick')?.match(/selPay\(this,'([^']+)'\)/);
    if (m) b.classList.toggle('active', m[1] === selectedPayment);
  });

  if (txn.mode) switchMode(txn.mode);

  renderCart();
  updateTotals();
  persistCart();
}

function discardPending(id, e) {
  e.stopPropagation();
  if (!confirm('Discard this pending transaction?')) return;
  pendingTransactions = pendingTransactions.filter(t => t.id !== id);
  persistPending();
  renderPendingPills();
}

function renderPendingPills() {
  const wrap = document.getElementById('pendingPillsWrap');
  if (!wrap) return;

  if (pendingTransactions.length === 0) {
    wrap.innerHTML = '';
    return;
  }

  wrap.innerHTML =
    `<span class="pending-label">On Hold:</span>` +
    pendingTransactions.map((t, i) => {
      const label = t.custName ? escHtml(t.custName) : `Txn ${i + 1}`;
      const total = t.cart.reduce((s, c) => s + c.price * c.qty, 0).toFixed(2);
      return `
        <div class="pending-pill" onclick="loadPending(${t.id})" title="Click to resume — ₱${total}">
          <i class="ti ti-clock-pause" style="font-size:12px"></i>
          ${label} &nbsp;<span style="opacity:.7">₱${total}</span>
          <span class="ppill-x" onclick="discardPending(${t.id}, event)" title="Discard">✕</span>
        </div>
      `;
    }).join('');
}

function startFreshTransaction() {
  cart            = [];
  discountAmount  = 0;
  discountValue   = 0;
  discountType    = 'percent';
  pendingTendered = 0;
  activePendingId = null;

  document.getElementById('custName').value = '';
  document.querySelectorAll('.qs-pill').forEach((p, i) => p.classList.toggle('active', i === 0));
  selectedService = 'Walk-In';
  document.querySelectorAll('.pbtn').forEach((b, i) => b.classList.toggle('active', i === 0));
  selectedPayment = 'cash';

  renderCart();
  updateTotals();
  // FIX: dapat storageKey() ang gamit, kapareho ng persistCart()
  localStorage.removeItem(storageKey('pos_cart'));
}

function persistPending() {
  try {
    localStorage.setItem(storageKey('pos_pending'), JSON.stringify(pendingTransactions));
  } catch (e) {
    console.warn('Could not persist pending:', e);
  }
}

// ============================================================
//  CART OPERATIONS
// ============================================================
function addToCart(id) {
  // Kapag may naka-load na voucher, voucher-only ang transaction
  if (cart.some(c => c.voucher)) {
    alert('An online voucher is loaded. Finish or remove it before adding other items.');
    return;
  }

  const product = phpProducts.find(p => p.id == id);
  if (!product) return;

  const stockNum = parseInt(product.stock);

  if (product.tracked
      && product.stock !== null
      && product.stock !== ''
      && stockNum <= 0) {
    return;
  }

  const existing = cart.find(c => c.id == id);

  if (existing) {
    if (product.tracked
        && product.stock !== null
        && product.stock !== ''
        && existing.qty >= stockNum) {
      alert(`Only ${product.stock} stock available for ${product.name}.`);
      return;
    }
    existing.qty++;
  } else {
    cart.push({
      id:    product.id,
      name:  product.name,
      price: parseFloat(product.price),
      qty:   1
    });
  }

  renderCart();
  updateTotals();
  persistCart();
}

// FIX: Hindi na pwedeng bumaba sa 1 ang qty gamit ang "−" button.
// Kapag aabot sa 0, dadaan sa manager authorization (removeItem) —
// hindi na basta-basta mawawala ang item sa cart.
function changeQty(id, delta) {
  // Hindi pwedeng baguhin ang qty ng voucher line
  if (cart.some(c => c.voucher)) return;

  const item = cart.find(c => c.id == id);
  if (!item) return;

  // Quantity can never go below 1 via the "−" button.
  // To remove an item, use the X button or Correction (both need manager approval).
  if (item.qty + delta < 1) return;

  const product = phpProducts.find(p => p.id == id);

  item.qty += delta;

  if (product && product.tracked && product.stock !== null && product.stock !== '') {
    const stockNum = parseInt(product.stock);
    if (item.qty > stockNum) {
      item.qty = stockNum;
      alert(`Only ${product.stock} stock available for ${product.name}.`);
    }
  }

  renderCart();
  updateTotals();
  persistCart();
}

function removeItem(id) {
  if (cart.length === 0) return;
  pendingVoidId = id;
  pendingAction = 'void';
  document.querySelector('#authModal h3').innerHTML =
    '<i class="ti ti-lock"></i> Admin Authorization — Void Item';
  document.getElementById('authUser').value        = '';
  document.getElementById('authPass').value        = '';
  document.getElementById('authError').textContent = '';
  document.getElementById('authModal').classList.add('show');
}

function renderCart() {
  const el = document.getElementById('cartScroll');
  if (cart.length === 0) {
    el.innerHTML = '<div class="empty-cart"><i class="ti ti-shopping-cart-off"></i><br>No items added yet</div>';
    return;
  }
  el.innerHTML = cart.map((item, i) => `
    <div class="cart-row">
      <span class="item-num">${i + 1}</span>
      <div class="item-desc">
        <div class="iname">${escHtml(item.name)}</div>
        <div class="iprice">₱${item.price.toFixed(2)} each${item.voucher ? ' · PAID ONLINE' : ''}</div>
      </div>
      <div class="qty-ctrl">
        <button class="qcbtn" onclick="changeQty(${item.id}, -1)"
                ${(item.qty <= 1 || item.voucher) ? 'disabled style="opacity:.35;cursor:not-allowed;pointer-events:none;"' : ''}>−</button>
        <span class="qcnum">${item.qty}</span>
        <button class="qcbtn" onclick="changeQty(${item.id}, 1)">+</button>
      </div>
      <span class="cart-amt">₱${(item.price * item.qty).toFixed(2)}</span>
      <button class="rmv-btn" onclick="removeItem(${item.id})" title="Remove">
        <i class="ti ti-x" style="font-size:11px"></i>
      </button>
    </div>
  `).join('');
}

// ============================================================
//  TOTALS
// ============================================================
function calcTotals() {
  let gross = 0;
  let regularGross = 0;
  let specialGrossVatIncl = 0; // covers exactly 1 unit per PWD/Senior-tagged item

  cart.forEach(item => {
    const lineTotal = item.price * item.qty;
    gross += lineTotal;
    const isSpecial = specialDiscount && specialDiscount.itemIds.includes(item.id);

    if (isSpecial) {
      // Isang (1) piraso lang bawat item ang covered ng PWD/Senior discount,
      // kahit ilan ang quantity na binili. Ang natitirang (qty - 1) ay
      // regular price pa rin at may VAT (walang discount).
      specialGrossVatIncl += item.price;                    // 1 unit lang
      regularGross        += item.price * (item.qty - 1);   // natitirang qty
    } else {
      regularGross += lineTotal;
    }
  });

  const specialNetOfVat     = specialGrossVatIncl / (1 + TAX_RATE);
  const specialDiscountAmt  = specialNetOfVat * 0.20;
  const specialAfterDiscount = specialNetOfVat - specialDiscountAmt;

  let genericDiscountAmt = 0;
  if (discountType === 'percent') {
    genericDiscountAmt = regularGross * (discountValue / 100);
  } else {
    genericDiscountAmt = Math.min(discountValue, regularGross);
  }

  const regularAfterDiscount = regularGross - genericDiscountAmt;
  const regularVatableBase   = regularAfterDiscount / (1 + TAX_RATE);
  const regularVatAmount     = regularAfterDiscount - regularVatableBase;

  const totalDiscount = genericDiscountAmt + specialDiscountAmt;
  const total = regularAfterDiscount + specialAfterDiscount;

  discountAmount = totalDiscount;

  return {
    gross,
    afterDiscount: total,
    vatableBase: regularVatableBase,
    vatAmount: regularVatAmount,
    vatExemptSales: specialAfterDiscount,
    specialDiscountAmt,
    genericDiscountAmt,
    total
  };
}

function updateTotals() {
  const t = calcTotals();
  document.getElementById('grossV').textContent = '₱' + t.gross.toFixed(2);
  document.getElementById('discV').textContent  = '−₱' + (t.specialDiscountAmt + t.genericDiscountAmt).toFixed(2);
  document.getElementById('taxV').textContent   = '₱' + t.vatAmount.toFixed(2);
  document.getElementById('totalV').textContent = '₱' + t.total.toFixed(2);
}

// ============================================================
//  QUICK SERVICE
// ============================================================
function selQS(el, service) {
  selectedService = service;
  document.querySelectorAll('.qs-pill').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
}

// ============================================================
//  PAYMENT METHOD
// ============================================================
function selPay(el, method) {
  selectedPayment = method;
  document.querySelectorAll('.pbtn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
}

// ============================================================
//  CHECKOUT
// ============================================================
function updatePaymentDiscountDisplay() {
  const t = calcTotals();
  const discountTotal = t.specialDiscountAmt + t.genericDiscountAmt;
  const row = document.getElementById('discountRow');
  if (!row) return;
  if (discountTotal > 0) {
    row.style.display = '';
    document.getElementById('discountDisplay').textContent = '−₱' + discountTotal.toFixed(2);
  } else {
    row.style.display = 'none';
  }
}

function doCheckout() {
  if (dayLocked) {
    alert('Transactions are closed for today after cut-off. Please try again tomorrow.');
    return;
  }

  if (cart.length === 0) {
    alert('Cart is empty. Please add items first.');
    return;
  }

  const { total } = calcTotals();

  // === PREPAID ONLINE VOUCHER: bayad na, walang payment modal ===
  const voucherItem = cart.find(c => c.voucher);
  if (voucherItem) {
    if (Math.abs(total - voucherItem.price) > 0.01) {
      alert('Discounts cannot be applied to a prepaid voucher. Please remove the discount first.');
      return;
    }
    selectedPayment = 'online';
    pendingTendered = total;
    processCheckout(voucherItem.voucher_code);
    return;
  }
  // ==============================================================

  selectedPayment = 'cash';
  document.querySelectorAll('#payModalOpts .pay-method-btn').forEach((b, i) => b.classList.toggle('active', i === 0));
  const footerEl = document.getElementById('ptFooterMethod');
  if (footerEl) footerEl.textContent = 'Cash';

  document.getElementById('tenderedRow').style.display   = '';
  document.getElementById('cashChangeRow').style.display = '';
  document.getElementById('payKeypad').style.display     = '';
  document.getElementById('payRefGroup').style.display   = 'none';
  document.getElementById('refInput').value = '';

  updatePaymentDiscountDisplay();

  document.getElementById('cashDueDisplay').textContent = '₱' + total.toFixed(2);
  document.getElementById('cashTenderedInput').value = total.toFixed(2);

  document.getElementById('paymentModal').classList.add('show');
  updateCashChangePreview();

  const tInput = document.getElementById('cashTenderedInput');
  setTimeout(() => { tInput.focus(); tInput.select(); }, 50);
}
function selPayModal(el, method) {
  selectedPayment = method;
  document.querySelectorAll('#payModalOpts .pay-method-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');

  const isCash = method === 'cash';
  const { total } = calcTotals();

  const footerEl = document.getElementById('ptFooterMethod');
  if (footerEl) footerEl.textContent = PAYMENT_LABELS[method] || method;

  const quickAmounts = document.getElementById('quickAmounts');
  if (quickAmounts) quickAmounts.style.display = isCash ? '' : 'none';

  document.getElementById('tenderedRow').style.display   = isCash ? '' : 'none';
  document.getElementById('cashChangeRow').style.display = isCash ? '' : 'none';
  document.getElementById('keypadDigits').style.display  = isCash ? '' : 'none';
  document.getElementById('payRefGroup').style.display   = isCash ? 'none' : '';

  document.getElementById('cashTenderedInput').value = total.toFixed(2);
  if (!isCash) document.getElementById('refInput').value = '';
  updateCashChangePreview();

  if (isCash) {
    const tInput = document.getElementById('cashTenderedInput');
    setTimeout(() => { tInput.focus(); tInput.select(); }, 50);
  }
}

function keypadDigit(d) {
  const el  = document.getElementById('cashTenderedInput');
  const cur = (el.value === '0.00') ? '' : el.value;
  if (d === '.' && cur.includes('.')) return;
  el.value = cur + d;
  updateCashChangePreview();
}
function setQuickAmount(val) {
  const { total } = calcTotals();
  const amount = val === 'exact' ? total : val;
  document.getElementById('cashTenderedInput').value = amount.toFixed(2);
  updateCashChangePreview();
}

function keypadBackspace() {
  const el = document.getElementById('cashTenderedInput');
  el.value = el.value.slice(0, -1);
  updateCashChangePreview();
}

function confirmPayment() {
  const { total } = calcTotals();
  const tendered  = parseFloat(document.getElementById('cashTenderedInput').value) || 0;

  if (selectedPayment === 'cash') {
    if (tendered < total) {
      alert('Tendered amount is less than the total.');
      return;
    }
    pendingTendered = tendered;
    closeModal('paymentModal');
    processCheckout();

  } else {
    const ref = document.getElementById('refInput').value.trim();
    if (!ref) {
      alert('Please enter a reference number.');
      return;
    }
    pendingTendered = tendered >= total ? tendered : total;
    closeModal('paymentModal');
    processCheckout(ref);
  }
}

function confirmCash() {
  const tendered = parseFloat(document.getElementById('cashTenderedInput').value) || 0;
  const { total } = calcTotals();

  if (tendered < total) {
    alert('Tendered amount is less than the total.');
    return;
  }
  pendingTendered = tendered;
  closeModal('cashModal');
  processCheckout();
}

function confirmRef() {
  const ref      = document.getElementById('refInput').value.trim();
  const tendered = parseFloat(document.getElementById('tenderedInput').value) || 0;

  if (!ref) { alert('Please enter a reference number.'); return; }

  const { total } = calcTotals();
  pendingTendered = tendered >= total ? tendered : total;
  closeModal('refModal');
  processCheckout(ref);
}

function processCheckout(refNumber = null) {
  const t = calcTotals();
  const { gross, vatAmount, total } = t;
  const custName = document.getElementById('custName').value.trim() || 'Walk-in Customer';
  const invNum   = document.getElementById('invNum').textContent;

  // Kung may voucher sa cart, isasama ang code para ang server ang mag-validate/mag-redeem
  const voucherLine = cart.find(c => c.voucher);

  saveTransaction({
    invoice_number:   invNum,
    customer_name:    custName,
    service_type:     selectedService,
    payment_method:   selectedPayment,
    reference_number: refNumber || null,
    voucher_code:     voucherLine ? voucherLine.voucher_code : null,
    tendered:         pendingTendered.toFixed(2),
    gross:            gross.toFixed(2),
    discount:         discountAmount.toFixed(2),
    tax:              vatAmount.toFixed(2),
    total:            total.toFixed(2),
    // Para sa system log: hiwalay ang manual discount at PWD/Senior
    manual_discount:  t.genericDiscountAmt.toFixed(2),
    special_discount: t.specialDiscountAmt.toFixed(2),
    special_type:     specialDiscount ? specialDiscount.type : null,
    discount_mode:    discountType,
    discount_value:   discountValue,
    items:            cart
  }, gross, vatAmount, total, custName, invNum, refNumber);
}

function saveTransaction(data, gross, vatAmount, total, custName, invNum, refNumber) {
  const coBtn = document.querySelector('.co-btn');
  if (coBtn) {
    coBtn.disabled   = true;
    coBtn.innerHTML  = '<i class="ti ti-loader"></i> Saving...';
  }
  showLoading('Saving transaction…');

  fetch('/pos/save-transaction', {
    method:  'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body:    JSON.stringify(data)
  })
  .then(r => r.text())
  .then(rawText => {
    if (coBtn) {
      coBtn.disabled  = false;
      coBtn.innerHTML = '<i class="ti ti-check"></i> Checkout';
    }

    let res;
    try {
      res = JSON.parse(rawText);
    } catch (e) {
      hideLoading();
      console.error('Non-JSON server response:', rawText);
      alert('Server returned unexpected output. Check console for details.\n\n' +
            (rawText.substring(0, 300) || '(empty response)'));
      return;
    }

    if (res.success) {
      const savedInvNum = String(res.transaction_id).padStart(11, '0');
      currentInvoice = res.transaction_id + 1;
      document.getElementById('invNum').textContent = String(currentInvoice).padStart(11, '0');
      hideLoading();
      showReceipt(savedInvNum, custName, gross, vatAmount, total, refNumber);
    } else {
      hideLoading();
      if (res.locked) applyDayLock(true, res.message);
      alert('Error saving transaction: ' + (res.message || 'Unknown error'));
    }
  })
  .catch(err => {
    if (coBtn) {
      coBtn.disabled  = false;
      coBtn.innerHTML = '<i class="ti ti-check"></i> Checkout';
    }
    hideLoading();
    console.error('Fetch error:', err);
    alert('Network error. Transaction was NOT saved.\n\n' + err.message);
  });
}

// ============================================================
//  CASH TENDERED - LIVE CHANGE PREVIEW
// ============================================================
function updateCashChangePreview() {
  const tendered  = parseFloat(document.getElementById('cashTenderedInput').value) || 0;
  const { total } = calcTotals();
  const change    = tendered - total;

  document.getElementById('cashDueDisplay').textContent = '₱' + total.toFixed(2);

  const changeRow = document.getElementById('cashChangeRow');
  const changeEl   = document.getElementById('cashChangeDisplay');

  if (tendered < total) {
    changeRow.classList.add('insufficient');
    changeEl.textContent = '−₱' + Math.abs(change).toFixed(2) + ' short';
  } else {
    changeRow.classList.remove('insufficient');
    changeEl.textContent = '₱' + change.toFixed(2);
  }
}

// ============================================================
//  RECEIPT MODAL
// ============================================================
function showReceipt(invNum, custName, gross, vatAmount, total, refNumber = null) {
  // I-reset ang voided-specific UI kung nandoon pa mula sa dating void
  document.getElementById('rVoidedBanner').style.display  = 'none';
  document.getElementById('rVoidedDetails').style.display = 'none';
  document.getElementById('voidedCloseBtn').style.display = 'none';
  document.getElementById('normalCloseBtn').style.display = '';

  const now = new Date();
  const dateStr = now.toLocaleDateString('en-US', {
    month: '2-digit', day: '2-digit', year: 'numeric'
  }) + ' ' + now.toLocaleTimeString('en-US', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
  });

  const t = calcTotals();

  document.getElementById('rDateTime').textContent    = dateStr;
  document.getElementById('rInvNum').textContent       = invNum;
  document.getElementById('rOrderNum').textContent     = String(parseInt(invNum, 10)) || invNum;
  document.getElementById('rCust').textContent         = custName || 'Walk-in Customer';

  if (specialDiscount) {
    document.getElementById('rCust').textContent += ` (${specialDiscount.type}: ${specialDiscount.name}, ID# ${specialDiscount.idNumber})`;
  }

  document.getElementById('rServiceLabel').textContent = selectedService.toUpperCase();
  document.getElementById('rCashier').textContent      = (typeof userName !== 'undefined' ? userName : 'CASHIER');
  document.getElementById('rGuestCount').textContent   = cart.reduce((s, i) => s + i.qty, 0) || 1;

  document.getElementById('rItems').innerHTML =
    `<div class="receipt-item-row"><span class="item-service-label">${escHtml(selectedService.toUpperCase())}</span></div>` +
    cart.map(item => `
      <div class="receipt-item-row">
        <span>${item.qty.toFixed(2)}</span>
        <span>${escHtml(item.name)}</span>
        <span style="text-align:right">${(item.price * item.qty).toFixed(2)} V</span>
      </div>
    `).join('');

  document.getElementById('rItemCount').textContent = cart.reduce((s, i) => s + i.qty, 0).toFixed(2);
  document.getElementById('rSubTotal').textContent = total.toFixed(2);
  document.getElementById('rTotal').textContent    = total.toFixed(2);

  const discountTotal = t.specialDiscountAmt + t.genericDiscountAmt;
  const rDiscountRow = document.getElementById('rDiscountRow');
  if (rDiscountRow) {
    if (discountTotal > 0) {
      rDiscountRow.style.display = '';
      document.getElementById('rDiscount').textContent = '−' + discountTotal.toFixed(2);
    } else {
      rDiscountRow.style.display = 'none';
    }
  }

  const tendered = pendingTendered >= total ? pendingTendered : total;
  const change   = Math.max(0, tendered - total);
  document.getElementById('rTendered').textContent      = tendered.toFixed(2);
  document.getElementById('rChange').textContent        = change.toFixed(2);
  document.getElementById('rPaymentMethod').textContent =
    (PAYMENT_LABELS[selectedPayment] || selectedPayment.toUpperCase()) +
    (refNumber ? ` (Ref: ${refNumber})` : '');

  document.getElementById('rVatable').textContent   = t.vatableBase.toFixed(2);
  document.getElementById('rVatAmt').textContent    = t.vatAmount.toFixed(2);
  document.getElementById('rVatExempt').textContent = t.vatExemptSales.toFixed(2);

  document.getElementById('receiptModal').classList.add('show');
}

function closeReceiptModal() {
  document.getElementById('receiptModal').classList.remove('show');
  resetAfterCheckout();
}

function printReceipt() { window.print(); }

function resetAfterCheckout() {
  cart            = [];
  discountAmount  = 0;
  discountValue   = 0;
  discountType    = 'percent';
  pendingTendered = 0;
  activePendingId = null;
  specialDiscount = null;

  document.getElementById('custName').value = '';
  document.querySelectorAll('.qs-pill').forEach((p, i) => p.classList.toggle('active', i === 0));
  selectedService = 'Walk-In';
  document.querySelectorAll('.pbtn').forEach((b, i) => b.classList.toggle('active', i === 0));
  selectedPayment = 'cash';

  renderCart();
  updateTotals();
  localStorage.removeItem(storageKey('pos_cart')); 
  document.getElementById('invNum').textContent = String(currentInvoice).padStart(11, '0');
}

// ============================================================
//  DISCOUNT MODAL
// ============================================================
function applyDiscount() {
  document.getElementById('discountModal').classList.add('show');
}

function confirmDiscount() {
  discountType  = document.getElementById('discType').value;
  discountValue = parseFloat(document.getElementById('discValue').value) || 0;
  updateTotals();
  closeModal('discountModal');
}

function removeDiscount() {
  discountType   = 'percent';
  discountValue  = 0;
  discountAmount = 0;
  specialDiscount = null;
  const discInput = document.getElementById('discValue');
  if (discInput) discInput.value = '';
  updateTotals();
}
// ===== CASHFLOW =====
function openCashflow() {
  document.getElementById('cashflowModal').classList.add('show');
  loadCashflow();
}

function loadCashflow() {
  document.getElementById('cashflowContent').innerHTML = 'Loading...';
  showLoading('Loading cashflow…');
  fetch('/pos/get_cashflow')
    .then(r => r.json())
    .then(data => {
      hideLoading();
      const rows = Array.isArray(data.rows) ? data.rows : [];
      document.getElementById('cashflowContent').innerHTML = `
        <table style="width:100%;font-size:13px;border-collapse:collapse">
          <tr style="border-bottom:1px solid #eee">
            <th style="text-align:left;padding:5px 0">Time</th>
            <th style="text-align:left;padding:5px 0">Desc</th>
            <th style="text-align:right;padding:5px 0">In</th>
            <th style="text-align:right;padding:5px 0">Out</th>
          </tr>
          ${rows.map(r => `
            <tr style="border-bottom:1px solid #f5f5f5">
              <td style="padding:5px 0">${new Date(r.date).toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</td>
              <td style="padding:5px 0">${escHtml(r.description || '-')}</td>
              <td style="text-align:right;color:#1D9E75">${r.cash_in ? '₱'+parseFloat(r.cash_in).toFixed(2) : ''}</td>
              <td style="text-align:right;color:#E24B4A">${r.cash_out ? '₱'+parseFloat(r.cash_out).toFixed(2) : ''}</td>
            </tr>
          `).join('')}
          <tr style="font-weight:700;border-top:2px solid #ddd">
            <td colspan="2" style="padding:6px 0">Cash Sales: ₱${parseFloat(data.cash_sales||0).toFixed(2)}</td>
            <td colspan="2" style="text-align:right;color:#6022b8">Net: ₱${parseFloat(data.net||0).toFixed(2)}</td>
          </tr>
        </table>`;
    })
    .catch(() => { hideLoading(); document.getElementById('cashflowContent').textContent = 'Could not load cashflow data.'; });
}

function submitCashMovement() {
  const type = document.getElementById('cfType').value;
  const amount = parseFloat(document.getElementById('cfAmount').value);
  const desc = document.getElementById('cfDesc').value.trim();
  if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }

  showLoading('Saving entry…');
  fetch('/pos/cash-movement', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    body: JSON.stringify({ type, amount, description: desc })
  })
    .then(r => r.json())
    .then(res => {
      hideLoading();
      if (res.success) {
        document.getElementById('cfAmount').value = '';
        document.getElementById('cfDesc').value = '';
        loadCashflow();
      } else { alert('Could not save entry.'); }
    })
    .catch(() => { hideLoading(); alert('Could not save entry.'); });
}

// ===== SALES REPORT (cut-off) =====
function printSalesReport() {
  pendingAction = 'print_report';
  document.querySelector('#authModal h3').innerHTML = '<i class="ti ti-lock"></i> Manager Authorization — Cut-off Report';
  document.getElementById('authUser').value = '';
  document.getElementById('authPass').value = '';
  document.getElementById('authError').textContent = '';
  document.getElementById('authModal').classList.add('show');
}

function runCutoffReport() {
  showLoading('Generating cut-off report…');
  fetch('/pos/print-sales-report', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    body: JSON.stringify({ authorized_by: lastAuthorizedBy })
  })
    .then(r => r.json())
    .then(res => {
      hideLoading();
      if (!res.success) { alert(res.message || 'No new transactions have been made yet.'); return; }
      renderSalesReport(res, false, false);
      applyDayLock(true, 'Transactions are closed for today after cut-off. New transactions open tomorrow.');
    })
    .catch(() => { hideLoading(); alert('Server error while generating report.'); });
}

function openReport(type) {
  if (type === 'present') {
    showLoading('Loading report…');
    fetch('/pos/present-report')
      .then(r => r.json())
      .then(res => {
        hideLoading();
        if (!res.success) { alert(res.message || 'Still Empty.'); return; }
        renderSalesReport(res, true, false);
      })
      .catch(() => { hideLoading(); alert('Could not load report.'); });
  } else if (type === 'historical') {
    document.getElementById('histDate').value = new Date().toISOString().slice(0,10);
    document.getElementById('histList').innerHTML = '';
    document.getElementById('historicalModal').classList.add('show');
  } else if (type === 'bir') {
    alert('BIR Backend Reports');
  }
}

function viewHistoricalCutoff(id) {
  showLoading('Loading report…');
  fetch('/pos/cutoff/' + id)
    .then(r => r.json())
    .then(res => {
      hideLoading();
      if (!res.success) { alert('Could not load report.'); return; }
      closeModal('historicalModal');
      renderSalesReport(res, false, true);
    })
    .catch(() => { hideLoading(); alert('Could not load report.'); });
}
function searchHistorical() {
  const date = document.getElementById('histDate').value;
  const list = document.getElementById('histList');
  list.innerHTML = 'Loading...';
  showLoading('Searching cut-offs…');

  fetch('/pos/historical-cutoffs' + (date ? '?date=' + encodeURIComponent(date) : ''))
    .then(r => r.json())
    .then(res => {
      hideLoading();
      const cutoffs = Array.isArray(res.cutoffs) ? res.cutoffs : [];
      if (cutoffs.length === 0) {
        list.innerHTML = '<div style="color:#aaa;text-align:center;padding:14px;font-size:13px">No cut-off reports found for this date.</div>';
        return;
      }
      list.innerHTML = cutoffs.map(c => `
        <div onclick="viewHistoricalCutoff(${c.id})"
             style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid var(--line);border-radius:8px;margin-bottom:8px;cursor:pointer;">
          <div>
            <div style="font-weight:700;font-size:13px">Cut-off #${c.cutoff_number}</div>
            <div style="font-size:11px;color:#888">${new Date(c.period_end).toLocaleString('en-PH')}</div>
          </div>
          <i class="ti ti-chevron-right" style="color:#aaa"></i>
        </div>
      `).join('');
    })
    .catch(() => {
      hideLoading();
      list.innerHTML = '<div style="color:#E24B4A;text-align:center;padding:14px;font-size:13px">Could not load historical reports.</div>';
    });
}

// ============================================================
//  SALES REPORT RENDERING
//  Order: Overall -> Vouchers Redeemed -> Void summary/footer ->
//  Sales by Zone (Roller Fever, Snackbar, Field of Rides,
//  Dino Adventure — only zones with sales show up) -> Per-Cashier
//  breakdown.
// ============================================================
function renderSalesReport(res, isPreview, isReprint = false) {
  const r = res.report;
  const dateStr = (res.period_end ? new Date(res.period_end) : new Date()).toLocaleString('en-PH');
  const title = isPreview
    ? 'DAILY SALES SUMMARY (PREVIEW)'
    : `DAILY SALES SUMMARY — Cut-off #${res.cutoff_number || ''}`;

  let html = buildReportReceipt(
    title, dateStr, r.overall.items, r.overall.total_sales, r.overall.total_discount,
    r.overall.payment_breakdown, null, isReprint
  );

  // === VOUCHERS / ONLINE BOOKINGS REDEEMED ===
  if (r.vouchers) {
    html += buildVoucherReceipt(dateStr, r.vouchers.items, r.vouchers.count, r.vouchers.total, isReprint);
  }
  // =============================================

  html += buildReportFooter(
    res.authorized_by, res.void_count || 0, res.void_total || 0, isPreview,
    res.voided_txn_count || 0, res.voided_txn_total || 0
  );

  // === SALES BY ZONE (Roller Fever / Snackbar / Field of Rides / Dino Adventure) ===
  (r.zones || []).forEach(z => {
    html += buildReportReceipt(
      `SALES BY ZONE — ${z.zone_label}`, dateStr, z.items, z.total_sales, 0, null, null, isReprint
    );
  });
  // ===================================================================================

  r.cashiers.forEach(c => {
    html += buildReportReceipt(
      `CASHIER SALES — ${c.cashier_name}`, dateStr, c.items, c.total_sales, c.total_discount,
      c.payment_breakdown, { klook: c.klook_total, stardeals: c.stardeals_total }, isReprint
    );
  });
  document.getElementById('salesReportContent').innerHTML = html;
  document.getElementById('salesReportModal').classList.add('show');
}

function buildReportFooter(authorizedBy, voidCount, voidTotal, isPreview, voidedTxnCount = 0, voidedTxnTotal = 0) {
  return `  
    <div class="report-receipt" style="border-top:2px dashed #ccc;margin-top:6px;padding-top:6px;">
      <div class="receipt-vat-section">
        <div class="vat-row"><span>Voided Item(s)</span><span>${voidCount}</span></div>
        <div class="vat-row"><span>Voided Amount</span><span>−₱${parseFloat(voidTotal).toFixed(2)}</span></div>
        <div class="vat-row"><span>Voided Transaction(s)</span><span>${voidedTxnCount}</span></div>
        <div class="vat-row"><span>Voided Txn Amount</span><span>−₱${parseFloat(voidedTxnTotal).toFixed(2)}</span></div>
      </div>
      <hr class="dashed">
      <div style="text-align:center;font-size:12px;font-weight:700;margin-top:4px;">
        ${isPreview ? 'Prepared by' : 'Cut-off Authorized by'}: ${escHtml(authorizedBy || (typeof userName !== 'undefined' ? userName : 'N/A'))}
      </div>
    </div>`;
}

// ============================================================
//  VOUCHER / ONLINE BOOKING REDEMPTION RECEIPT BLOCK
//  Ipinapakita: ilang booking/voucher ang na-redeem sa period na ito,
//  at anong item/package ang inavail bawat isa (grouped by item name).
// ============================================================
function buildVoucherReceipt(dateStr, items, count, total, isReprint = false) {
  const itemsHtml = (items || []).map(it => `
    <div class="receipt-item-row"><span>${it.qty}</span><span>${escHtml(it.name)}</span><span style="text-align:right">₱${parseFloat(it.total).toFixed(2)}</span></div>
  `).join('');

  const reprintBanner = isReprint
    ? `<div style="text-align:center;color:#E24B4A;font-weight:800;font-size:12px;letter-spacing:1px;margin-bottom:4px;">** REPRINT **</div>`
    : '';

  return `
    <div class="report-receipt">
      <div class="receipt-header">
        ${reprintBanner}
        <div class="receipt-store">PISTAVORA AMUSEMENT PARK</div>
        <div class="receipt-title">ONLINE BOOKINGS REDEEMED</div>
      </div>
      <hr class="dashed">
      <div class="receipt-datetime">${dateStr}</div>
      <hr class="dashed">
      <div class="receipt-items-head"><span>Qty</span><span>Item</span><span style="text-align:right">Total</span></div>
      ${itemsHtml || '<div style="text-align:center;color:#aaa;font-size:12px;padding:6px 0">No vouchers redeemed.</div>'}
      <hr class="dashed">
      <div class="rt-row rt-total"><span>VOUCHERS REDEEMED</span><span>${count}</span></div>
      <div class="rt-row"><span class="rt-label">Total Amount</span><span class="rt-val">₱${parseFloat(total).toFixed(2)}</span></div>
    </div>`;
}

function buildReportReceipt(title, dateStr, items, totalSales, totalDiscount, payments, extra, isReprint = false) {
  const itemsHtml = items.map(it => `
    <div class="receipt-item-row"><span>${it.qty}</span><span>${escHtml(it.name)}</span><span style="text-align:right">₱${parseFloat(it.total).toFixed(2)}</span></div>
  `).join('');
  const payHtml = Object.entries(payments || {}).filter(([,v]) => v > 0)
    .map(([k,v]) => `<div class="vat-row"><span>${(PAYMENT_LABELS[k]||k).toUpperCase()}</span><span>₱${parseFloat(v).toFixed(2)}</span></div>`).join('');
  const extraHtml = extra ? `
    <div class="vat-row"><span>Klook</span><span>₱${parseFloat(extra.klook||0).toFixed(2)}</span></div>
    <div class="vat-row"><span>StarDeals</span><span>₱${parseFloat(extra.stardeals||0).toFixed(2)}</span></div>` : '';

  const reprintBanner = isReprint
    ? `<div style="text-align:center;color:#E24B4A;font-weight:800;font-size:12px;letter-spacing:1px;margin-bottom:4px;">** REPRINT **</div>`
    : '';

  return `
    <div class="report-receipt">
      <div class="receipt-header">
        ${reprintBanner}
        <div class="receipt-store">PISTAVORA AMUSEMENT PARK</div>
        <div class="receipt-title">${title}</div>
      </div>
      <hr class="dashed">
      <div class="receipt-datetime">${dateStr}</div>
      <hr class="dashed">
      <div class="receipt-items-head"><span>Qty</span><span>Item</span><span style="text-align:right">Total</span></div>
      ${itemsHtml}
      <hr class="dashed">
      <div class="rt-row rt-total"><span>TOTAL SALES</span><span>₱${parseFloat(totalSales).toFixed(2)}</span></div>
      <div class="rt-row"><span class="rt-label">Total Discount</span><span class="rt-val">−₱${parseFloat(totalDiscount).toFixed(2)}</span></div>
      <hr class="dashed">
      <div class="receipt-vat-section">${payHtml}${extraHtml}</div>
    </div>`;
}

// ============================================================
//  CORRECTION / VOID
// ============================================================
function openCorrection() {
  if (cart.length === 0) { alert('Cart is empty. Nothing to void.'); return; }
  pendingVoidId = null;
  pendingAction = 'correction';
  document.querySelector('#authModal h3').innerHTML =
    '<i class="ti ti-lock"></i> Admin Authorization — Correction';
  document.getElementById('authUser').value        = '';
  document.getElementById('authPass').value        = '';
  document.getElementById('authError').textContent = '';
  document.getElementById('authModal').classList.add('show');
}

function confirmAuth() {
  const username = document.getElementById('authUser').value.trim();
  const password = document.getElementById('authPass').value;

  if (!username || !password) {
    document.getElementById('authError').textContent = 'Please enter username and password.';
    return;
  }

  showLoading('Verifying…');
  fetch('/pos/verify_manager', {
    method:  'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body:    JSON.stringify({ username, password })
  })
  .then(r => r.json())
  .then(res => {
    hideLoading();
    if (res.success) {
      lastAuthorizedBy = res.manager_name || username;
      closeModal('authModal');

      if (pendingAction === 'reprint') {
        pendingAction = null;
        document.getElementById('reprintInvInput').value    = '';
        document.getElementById('reprintError').textContent = '';
        document.getElementById('reprintModal').classList.add('show');
        setTimeout(() => document.getElementById('reprintInvInput').focus(), 100);

      } else if (pendingAction === 'correction') {          // FIX: dating wala ito kaya walang nangyayari
        pendingAction = null;
        showCorrectionModal();

      } else if (pendingAction === 'void' && pendingVoidId !== null) {
        const id      = pendingVoidId;
        pendingVoidId = null;
        pendingAction = null;
        const item    = cart.find(c => c.id == id);
        if (item && confirm(`Void "${item.name}" from cart?`)) {
          logVoidToServer(item, lastAuthorizedBy);
          cart = cart.filter(c => c.id != id);
          renderCart();
          updateTotals();
          persistCart();
        }

      } else if (pendingAction === 'void_transaction' && pendingVoidTxnInvoice) {
        pendingAction = null;
        confirmVoidTransaction(lastAuthorizedBy);

      } else if (pendingAction === 'print_report') {
        pendingAction = null;
        runCutoffReport();
      }

    } else {
      document.getElementById('authError').textContent = res.message || 'Invalid credentials.';
    }
  })
  .catch(() => {
    hideLoading();
    document.getElementById('authError').textContent = 'Server error. Try again.';
  });
}

function showCorrectionModal() {
  const el = document.getElementById('correctionItems');

  // Keep whatever the cashier already typed as the reason when the list re-renders
  const prevReasonEl = document.getElementById('correctionReason');
  const prevReason   = prevReasonEl ? prevReasonEl.value : '';

  if (cart.length === 0) {
    el.innerHTML = '<div style="color:#aaa;text-align:center;padding:10px">No items in cart.</div>';
  } else {
    el.innerHTML = `
      <div class="form-group" style="margin-bottom:10px">
        <label>Reason for Void <span style="color:#E24B4A">*</span></label>
        <textarea id="correctionReason" class="form-control" rows="2"
                  placeholder="e.g. Wrong item, customer changed order...">${escHtml(prevReason)}</textarea>
      </div>
      <div id="correctionError" style="color:#E24B4A;font-size:12px;margin-bottom:8px;min-height:16px"></div>
      ${cart.map(item => `
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:8px 10px;border:1px solid #f0f0f0;border-radius:8px;margin-bottom:6px">
          <div>
            <div style="font-weight:600;font-size:13px">${escHtml(item.name)}</div>
            <div style="font-size:11px;color:#888">
              Qty: ${item.qty} &nbsp;|&nbsp; ₱${(item.price * item.qty).toFixed(2)}
            </div>
          </div>
          <button onclick="voidItem(${item.id})"
            style="background:#E24B4A;color:#fff;border:none;border-radius:6px;
                   padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer">
            <i class="ti ti-trash"></i> Void
          </button>
        </div>
      `).join('')}
    `;
  }
  document.getElementById('correctionModal').classList.add('show');
}

function voidItem(id) {
  const item = cart.find(c => c.id == id);
  if (!item) return;

  // A reason is required before any item can be voided
  const reasonEl = document.getElementById('correctionReason');
  const reason   = reasonEl ? reasonEl.value.trim() : '';
  if (!reason) {
    const errEl = document.getElementById('correctionError');
    if (errEl) errEl.textContent = 'Please enter the reason for the void first.';
    if (reasonEl) reasonEl.focus();
    return;
  }

  if (!confirm(`Void "${item.name}" from cart?\nReason: ${reason}`)) return;

  logVoidToServer(item, lastAuthorizedBy, reason);
  cart = cart.filter(c => c.id != id);
  renderCart();
  updateTotals();
  persistCart();

  if (cart.length === 0) {
    closeModal('correctionModal');
  } else {
    showCorrectionModal();
  }
}

function logVoidToServer(item, authorizedBy, reason = null) {
  fetch('/pos/log-void', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      item_name: item.name,
      price: item.price,
      qty: item.qty,
      authorized_by: authorizedBy,
      reason: reason
    })
  }).catch(err => console.warn('Void log failed:', err));
}

function openDrawer() {
  fetch('/pos/open_drawer')
    .then(() => alert('Cash drawer opened.'))
    .catch(() => alert('Could not open drawer.'));
}

// ============================================================
//  REPRINT RECEIPT
// ============================================================
function reprintReceipt() {
  document.getElementById('reprintInvInput').value    = '';
  document.getElementById('reprintError').textContent = '';
  document.getElementById('reprintModal').classList.add('show');
  setTimeout(() => document.getElementById('reprintInvInput').focus(), 100);
}

function confirmReprint() {
  const raw = document.getElementById('reprintInvInput').value.trim();
  if (!raw) {
    document.getElementById('reprintError').textContent = 'Please enter an invoice number.';
    return;
  }
  const invNum = raw.padStart(11, '0');
  document.getElementById('reprintError').textContent = '';

  const btn     = document.getElementById('reprintLookupBtn');
  btn.disabled  = true;
  btn.innerHTML = '<i class="ti ti-loader"></i> Looking up...';
  showLoading('Looking up invoice…');

  fetch('/pos/get_transaction?invoice=' + encodeURIComponent(invNum), {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
   .then(res => {
    if (!res.ok) throw new Error('Server error ' + res.status);
    return res.json();
   })
    .then(res => {
      btn.disabled  = false;
      btn.innerHTML = '<i class="ti ti-search"></i> Find &amp; Reprint';
      hideLoading();
      if (!res.success) {
        document.getElementById('reprintError').textContent = res.message || 'Invoice not found.';
        return;
      }
      closeModal('reprintModal');
      showReprintedReceipt(res.transaction);
    })
    .catch(err => {
      btn.disabled  = false;
      btn.innerHTML = '<i class="ti ti-search"></i> Find &amp; Reprint';
      hideLoading();
      document.getElementById('reprintError').textContent = err.message || 'Something went wrong.';
    });
}


function showReprintedReceipt(t) {
  
  document.getElementById('rVoidedBanner').style.display  = 'none';
  document.getElementById('rVoidedDetails').style.display = 'none';
  document.getElementById('voidedCloseBtn').style.display = 'none';
  document.getElementById('normalCloseBtn').style.display = '';

  const invNum      = String(t.invoice_number || t.transaction_id || '').padStart(11, '0');
  const gross       = parseFloat(t.gross    || 0);
  const disc        = parseFloat(t.discount || 0);
  const total       = parseFloat(t.total    || 0);
  const tendered    = parseFloat(t.tendered || 0) || total;
  const change      = Math.max(0, tendered - total);
  const vatableBase = total / (1 + TAX_RATE);
  const vatAmount   = total - vatableBase;

  document.getElementById('rDateTime').textContent     = t.created_at || '';
  document.getElementById('rInvNum').textContent        = invNum;
  document.getElementById('rOrderNum').textContent      = String(parseInt(invNum, 10));
  document.getElementById('rCust').textContent          = t.customer_name || 'Walk-in Customer';
  document.getElementById('rServiceLabel').textContent  = (t.service_type || 'WALK-IN').toUpperCase();
  document.getElementById('rCashier').textContent       = t.cashier_name || 'CASHIER';

  const items = Array.isArray(t.items) ? t.items : [];
  document.getElementById('rGuestCount').textContent = items.reduce((s, i) => s + parseFloat(i.qty || 0), 0) || 1;

  document.getElementById('rItems').innerHTML =
    `<div class="receipt-item-row"><span class="item-service-label">${escHtml((t.service_type || 'WALK-IN').toUpperCase())}</span></div>` +
    items.map(item => `
      <div class="receipt-item-row">
        <span>${parseFloat(item.qty).toFixed(2)}</span>
        <span>${escHtml(item.name || '')}</span>
        <span style="text-align:right">${(parseFloat(item.price) * parseFloat(item.qty)).toFixed(2)} V</span>
      </div>
    `).join('');

  document.getElementById('rItemCount').textContent     = items.reduce((s, i) => s + parseFloat(i.qty || 0), 0).toFixed(2);
  document.getElementById('rSubTotal').textContent      = total.toFixed(2);
  document.getElementById('rTotal').textContent         = total.toFixed(2);

  const rDiscountRow = document.getElementById('rDiscountRow');
  if (rDiscountRow) {
    if (disc > 0) {
      rDiscountRow.style.display = '';
      document.getElementById('rDiscount').textContent = '−' + disc.toFixed(2);
    } else {
      rDiscountRow.style.display = 'none';
    }
  }

  document.getElementById('rTendered').textContent      = tendered.toFixed(2);
  document.getElementById('rChange').textContent        = change.toFixed(2);
  document.getElementById('rPaymentMethod').textContent =
    (t.payment_method || 'CASH').toUpperCase() +
    (t.reference_number ? ` (Ref: ${t.reference_number})` : '');
  document.getElementById('rVatable').textContent       = vatableBase.toFixed(2);
  document.getElementById('rVatAmt').textContent        = vatAmount.toFixed(2);
  document.getElementById('rVatExempt').textContent     = '0.00';

  document.getElementById('receiptModal').classList.add('show');
}

// ============================================================
//  GENERIC MODAL CLOSE
// ============================================================
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
  }
});

// ============================================================
//  UTILITIES
// ============================================================
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function escHtml(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(String(str)));
  return d.innerHTML;
}

function persistCart() {
  try {
    localStorage.setItem(storageKey('pos_cart'), JSON.stringify(cart));
  } catch (e) {
    console.warn('Could not persist cart:', e);
  }
}
// ===== DAY LOCK (cut-off) =====
let dayLocked = false;

function checkCutoffStatus() {
  // Walang overlay dito — silent background check lang ito sa init,
  // hindi natin gustong may flash ng loading overlay pag first-load pa lang.
  fetch('/pos/cutoff-status')
    .then(r => r.json())
    .then(res => applyDayLock(res.locked, res.message))
    .catch(() => {});
}

function applyDayLock(locked, message) {
  dayLocked = !!locked;

  const banner = document.getElementById('dayLockBanner');
  const msgEl  = document.getElementById('dayLockMsg');
  const coBtn  = document.querySelector('.co-btn');

  if (banner) banner.style.display = dayLocked ? 'flex' : 'none';
  if (msgEl && message) msgEl.textContent = message;

  if (coBtn) {
    coBtn.disabled = dayLocked;
    coBtn.style.opacity = dayLocked ? '0.5' : '';
    coBtn.style.cursor  = dayLocked ? 'not-allowed' : '';
  }
}
// ============================================================
//  KEYBOARD SHORTCUTS — Options / Reports / Checkout
// ============================================================
document.addEventListener('keydown', function (e) {
  // F9 — Checkout (works anywhere, even while typing)
  if (e.key === 'F9') {
    e.preventDefault();
    doCheckout();
    return;
  }

  // Lahat ng iba pa ay Alt+Shift+<letter>
  if (!e.altKey || !e.shiftKey) return;

  const key = e.key.toLowerCase();

  const shortcutMap = {
    d: openDrawer,              // Open Drawer
    r: reprintReceipt,          // Reprint Receipt
    m: removeDiscount,          // Remove Discount
    f: openCashflow,            // Cashflow
    i: openPwdSeniorDiscount,   // PWD/Senior Discount
    p: printSalesReport,        // Print Sales Report
    e: () => openReport('present'),     // Present Reports
    h: () => openReport('historical'),  // Historical Reports
    b: () => openReport('bir'),         // BIR Backend Reports
    v: openCorrection,          // Correction
    o: openVoucher,             // Online booking voucher
  };

  if (shortcutMap[key]) {
    e.preventDefault();
    shortcutMap[key]();
  }
});

let pendingVoidTxnInvoice = null;

function openVoidTransaction() {
  document.getElementById('voidTxnInvInput').value = '';
  document.getElementById('voidTxnReason').value = '';
  document.getElementById('voidTxnError').textContent = '';
  document.getElementById('voidTxnPreview').style.display = 'none';
  document.getElementById('voidTxnModal').classList.add('show');
  setTimeout(() => document.getElementById('voidTxnInvInput').focus(), 100);
}


function lookupVoidTransaction() {
  const raw    = document.getElementById('voidTxnInvInput').value.trim();
  const reason = document.getElementById('voidTxnReason').value.trim();
 
  if (!raw) {
    document.getElementById('voidTxnError').textContent = 'Please enter an invoice number.';
    return;
  }
  if (!reason) {
    document.getElementById('voidTxnError').textContent = 'Please enter a reason for the void.';
    return;
  }
 
  const invNum = raw.padStart(11, '0');
  document.getElementById('voidTxnError').textContent = '';
 
  const btn = document.getElementById('voidTxnLookupBtn');
  btn.disabled  = true;
  btn.innerHTML = '<i class="ti ti-loader"></i> Looking up...';
  showLoading('Looking up invoice…');
 
  // purpose=void: para hindi mabilang na "reprint" sa system log ang lookup na ito
  fetch('/pos/get_transaction?purpose=void&invoice=' + encodeURIComponent(invNum), {
    headers: {
      'Accept':           'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
  })
    .then(async res => {
      // Kahit 200 status, i-guard pa rin natin sa case na naka-redirect
      // sa login page (session expired) na nagbabalik ng HTML.
      const contentType = res.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        throw new Error(
          res.status === 302 || res.redirected
            ? 'Session may have expired. Please refresh the page and log in again.'
            : `Unexpected server response (HTTP ${res.status}).`
        );
      }
      if (!res.ok) {
        const errBody = await res.json().catch(() => ({}));
        throw new Error(errBody.message || `Request failed (HTTP ${res.status}).`);
      }
      return res.json();
    })
    .then(res => {
      btn.disabled  = false;
      btn.innerHTML = '<i class="ti ti-search"></i> Find Transaction';
      hideLoading();
 
      if (!res.success) {
        document.getElementById('voidTxnError').textContent = res.message || 'Invoice not found.';
        return;
      }
 
      const t = res.transaction;
      document.getElementById('voidTxnPreview').style.display = '';
      document.getElementById('voidTxnPreview').innerHTML = `
        <strong>Invoice #${t.invoice_number}</strong><br>
        Customer: ${escHtml(t.customer_name || 'Walk-in')}<br>
        Total: ₱${parseFloat(t.total).toFixed(2)}<br>
        Payment: ${t.payment_method}<br>
        Items: ${(t.items || []).length} item(s)
      `;
 
      // I-store yung invoice number, ipasa sa auth flow
      pendingVoidTxnInvoice = raw.padStart(11, '0');
      pendingAction = 'void_transaction';
 
      document.querySelector('#authModal h3').innerHTML =
        '<i class="ti ti-lock"></i> Admin Authorization — Void Transaction';
      document.getElementById('authUser').value = '';
      document.getElementById('authPass').value = '';
      document.getElementById('authError').textContent = '';
      closeModal('voidTxnModal');
      document.getElementById('authModal').classList.add('show');
    })
    .catch(err => {
      btn.disabled  = false;
      btn.innerHTML = '<i class="ti ti-search"></i> Find Transaction';
      hideLoading();
      console.error('lookupVoidTransaction error:', err);
      document.getElementById('voidTxnError').textContent =
        err.message || 'Something went wrong.';
    });
}
 
function confirmVoidTransaction(authorizedBy) {
  const reason = document.getElementById('voidTxnReason').value.trim();

  const authBtn = document.querySelector('#authModal .mbtn.print');
  if (authBtn) authBtn.disabled = true;

  showLoading('Voiding transaction…');

  fetch('/pos/void-transaction', {
    method: 'POST',
    headers: {
      'Content-Type':     'application/json',
      'Accept':            'application/json',   
      'X-Requested-With':  'XMLHttpRequest',      
      'X-CSRF-TOKEN':      document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      invoice_number: parseInt(pendingVoidTxnInvoice, 10),
      reason:         reason,
      authorized_by:  authorizedBy
    })
  })
    .then(async res => {
      const contentType = res.headers.get('content-type') || '';

      if (!contentType.includes('application/json')) {
        if (res.status === 419) {
          throw new Error('Your session/CSRF token expired. Please refresh the page and try again.');
        }
        if (res.redirected || res.status === 302) {
          throw new Error('Request was redirected by the server (expired session or failed validation). Please refresh and try again.');
        }
        throw new Error(`Unexpected server response (HTTP ${res.status}). Void was NOT confirmed — please check before retrying.`);
      }

      if (!res.ok) {
        const errBody = await res.json().catch(() => ({}));
        const firstValidationMsg = errBody.errors
          ? Object.values(errBody.errors)[0]?.[0]
          : null;
        throw new Error(firstValidationMsg || errBody.message || `Request failed (HTTP ${res.status}).`);
      }

      return res.json();
    })
    .then(res => {
      hideLoading();
      if (authBtn) authBtn.disabled = false;

      if (res.success) {
        showVoidedReceipt(res.transaction);
      } else {
        alert('Error: ' + (res.message || 'Could not void transaction.'));
      }
      pendingVoidTxnInvoice = null;
    })
    .catch(err => {
      hideLoading();
      if (authBtn) authBtn.disabled = false;
      console.error('confirmVoidTransaction error:', err);
      alert(err.message || 'Something went wrong. Void was NOT processed.');
      pendingVoidTxnInvoice = null;
    });
}

// ============================================================
//  VOIDED TRANSACTION RECEIPT
// ============================================================
function showVoidedReceipt(t) {
  const total = parseFloat(t.total || 0);
  const disc  = parseFloat(t.discount || 0);
  const items = Array.isArray(t.items) ? t.items : [];

  document.getElementById('rVoidedBanner').style.display  = '';
  document.getElementById('rVoidedDetails').style.display = '';

  document.getElementById('rDateTime').textContent    = t.voided_at || '';
  document.getElementById('rInvNum').textContent       = t.invoice_number;
  document.getElementById('rOrderNum').textContent     = String(parseInt(t.invoice_number, 10));
  document.getElementById('rCust').textContent         = t.customer_name || 'Walk-in Customer';
  document.getElementById('rServiceLabel').textContent = (t.service_type || 'WALK-IN').toUpperCase();
  document.getElementById('rCashier').textContent      = (typeof userName !== 'undefined' ? userName : 'CASHIER');
  document.getElementById('rGuestCount').textContent   = items.reduce((s, i) => s + parseFloat(i.qty || 0), 0) || 1;

  document.getElementById('rItems').innerHTML =
    `<div class="receipt-item-row"><span class="item-service-label">${escHtml((t.service_type || 'WALK-IN').toUpperCase())}</span></div>` +
    items.map(item => `
      <div class="receipt-item-row">
        <span>${parseFloat(item.qty).toFixed(2)}</span>
        <span>${escHtml(item.name)}</span>
        <span style="text-align:right">${(parseFloat(item.price) * parseFloat(item.qty)).toFixed(2)} V</span>
      </div>
    `).join('');

  document.getElementById('rItemCount').textContent = items.reduce((s, i) => s + parseFloat(i.qty || 0), 0).toFixed(2);
  document.getElementById('rSubTotal').textContent   = total.toFixed(2);
  document.getElementById('rTotal').textContent      = total.toFixed(2);

  const rDiscountRow = document.getElementById('rDiscountRow');
  if (rDiscountRow) {
    if (disc > 0) {
      rDiscountRow.style.display = '';
      document.getElementById('rDiscount').textContent = '−' + disc.toFixed(2);
    } else {
      rDiscountRow.style.display = 'none';
    }
  }

  document.getElementById('rTendered').textContent      = total.toFixed(2);
  document.getElementById('rChange').textContent        = '0.00';
  document.getElementById('rPaymentMethod').textContent = (t.payment_method || 'CASH').toUpperCase();

  const vatableBase = total / (1 + TAX_RATE);
  const vatAmount   = total - vatableBase;
  document.getElementById('rVatable').textContent   = vatableBase.toFixed(2);
  document.getElementById('rVatAmt').textContent    = vatAmount.toFixed(2);
  document.getElementById('rVatExempt').textContent = '0.00';

  document.getElementById('rVoidReason').textContent = t.void_reason || '';
  document.getElementById('rVoidedBy').textContent    = t.voided_by || '';
  document.getElementById('rVoidedAt').textContent    = t.voided_at || '';

  // Ipakita ang "voided" close button, itago ang normal na close button
  // — para hindi ma-trigger ang resetAfterCheckout() na pwedeng
  // makabura sa kasalukuyang cart ng cashier.
  document.getElementById('voidedCloseBtn').style.display = '';
  document.getElementById('normalCloseBtn').style.display = 'none';

  document.getElementById('receiptModal').classList.add('show');
}

function closeVoidedReceiptModal() {
  document.getElementById('receiptModal').classList.remove('show');

  // I-reset lang ang voided-specific na UI state, HINDI ang cart.
  document.getElementById('rVoidedBanner').style.display  = 'none';
  document.getElementById('rVoidedDetails').style.display = 'none';
  document.getElementById('voidedCloseBtn').style.display = 'none';
  document.getElementById('normalCloseBtn').style.display = '';
}

// ============================================================
//  ONLINE BOOKING VOUCHER
//  Flow: cashier types the customer's voucher code -> POS shows
//  what was availed + amount (from the bookings table) -> Load to
//  Cart -> F9 checkout saves it as "Online" (ref = voucher code)
//  and the server marks the booking as done.
// ============================================================
let pendingVoucher = null;

function openVoucher() {
  if (dayLocked) {
    alert('Transactions are closed for today after cut-off. Please try again tomorrow.');
    return;
  }
  if (cart.length > 0) {
    alert('Cart must be empty before loading a voucher. Finish or hold the current transaction first.');
    return;
  }

  pendingVoucher = null;
  document.getElementById('voucherInput').value = '';
  document.getElementById('voucherError').textContent = '';
  document.getElementById('voucherPreview').style.display = 'none';
  document.getElementById('voucherLoadBtn').style.display = 'none';
  document.getElementById('voucherModal').classList.add('show');
  setTimeout(() => document.getElementById('voucherInput').focus(), 100);
}

function lookupVoucher() {
  const code = document.getElementById('voucherInput').value.trim();
  if (!code) {
    document.getElementById('voucherError').textContent = 'Please enter a voucher code.';
    return;
  }

  pendingVoucher = null;
  document.getElementById('voucherError').textContent = '';
  document.getElementById('voucherPreview').style.display = 'none';
  document.getElementById('voucherLoadBtn').style.display = 'none';
  showLoading('Looking up voucher…');

  fetch('/pos/voucher-lookup?code=' + encodeURIComponent(code), {
    headers: {
      'Accept':           'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(r => r.json())
    .then(res => {
      hideLoading();

      if (!res.success) {
        document.getElementById('voucherError').textContent = res.message || 'Voucher not found.';
        return;
      }

      const v = res.voucher;
      pendingVoucher = v;

      const dateWarn = v.is_today ? '' : `
        <div style="color:#E24B4A;font-weight:700;margin-top:6px">
          ⚠ Visit date is not today. Please confirm with the customer.
        </div>`;

      const box = document.getElementById('voucherPreview');
      box.style.display = '';
      box.innerHTML = `
        <strong>${escHtml(v.code)}</strong> — PAID (Online)<br>
        Customer: ${escHtml(v.customer_name || 'N/A')}<br>
        Availed: ${escHtml(v.item_name)}<br>
        Visit: ${escHtml(v.visit_date)} ${escHtml(v.visit_time || '')}<br>
        <strong>Amount: ₱${parseFloat(v.price).toFixed(2)}</strong>
        ${dateWarn}`;

      document.getElementById('voucherLoadBtn').style.display = '';
    })
    .catch(() => {
      hideLoading();
      document.getElementById('voucherError').textContent = 'Could not look up the voucher. Please try again.';
    });
}

function loadVoucherToCart() {
  if (!pendingVoucher) return;
  if (cart.length > 0) {
    alert('Cart must be empty before loading a voucher.');
    return;
  }

  removeDiscount(); // walang discount sa prepaid voucher

  cart.push({
    id:           0,                          // walang product_id, galing sa booking
    name:         pendingVoucher.item_name,
    price:        parseFloat(pendingVoucher.price),
    qty:          1,
    voucher:      true,
    voucher_code: pendingVoucher.code
  });

  if (pendingVoucher.customer_name) {
    document.getElementById('custName').value = pendingVoucher.customer_name;
  }

  selectedService = 'Reserve';
  document.querySelectorAll('.qs-pill').forEach(p => {
    p.classList.toggle('active', p.textContent.trim() === 'Reserve');
  });

  pendingVoucher = null;
  closeModal('voucherModal');
  renderCart();
  updateTotals();
  persistCart();
}