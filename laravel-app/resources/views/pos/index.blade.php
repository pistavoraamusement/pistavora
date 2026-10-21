<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pistavora System | POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>
        .app-shell {
            display: block !important;
        }

        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            background: var(--white);
            color: var(--ink);
            border: 1.5px solid var(--border);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-right: 12px;
            transition: background .15s ease, border-color .15s ease;
        }

        .back-home-btn:hover {
            background: var(--pink-pale);
            border-color: var(--pink-deep);
            color: var(--pink-dark);
        }

        .payment-box {
            width: 780px;
            max-width: 95vw;
            padding: 0;
            overflow: hidden;
        }

        .pt-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 20px 24px 16px;
        }

        .pt-header-text h3 {
            font-family: var(--font-display);
            font-size: 19px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 4px;
            display: block;
        }

        .pt-header-text p {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .pt-close-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: var(--bg);
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .15s;
        }

        .pt-close-btn:hover { background: var(--pink-light); color: var(--pink-deep); }

        .pt-section-label {
            font-size: 10px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 10px;
        }

        .pay-method-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .pay-method-btn {
            padding: 12px 14px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            border-radius: var(--radius);
            background: var(--white);
            color: var(--text-primary);
            cursor: pointer;
            border: 1.5px solid var(--border-mid);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
            transition: .15s ease;
        }

        .pay-method-btn .pmb-badge {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .pay-method-btn:hover { background: var(--pink-pale); border-color: var(--pink-light); }

        .pay-method-btn.active {
            background: var(--pink-deep);
            color: #fff;
            border-color: var(--pink-deep);
        }

        .pay-method-btn.active .pmb-badge { color: rgba(255,255,255,.75); }

        .amount-due-card {
            background: var(--pink-pale);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
            color: var(--ink);
            margin-bottom: 12px;
        }

        .amount-due-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
        }

        .amount-due-val {
            font-size: 28px;
            font-weight: 800;
            margin-top: 2px;
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
        }

        .pay-amounts { display: flex; flex-direction: column; }

        .amt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
        }

        .amt-label {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 6px;
            display: block;
        }

        .amt-val {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-primary);
        }

        .amt-input {
            width: 100%;
            text-align: right;
            font-size: 22px;
            font-weight: 800;
            border: 1.5px solid var(--pink-deep);
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            background: var(--white);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
        }

        .amt-input:focus {
            outline: none;
            border-color: var(--pink);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .ref-input {
            font-size: 14px;
            font-weight: 600;
            text-align: left;
        }

        .quick-amounts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 12px 0;
        }

        .qa-btn {
            padding: 9px 6px;
            border: 1.5px solid var(--border-mid);
            border-radius: var(--radius-sm);
            background: var(--white);
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all .12s;
            font-family: 'Inter', sans-serif;
        }

        .qa-btn:hover { background: var(--pink-light); border-color: var(--pink); color: var(--pink-dark); }

        .cash-change-card {
            background: var(--green-soft);
            border: 1px solid #99F6E4;
            border-radius: var(--radius);
            padding: 10px 14px;
            margin-top: 0;
            margin-bottom: 12px;
        }

        .cash-change-card .amt-row { padding: 0; }

        .change-val {
            color: var(--green-dark);
            font-size: 18px;
            font-weight: 800;
        }

        #cashChangeRow.insufficient .cash-change-card { background: var(--danger-soft); border-color: #FECDD3; }
        #cashChangeRow.insufficient .change-val { color: var(--danger); }

        .pay-keypad {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .keypad-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .pay-keypad button {
            padding: 14px 0;
            font-size: 16px;
            font-weight: 700;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--white);
            cursor: pointer;
            color: var(--text-primary);
        }

        .pay-keypad button:hover { background: var(--pink-pale); border-color: var(--pink-light); }

        .kp-backspace { background: var(--pink-pale); }

        .kp-confirm-full {
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            padding: 13px 0;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 4px;
        }

        .kp-confirm-full:hover { background: var(--green-dark); }

        .kp-cancel {
            background: var(--white);
            color: var(--text-secondary);
            border: 1.5px solid var(--border-mid);
            border-radius: var(--radius);
            padding: 11px 0;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }

        .kp-cancel:hover { background: var(--pink-pale); }

        .pt-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            border-top: 1px solid var(--border);
            background: var(--pink-pale);
            font-size: 12px;
            color: var(--text-muted);
        }

        .pt-footer b { color: var(--text-primary); }

        .pt-kbd {
            background: var(--white);
            border: 1px solid var(--border-mid);
            border-radius: 4px;
            padding: 1px 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
        }

        /* ═══ PAYMENT GRID — single source of truth, no duplicates ═══ */
        .pt-methods-col { grid-area: methods; }
        .pay-amounts     { grid-area: amounts; }
        .pay-keypad      { grid-area: keypad; }

        .payment-grid {
            display: grid;
            grid-template-columns: 190px 1fr 210px;
            grid-template-areas: "methods amounts keypad";
            align-items: start;
            gap: 18px;
            margin-top: 0;
            padding: 0 24px 20px;
            border-top: 1px solid var(--border);
            padding-top: 18px;
        }

        @media (max-width: 680px) {
            .payment-box { width: 95vw; }
            .payment-grid {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "methods"
                    "amounts"
                    "keypad";
                row-gap: 16px;
            }
            .pt-footer { flex-direction: column; gap: 4px; align-items: flex-start; }
        }

        /* ═══ LOADING OVERLAY ═══ */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(248, 250, 252, .85);
            backdrop-filter: blur(2px);
            z-index: 5000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 4px solid var(--pink-light);
            border-top-color: var(--pink);
            animation: spin .7s linear infinite;
        }

        .loading-text {
            font-size: .85rem;
            font-weight: 700;
            color: var(--pink-dark);
            letter-spacing: .02em;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="app-shell">
        <div class="main-content">

            <div class="pos-topbar">
                <a href="{{ url('/home') }}" class="back-home-btn"><i class="ti ti-arrow-left"></i> Back to Home</a>
                <i class="ti ti-shopping-cart"></i>
                <span class="topbar-title">Point of Sale</span>
                <span class="topbar-datetime" id="topbarTime"></span>
            </div>

            <div class="mode-navbar">
                <button class="mode-tab active" data-mode="skates" onclick="switchMode('skates')">
                    <i class="ti ti-roller-skating"></i> Skataya
                </button>
                <button class="mode-tab" data-mode="snackbar" onclick="switchMode('snackbar')">
                    <i class="ti ti-tools-kitchen-2"></i> Snackbar
                </button>
                <button class="mode-tab" data-mode="fieldofrides" onclick="switchMode('fieldofrides')">
                    <i class="ti ti-ticket"></i> PiyestaRides
                </button>
                <button class="mode-tab" data-mode="dinoadventure" onclick="switchMode('dinoadventure')">
                    <i class="ti ti-paw"></i> DinoLakbay
                </button>
                <div class="pending-pills-wrap" id="pendingPillsWrap"></div>
                <div style="padding: 0 12px; display:flex; align-items:center;" id="holdBtnWrap">
                    <button class="new-txn-btn" id="holdBtn" onclick="holdTransaction()"
                        title="Hold current transaction">
                        <i class="ti ti-player-pause"></i> New Transaction
                    </button>
                </div>
            </div>

            <div class="day-lock-banner" id="dayLockBanner">
                <i class="ti ti-lock"></i> <span id="dayLockMsg">Transactions are closed for today after cut-off.</span>
            </div>

            <div class="pos-body">

                <!-- LEFT: Product Grid -->
                <div class="left-panel">
                    <div class="search-row">
                        <i class="ti ti-search"></i>
                        <input type="text" id="searchInput" placeholder="Search item..." oninput="renderProducts()">
                    </div>
                    <div id="skatesCatWrap" class="skates-body">
                        <div class="snack-sidebar" id="catRow"></div>
                        <div class="products-grid" id="prodGridSkates"></div>
                    </div>
                    <div id="snackbarBody" class="snackbar-body" style="display:none;">
                        <div class="snack-sidebar" id="snackSidebar"></div>
                        <div class="products-grid" id="prodGrid"></div>
                    </div>
                    <div class="products-grid" id="prodGridField" style="display:none;"></div>
                    <div class="products-grid" id="prodGridDino" style="display:none;"></div>
                </div>

                <!-- RIGHT: 4 Stacked Boxes -->
                <div class="right-col">

                    <!-- BOX 1: Options | Reports -->
                    <div class="rbox rbox1">
                        <div class="rbox1-inner">
                            <div class="rbox1-col">
                                <div class="rbox1-head">Options</div>
                                <button class="qbtn green" onclick="openDrawer()">
                                    <i class="ti ti-cash-register"></i>Open Drawer
                                </button>
                                <button class="qbtn" onclick="reprintReceipt()">
                                    <i class="ti ti-printer"></i>Reprint Receipt
                                </button>
                                <button class="qbtn red" onclick="removeDiscount()">
                                    <i class="ti ti-discount-off"></i>Remove Discount
                                </button>
                                <button class="qbtn" onclick="openCashflow()">
                                    <i class="ti ti-cash"></i>Cashflow
                                </button>
                                <button class="qbtn" onclick="openPwdSeniorDiscount()">
                                    <i class="ti ti-id-badge-2"></i>Discount
                                </button>
                                <button class="qbtn green" onclick="openVoucher()" title="Online booking voucher (Alt+Shift+O)">
                                    <i class="ti ti-barcode"></i>Voucher
                                </button>
                            </div>
                            <div class="rbox1-col">
                                <div class="rbox1-head">Reports</div>
                                <button class="qbtn" onclick="printSalesReport()">
                                    <i class="ti ti-report-analytics"></i>Print Sales Report
                                </button>
                                <button class="qbtn" onclick="openReport('present')">
                                    <i class="ti ti-file-analytics"></i>Present Reports
                                </button>
                                <button class="qbtn" onclick="openReport('historical')">
                                    <i class="ti ti-history"></i>Historical Reports
                                </button>
                                <button class="qbtn" onclick="openReport('bir')">
                                    <i class="ti ti-building-bank"></i>BIR Backend Reports
                                </button>
                                <button class="qbtn red" onclick="openCorrection()">
                                    <i class="ti ti-edit"></i>Correction
                                </button>
                                <button class="qbtn red" onclick="openVoidTransaction()">
                                    <i class="ti ti-receipt-off"></i>Void Transaction
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- BOX 2: Invoice | Quick Service -->
                    <div class="rbox rbox2">
                        <div class="rbox2-inner">
                            <div class="inv-col">
                                <div class="inv-label">Invoice No.</div>
                                <div class="inv-val" id="invNum">{{ $invoice_number }}</div>
                                <div style="margin-top:10px;">
                                    <div class="inv-label">Customer Name</div>
                                    <input class="inv-input" id="custName" type="text"
                                        placeholder="Enter name...">
                                </div>
                            </div>
                            <div class="inv-col">
                                <div class="qs-label">Quick Service</div>
                                <div class="qs-pills">
                                    <div class="qs-pill active" onclick="selQS(this,'Walk-In')">Walk-In</div>
                                    <div class="qs-pill" onclick="selQS(this,'Dine-In')">Dine-In</div>
                                    <div class="qs-pill" onclick="selQS(this,'Reserve')">Reserve</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOX 3: Cart -->
                    <div class="rbox rbox3">
                        <div class="cart-head">
                            <span>#</span>
                            <span>Description</span>
                            <span style="text-align:center">Qty</span>
                            <span style="text-align:right">Amount</span>
                            <span></span>
                        </div>
                        <div class="cart-scroll" id="cartScroll">
                            <div class="empty-cart"><i class="ti ti-shopping-cart-off"></i><br>No items added yet
                            </div>
                        </div>
                    </div>

                    <!-- BOX 4: Totals | Checkout -->
                    <div class="rbox rbox4">
                        <div class="rbox4-inner">
                            <div class="totals-col">
                                <div class="t-row"><span>Gross:</span><span id="grossV">₱0.00</span></div>
                                <div class="t-row"><span>Discount:</span><span id="discV"
                                        style="color:var(--danger);">−₱0.00</span></div>
                                <div class="t-row"><span>Tax (12% VAT):</span><span id="taxV">₱0.00</span></div>
                                <div class="t-row grand"><span>Sub-Total:</span><span class="gval"
                                        id="totalV">₱0.00</span></div>
                            </div>
                            <div class="totals-col"
                                style="display:flex;flex-direction:column;justify-content:flex-end;">
                                <div class="pay-head" style="opacity:.6;">Press Checkout to select payment method
                                </div>
                                <button class="co-btn" onclick="doCheckout()">
                                    <i class="ti ti-check"></i>Checkout
                                    <span class="co-btn-kbd">F9</span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text" id="loadingText">Processing…</div>
    </div>

    <!-- RECEIPT MODAL -->
    <div class="modal-overlay" id="receiptModal">
        <div class="modal-box receipt-box">
            <div class="receipt-header">
                <div id="rVoidedBanner" style="display:none;text-align:center;color:var(--danger);font-weight:800;font-size:14px;letter-spacing:1px;margin-bottom:6px;border:2px dashed var(--danger);padding:4px;">
                    ** VOIDED **
                </div>
                <div class="receipt-store">PISTAVORA AMUSEMENT PARK</div>
                <div class="receipt-owned">Owned &amp; Optd. by:</div>
                <div class="receipt-owned">PISTAVORA AMUSEMENT PARK</div>
                <div class="receipt-address">Lower Deck, Building J., The Outlets at Lipa<br>Lima Estates, San Lucas,
                    City of Lipa, Batangas</div>
                <div class="receipt-tin">VAT Reg. TIN : 010-412-741-00006</div>
                <div class="receipt-min">MIN : 240903083555678750</div>
                <div class="receipt-min">MSN : 8500324000925</div>
                <div class="receipt-title">SALES INVOICE</div>
            </div>
            <hr class="dashed">
            <div class="receipt-meta">
                <div class="meta-row"><span class="meta-label">No. of Guest:</span><span class="meta-val"
                        id="rGuestCount">1</span></div>
                <div class="meta-row"><span class="meta-label">Order #:</span><span class="meta-val"
                        id="rOrderNum"></span></div>
                <div class="meta-row"><span class="meta-label">Invoice #:</span><span class="meta-val"
                        id="rInvNum"></span></div>
                <div class="meta-row">
                    <span class="meta-label">Term #: 1&nbsp;&nbsp;&nbsp;Cshr: <span
                            id="rCashier">CASHIER</span></span>
                </div>
                <div style="font-weight:700;font-size:12px;margin-top:3px" id="rServiceLabel">WALK-IN</div>
            </div>
            <hr class="dashed">
            <div class="receipt-items-head">
                <span>Qty</span>
                <span>Description(s)</span>
                <span style="text-align:right">Price</span>
            </div>
            <div id="rItems"></div>
            <div style="font-size:11px;color:#666;text-align:center;margin:4px 0;font-family:'Courier New',monospace">
                -------- <span id="rItemCount">0</span> item(s) --------
            </div>
            <div class="receipt-totals-section">
                <div class="rt-row"><span class="rt-label">Sub Total</span><span class="rt-val"
                        id="rSubTotal">0.00</span></div>
                <div class="rt-row" id="rDiscountRow" style="display:none;"><span class="rt-label">Discount</span><span class="rt-val"
                        id="rDiscount">0.00</span></div>
            </div>
            <hr class="solid">
            <div class="receipt-totals-section">
                <div class="rt-row rt-total"><span>TOTAL</span><span id="rTotal">0.00</span></div>
                <div class="rt-row rt-tendered">
                    <span class="rt-label">Tendered:<br><span id="rPaymentMethod"
                            style="font-weight:700">CASH</span></span>
                    <span class="rt-val" id="rTendered">0.00</span>
                </div>
                <div class="rt-row rt-change" style="font-weight:700">
                    <span class="rt-label">Change:</span>
                    <span class="rt-val" id="rChange">0.00</span>
                </div>
            </div>
            <hr class="dashed">
            <div class="receipt-vat-section">
                <div class="vat-row"><span>VATable Sales(V)</span><span id="rVatable">0.00</span></div>
                <div class="vat-row"><span>VAT Amount</span><span id="rVatAmt">0.00</span></div>
                <div class="vat-row"><span>VAT Exempt Sales(E)</span><span id="rVatExempt">0.00</span></div>
                <div class="vat-row"><span>Zero-Rated Sales(Z)</span><span>0.00</span></div>
            </div>
            <hr class="dashed">
            <div class="receipt-datetime" id="rDateTime"></div>
            <div class="receipt-buyer-section">
                <div class="buyer-row"><span class="blabel">Name :</span><span class="bval" id="rCust"></span>
                </div>
                <div class="buyer-row"><span class="blabel">Address :</span><span class="bval"></span></div>
                <div class="buyer-row"><span class="blabel">TIN :</span><span class="bval"></span></div>
                <div class="buyer-row"><span class="blabel">Business Style :</span><span class="bval"></span></div>
            </div>
            <div class="receipt-machine-section">
                <div class="machine-company">Syntax Squad</div>
                <div>Balintawak rd</div>
                <div>Lipa City, Batangas</div>
                <div>VAT Reg. TIN : 006-737-173-00000</div>
                <div>BIR Accr. No.: 046-006737173-000611</div>
                <div>Date Issued: 03/01/2013&nbsp;&nbsp;Valid Until: 07/31/2025</div>
                <br>
                <div>PTU No : FP092024-059-0465680-0090989</div>
                <div>Date Issued : 09/10/2024</div>
            </div>
            <hr class="dashed">
            <div id="rVoidedDetails" style="display:none;text-align:left;font-size:11px;color:var(--danger);border-top:1px dashed var(--danger);padding-top:6px;margin-top:6px;">
                <div><strong>Void Reason:</strong> <span id="rVoidReason"></span></div>
                <div><strong>Voided By:</strong> <span id="rVoidedBy"></span></div>
                <div><strong>Voided At:</strong> <span id="rVoidedAt"></span></div>
            </div>
            <div class="receipt-thank">This serves as a SALES INVOICE</div>
            <div class="receipt-thank">Thank you... Come Again...</div>
            <div class="modal-buttons">
                <button class="mbtn print" onclick="printReceipt()"><i class="ti ti-printer"></i> Print</button>
                <button class="mbtn close-btn" onclick="closeVoidedReceiptModal()" id="voidedCloseBtn" style="display:none;"><i class="ti ti-x"></i> Close</button>
                <button class="mbtn close-btn" onclick="closeReceiptModal()" id="normalCloseBtn"><i class="ti ti-x"></i> Close</button>
            </div>
        </div>
    </div>

    <!-- DISCOUNT MODAL -->
    <div class="modal-overlay" id="discountModal">
        <div class="modal-box small-box">
            <h3>Apply Discount</h3>
            <div class="form-group">
                <label>Discount Type</label>
                <select id="discType" class="form-control">
                    <option value="percent">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (₱)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Value</label>
                <input type="number" id="discValue" class="form-control" placeholder="e.g. 10" min="0">
            </div>
            <div class="modal-buttons">
                <button class="mbtn print" onclick="confirmDiscount()">Apply</button>
                <button class="mbtn close-btn" onclick="closeModal('discountModal')">Cancel</button>
            </div>
        </div>
    </div>

    <!-- PWD/SENIOR DISCOUNT MODAL -->
    <div class="modal-overlay" id="pwdSeniorModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-id-badge-2"></i> PWD / Senior Citizen Discount</h3>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:10px">
                Select the item(s) covered by this ID, then enter the holder's details.
                (20% discount + 12% VAT exemption per RA 9994 / RA 10754)
            </p>
            <div class="form-group">
                <label>Discount Type</label>
                <select id="pwdSeniorType" class="form-control">
                    <option value="Senior Citizen">Senior Citizen</option>
                    <option value="PWD">PWD</option>
                </select>
            </div>
            <div class="form-group">
                <label>ID Holder Name</label>
                <input type="text" id="pwdSeniorName" class="form-control" placeholder="Full name on ID">
            </div>
            <div class="form-group">
                <label>ID Number</label>
                <input type="text" id="pwdSeniorIdNum" class="form-control" placeholder="e.g. SC-2026-00123">
            </div>
            <div class="form-group">
                <label>Covered Item(s)</label>
                <div id="pwdSeniorItemsList"
                    style="max-height:180px;overflow-y:auto;border:1px solid var(--line);border-radius:8px;padding:8px;">
                </div>
            </div>
            <div id="pwdSeniorError" style="color:var(--danger);font-size:12px;margin-bottom:8px;min-height:16px"></div>
            <div class="modal-buttons">
                <button class="mbtn print" onclick="confirmPwdSeniorDiscount()"><i class="ti ti-check"></i>
                    Apply</button>
                <button class="mbtn close-btn" onclick="closeModal('pwdSeniorModal')"><i class="ti ti-x"></i>
                    Cancel</button>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL -->
    <div class="modal-overlay" id="paymentModal">
        <div class="modal-box payment-box">
            <div class="pt-header">
                <div class="pt-header-text">
                    <h3>Payment Tender</h3>
                    <p>Select tender method and confirm transaction</p>
                </div>
                <button type="button" class="pt-close-btn" onclick="closeModal('paymentModal')">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <div class="payment-grid">
                <div class="pt-methods-col">
                    <div class="pt-section-label">Payment Method</div>
                    <div class="pay-method-list" id="payModalOpts">
                        <div class="pay-method-btn active" onclick="selPayModal(this,'cash')">
                            <span>Cash</span><span class="pmb-badge">PHP</span>
                        </div>
                        <div class="pay-method-btn" onclick="selPayModal(this,'card')">
                            <span>Card</span><span class="pmb-badge">Terminal</span>
                        </div>
                        <div class="pay-method-btn" onclick="selPayModal(this,'gcash')">
                            <span>GCash</span><span class="pmb-badge">QR</span>
                        </div>
                        <div class="pay-method-btn" onclick="selPayModal(this,'maya')">
                            <span>Maya</span><span class="pmb-badge">Wallet</span>
                        </div>
                        <div class="pay-method-btn" onclick="selPayModal(this,'stardeals')">
                            <span>StarDeals</span><span class="pmb-badge">Promo</span>
                        </div>
                        <div class="pay-method-btn" onclick="selPayModal(this,'klook')">
                            <span>Klook</span><span class="pmb-badge">Voucher</span>
                        </div>
                        <div class="pay-method-btn" onclick="selPayModal(this,'online')">
                            <span>Online</span><span class="pmb-badge">Online</span>
                        </div>
                    </div>
                </div>

                <div class="pay-amounts">
                    <div class="amount-due-card">
                        <div class="amount-due-label">Amount Due</div>
                        <div class="amount-due-val" id="cashDueDisplay">₱0.00</div>
                    </div>

                    <div class="amt-row" id="discountRow" style="display:none;">
                        <span class="amt-label" style="margin-bottom:0">Discount</span>
                        <span class="amt-val" id="discountDisplay" style="color:var(--danger);">−₱0.00</span>
                    </div>

                    <div id="tenderedRow">
                        <span class="amt-label">Tendered</span>
                        <input type="text" id="cashTenderedInput" class="amt-input" inputmode="decimal"
                            autocomplete="off" placeholder="0.00" value="0.00" oninput="updateCashChangePreview()"
                            onfocus="this.select()" onkeydown="if(event.key==='Enter') confirmPayment()">
                    </div>

                    <div class="amt-row" id="payRefGroup" style="display:none;flex-direction:column;align-items:stretch;">
                        <span class="amt-label">Reference No.</span>
                        <input type="text" id="refInput" class="amt-input ref-input" placeholder="Enter ref #">
                    </div>

                    <div class="quick-amounts" id="quickAmounts">
                        <button type="button" class="qa-btn" onclick="setQuickAmount('exact')">Exact</button>
                        <button type="button" class="qa-btn" onclick="setQuickAmount(500)">₱500</button>
                        <button type="button" class="qa-btn" onclick="setQuickAmount(1000)">₱1,000</button>
                        <button type="button" class="qa-btn" onclick="setQuickAmount(2000)">₱2,000</button>
                    </div>

                    <div class="cash-change-card" id="cashChangeRow">
                        <div class="amt-row">
                            <span class="amt-label" style="margin-bottom:0">Change</span>
                            <span class="amt-val change-val" id="cashChangeDisplay">₱0.00</span>
                        </div>
                    </div>

                    <button type="button" class="kp-confirm-full" onclick="confirmPayment()">
                        <i class="ti ti-check"></i> Confirm Payment
                    </button>
                    <button type="button" class="kp-cancel" style="margin-top:8px" onclick="closeModal('paymentModal')">
                        Cancel
                    </button>
                </div>

                <div class="pay-keypad" id="payKeypad">
                    <div class="keypad-grid" id="keypadDigits">
                        <button type="button" onclick="keypadDigit('7')">7</button>
                        <button type="button" onclick="keypadDigit('8')">8</button>
                        <button type="button" onclick="keypadDigit('9')">9</button>
                        <button type="button" onclick="keypadDigit('4')">4</button>
                        <button type="button" onclick="keypadDigit('5')">5</button>
                        <button type="button" onclick="keypadDigit('6')">6</button>
                        <button type="button" onclick="keypadDigit('1')">1</button>
                        <button type="button" onclick="keypadDigit('2')">2</button>
                        <button type="button" onclick="keypadDigit('3')">3</button>
                        <button type="button" onclick="keypadDigit('.')">.</button>
                        <button type="button" onclick="keypadDigit('0')">0</button>
                        <button type="button" class="kp-backspace" onclick="keypadBackspace()" title="Backspace"><i
                                class="ti ti-backspace"></i></button>
                    </div>
                </div>
            </div>

            <div class="pt-footer">
                <span>Payment Method: <b id="ptFooterMethod">Cash</b></span>
                <span>Press <span class="pt-kbd">Enter</span> to confirm payment</span>
            </div>
        </div>
    </div>

    <!-- CASHFLOW MODAL -->
    <div class="modal-overlay" id="cashflowModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-cash"></i> Cashflow</h3>
            <div style="display:flex; gap:6px; margin-bottom:8px;">
                <select id="cfType" class="form-control" style="flex:1">
                    <option value="in">Cash In</option>
                    <option value="out">Cash Out</option>
                </select>
                <input type="number" id="cfAmount" class="form-control" placeholder="Amount" style="flex:1">
            </div>
            <input type="text" id="cfDesc" class="form-control" placeholder="Description (optional)"
                style="margin-bottom:8px">
            <button class="mbtn print" style="margin-bottom:12px" onclick="submitCashMovement()">
                <i class="ti ti-plus"></i> Add Entry
            </button>
            <div id="cashflowContent">Loading...</div>
            <div class="modal-buttons">
                <button class="mbtn close-btn" onclick="closeModal('cashflowModal')">Close</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="salesReportModal">
        <div class="modal-box" style="width:380px;">
            <div id="salesReportContent"></div>
            <div class="modal-buttons">
                <button class="mbtn print" onclick="window.print()"><i class="ti ti-printer"></i> Print</button>
                <button class="mbtn close-btn" onclick="closeModal('salesReportModal')"><i class="ti ti-x"></i>
                    Close</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="historicalModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-history"></i> Historical Reports</h3>
            <div class="form-group">
                <label>Date</label>
                <input type="date" id="histDate" class="form-control">
            </div>
            <button class="mbtn print" style="margin-bottom:12px" onclick="searchHistorical()">
                <i class="ti ti-search"></i> Search
            </button>
            <div id="histList"></div>
            <div class="modal-buttons">
                <button class="mbtn close-btn" onclick="closeModal('historicalModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- VOUCHER MODAL (online booking voucher) -->
    <div class="modal-overlay" id="voucherModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-barcode"></i> Online Booking Voucher</h3>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">
                Enter the voucher code from the customer's booking receipt.
            </p>
            <div class="form-group">
                <label>Voucher Code</label>
                <input type="text" id="voucherInput" class="form-control" style="text-transform:uppercase"
                    placeholder="Enter voucher code" autocomplete="off"
                    onkeydown="if(event.key==='Enter') lookupVoucher()">
            </div>
            <div id="voucherPreview"
                style="display:none;margin-bottom:10px;padding:10px;background:var(--bg);border-radius:8px;font-size:12px;"></div>
            <div id="voucherError" style="color:var(--danger);font-size:12px;margin-bottom:8px;min-height:16px"></div>
            <div class="modal-buttons">
                <button class="mbtn print" id="voucherFindBtn" onclick="lookupVoucher()">
                    <i class="ti ti-search"></i> Find Voucher
                </button>
                <button class="mbtn print" id="voucherLoadBtn" onclick="loadVoucherToCart()" style="display:none;">
                    <i class="ti ti-check"></i> Load to Cart
                </button>
                <button class="mbtn close-btn" onclick="closeModal('voucherModal')">
                    <i class="ti ti-x"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- AUTH MODAL -->
    <div class="modal-overlay" id="authModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-lock"></i> Manager Authorization</h3>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">TL or Manager credentials required.</p>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="authUser" class="form-control" placeholder="Enter username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="authPass" class="form-control" placeholder="Enter password">
            </div>
            <div id="authError" style="color:var(--danger);font-size:12px;margin-bottom:8px;min-height:16px"></div>
            <div class="modal-buttons">
                <button class="mbtn print" onclick="confirmAuth()">
                    <i class="ti ti-check"></i> Authorize
                </button>
                <button class="mbtn close-btn" onclick="closeModal('authModal')">
                    <i class="ti ti-x"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- CORRECTION MODAL -->
    <div class="modal-overlay" id="correctionModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-trash"></i> Void Item</h3>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Select item to void from cart:</p>
            <div id="correctionItems" style="max-height:220px;overflow-y:auto;margin-bottom:12px"></div>
            <div class="modal-buttons">
                <button class="mbtn close-btn" onclick="closeModal('correctionModal')">
                    <i class="ti ti-x"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- REPRINT RECEIPT MODAL (was missing — reprintReceipt() in pos.js needs these ids) -->
    <div class="modal-overlay" id="reprintModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-printer"></i> Reprint Receipt</h3>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">
                Enter the invoice number of the receipt you want to reprint.
            </p>
            <div class="form-group">
                <label>Invoice Number</label>
                <input type="text" id="reprintInvInput" class="form-control"
                    placeholder="e.g. 14988 or 00000014988" autocomplete="off"
                    onkeydown="if(event.key==='Enter') confirmReprint()">
            </div>
            <div id="reprintError" style="color:var(--danger);font-size:12px;margin-bottom:8px;min-height:16px"></div>
            <div class="modal-buttons">
                <button class="mbtn print" id="reprintLookupBtn" onclick="confirmReprint()">
                    <i class="ti ti-search"></i> Find &amp; Reprint
                </button>
                <button class="mbtn close-btn" onclick="closeModal('reprintModal')">
                    <i class="ti ti-x"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- VOID TRANSACTION MODAL -->
    <div class="modal-overlay" id="voidTxnModal">
        <div class="modal-box small-box">
            <h3><i class="ti ti-receipt-off"></i> Void Transaction</h3>
            <p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">
                Enter the invoice number of the completed transaction to void.
                This requires Manager/TL authorization and will reflect in the sales report.
            </p>
            <div class="form-group">
                <label>Invoice Number</label>
                <input type="text" id="voidTxnInvInput" class="form-control"
                    placeholder="e.g. 14988 or 00000014988">
            </div>
            <div id="voidTxnPreview" style="display:none;margin-bottom:10px;padding:10px;background:var(--bg);border-radius:8px;font-size:12px;"></div>
            <div class="form-group">
                <label>Reason for Void</label>
                <textarea id="voidTxnReason" class="form-control" rows="2" placeholder="e.g. Wrong item, customer changed order..."></textarea>
            </div>
            <div id="voidTxnError" style="color:var(--danger);font-size:12px;margin-bottom:8px;min-height:16px"></div>
            <div class="modal-buttons">
                <button class="mbtn print" id="voidTxnLookupBtn" onclick="lookupVoidTransaction()">
                    <i class="ti ti-search"></i> Find Transaction
                </button>
                <button class="mbtn close-btn" onclick="closeModal('voidTxnModal')">
                    <i class="ti ti-x"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Pass PHP data to JS via data attributes -->
    <div id="posData" data-products="{{ json_encode($products) }}" data-start-invoice="{{ $last_inv }}"
        data-user-role="{{ session('role') }}" data-user-name="{{ $cashier_name }}"
        data-current-user-id="{{ auth()->check() ? auth()->id() : session('user_id', $cashier_name) }}"
        style="display:none;"></div>

    <script>
        const posDataEl = document.getElementById('posData');
        const phpProducts = JSON.parse(posDataEl.dataset.products);
        const startInvoice = Number(posDataEl.dataset.startInvoice);
        const userRole = posDataEl.dataset.userRole;
        const userName = posDataEl.dataset.userName;
        const currentUserId = posDataEl.dataset.currentUserId;
    </script>
    <script src="{{ asset('js/pos.js') }}"></script>

</body>

</html>