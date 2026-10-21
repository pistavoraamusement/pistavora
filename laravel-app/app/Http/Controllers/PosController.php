<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemLog;
use App\Services\SystemLogger;

class PosController extends Controller
{
    // ================= SYSTEM LOG HELPER =================
    // Single place that calls SystemLogger::record().
    // The app uses sessions (not Laravel Auth), so user_id and email are
    // passed manually. The "actor" is always the logged-in cashier. If another
    // person is involved (e.g. a manager who authorized something), they go in
    // $context. Wrapped in try/catch so the POS never breaks if logging fails.
    private function audit(
        string $eventType,
        string $description,
        string $severity = 'info',
        array $context = [],
        bool $isAnomaly = false
    ): void {
        try {
            $sessionId = session('user_id');
            $uid = is_numeric($sessionId) ? (int) $sessionId : null;

            SystemLogger::record(
                $eventType,
                $description,
                $severity,
                $context,
                $isAnomaly,
                $uid,
                session('email'),
                'pos'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SystemLogger failed (POS): ' . $e->getMessage());
        }
    }

    // Only accepts an "authorized_by" that matches the manager who was recently
    // verified in verifyManager() during this session. If it doesn't match, the
    // name sent by the browser may be fake.
    private function overrideMatches(?string $authorizedBy): bool
    {
        $o = session('pos_override');
        if (!$o || !$authorizedBy) {
            return false;
        }

        $ttl = (int) config('system_log.pos_override_ttl_minutes', 15) * 60;

        return (now()->timestamp - (int) ($o['at'] ?? 0)) <= $ttl
            && strcasecmp(trim((string) ($o['name'] ?? '')), trim($authorizedBy)) === 0;
    }

    // Name of the manager whose override is verified and not yet expired in this
    // session. Null if there is no active override. Used to record manual discounts.
    private function activeOverrideName(): ?string
    {
        $o = session('pos_override');
        if (!$o) {
            return null;
        }

        $ttl = (int) config('system_log.pos_override_ttl_minutes', 15) * 60;

        return (now()->timestamp - (int) ($o['at'] ?? 0)) <= $ttl
            ? (string) ($o['name'] ?? '')
            : null;
    }

    // Repeated voids by the same cashier = anomaly. One alert per window.
    private function checkRepeatedVoids(): void
    {
        try {
            $sessionId = session('user_id');
            if (!is_numeric($sessionId)) {
                return;
            }
            $uid = (int) $sessionId;

            $window    = (int) config('system_log.pos_repeat_window_minutes', 60);
            $threshold = (int) config('system_log.pos_repeat_threshold', 3);

            $count = SystemLog::where('user_id', $uid)
                ->whereIn('event_type', ['item_voided', 'transaction_voided'])
                ->where('created_at', '>=', now()->subMinutes($window))
                ->count();

            if ($count >= $threshold && Cache::add("syslog:pos:voids:{$uid}", 1, now()->addMinutes($window))) {
                $this->audit(
                    'repeated_voids',
                    "{$count} voids by the same cashier within {$window} minutes.",
                    'critical',
                    ['count' => $count, 'window_minutes' => $window],
                    true
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Repeat-void check failed (POS): ' . $e->getMessage());
        }
    }

    // Repeated manual discounts by the same cashier = anomaly. One alert per window.
    private function checkRepeatedManualDiscounts(): void
    {
        try {
            $sessionId = session('user_id');
            if (!is_numeric($sessionId)) {
                return;
            }
            $uid = (int) $sessionId;

            $window    = (int) config('system_log.pos_repeat_window_minutes', 60);
            $threshold = (int) config('system_log.pos_discount_repeat_threshold', 5);

            $count = SystemLog::where('user_id', $uid)
                ->where('event_type', 'manual_discount')
                ->where('created_at', '>=', now()->subMinutes($window))
                ->count();

            if ($count >= $threshold && Cache::add("syslog:pos:discounts:{$uid}", 1, now()->addMinutes($window))) {
                $this->audit(
                    'repeated_manual_discounts',
                    "{$count} manual discounts by the same cashier within {$window} minutes.",
                    'critical',
                    ['count' => $count, 'window_minutes' => $window],
                    true
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Repeat-discount check failed (POS): ' . $e->getMessage());
        }
    }

    // Receipt reprint: always logged; flagged as an anomaly when repeated or
    // when the invoice is already voided.
    private function logReprint(\stdClass $txn): void
    {
        try {
            $invoiceRef = 'Inv #' . str_pad($txn->transaction_id, 11, '0', STR_PAD_LEFT);

            $previous = SystemLog::where('event_type', 'receipt_reprinted')
                ->where('description', 'like', "Reprinted receipt for {$invoiceRef} %")
                ->count();
            $reprintNo = $previous + 1;

            $threshold = (int) config('system_log.pos_reprint_anomaly_count', 3);
            $flags = [];
            if ($reprintNo >= $threshold) {
                $flags[] = 'repeated reprint';
            }
            if ($txn->transaction_status === 'Voided') {
                $flags[] = 'invoice is voided';
            }

            $this->audit(
                'receipt_reprinted',
                "Reprinted receipt for {$invoiceRef} (reprint #{$reprintNo})"
                    . ($flags ? ' — ' . implode(', ', $flags) : ''),
                $flags ? 'warning' : 'info',
                [
                    'transaction_id'      => (int) $txn->transaction_id,
                    'original_cashier_id' => isset($txn->cashier_id) ? (int) $txn->cashier_id : null,
                    'same_cashier'        => isset($txn->cashier_id) && (int) $txn->cashier_id === (int) session('user_id'),
                    'reprint_no'     => $reprintNo,
                    'status'         => $txn->transaction_status,
                    'total'          => (float) $txn->total_amount,
                ],
                !empty($flags)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Reprint log failed (POS): ' . $e->getMessage());
        }
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->select(
                'products.product_id',
                'products.product_name',
                'products.price',
                'products.stock',
                'products.status',
                'categories.category_id',
                'categories.category_name',
                'categories.zone',
                'categories.is_stock_tracked'
            )
            ->orderBy('categories.zone')
            ->orderBy('categories.category_name')
            ->orderBy('products.product_name')
            ->get()
            ->map(fn($row) => [
                'id'          => (int) $row->product_id,
                'name'        => $row->product_name,
                'price'       => (float) $row->price,
                'category'    => strtolower($row->category_name),
                'category_id' => (int) $row->category_id,
                'zone'        => strtolower($row->zone),
                'tracked'     => (bool) $row->is_stock_tracked,
                'stock'       => $row->stock,
                'status'      => $row->status,
            ])->toArray();

        $last_inv = DB::table('sales_transactions')->max('transaction_id');
        $last_inv = $last_inv ? intval($last_inv) + 1 : 1;
        $invoice_number = str_pad($last_inv, 11, '0', STR_PAD_LEFT);

        $cashier_name = session('username') ?? session('fullname') ?? 'CASHIER';

        return view('pos.index', compact('products', 'last_inv', 'invoice_number', 'cashier_name'));
    }

    public function verifyManager(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::table('users')->where('username', $data['username'])->first();

        if (!$user) {
            // LOG: unknown username on manager override
            $this->audit(
                'login_failed',
                'Failed manager override on POS: unknown username',
                'warning',
                [
                    'reason'   => 'pos_manager_override_unknown_username',
                    'username' => mb_substr($data['username'], 0, 100),
                ]
            );
            SystemLogger::checkBruteForce($request->ip(), session('email'));

            return response()->json(['success' => false, 'message' => 'Invalid credentials.']);
        }

        if ($user->status !== 'active') {
            // LOG: a deactivated account was used for the override
            $this->audit(
                'manager_override_denied',
                "Attempted POS override with a deactivated account: {$user->username}",
                'warning',
                ['reason' => 'inactive', 'target_user_id' => $user->user_id, 'target_role' => $user->role]
            );

            return response()->json(['success' => false, 'message' => 'Account is inactive.']);
        }

        if (!Hash::check($data['password'], $user->password)) {
            // LOG: wrong password on manager override (counted by the brute-force check)
            $this->audit(
                'login_failed',
                "Failed manager override on POS: wrong password for {$user->username}",
                'warning',
                [
                    'reason'         => 'pos_manager_override_wrong_password',
                    'target_user_id' => $user->user_id,
                    'target_role'    => $user->role,
                ]
            );
            SystemLogger::checkBruteForce($request->ip(), session('email'));

            return response()->json(['success' => false, 'message' => 'Invalid credentials.']);
        }

        if (!in_array($user->role, ['tl', 'admin'])) {
            // LOG: correct password but the user is not a TL/Manager
            $this->audit(
                'manager_override_denied',
                "{$user->username} ({$user->role}) tried to override on POS but is not a TL/Manager",
                'warning',
                ['reason' => 'not_authorized_role', 'target_user_id' => $user->user_id, 'target_role' => $user->role]
            );

            return response()->json(['success' => false, 'message' => 'Not authorized. TL or Manager only.']);
        }

        // Remember in the session which manager was verified (used by overrideMatches())
        session(['pos_override' => [
            'name'    => $user->fullname ?? $user->username,
            'user_id' => $user->user_id,
            'at'      => now()->timestamp,
        ]]);

        // LOG: successful manager override (the manager is tied to the next void/discount)
        $this->audit(
            'manager_override_granted',
            ($user->fullname ?? $user->username) . " ({$user->role}) authorized an override on POS",
            'info',
            ['authorizer_user_id' => $user->user_id, 'authorizer_role' => $user->role]
        );

        return response()->json([
            'success' => true,
            'manager_name' => $user->fullname ?? $user->username,
        ]);
    }

    // Item-level void from the cart (Correction / X button).
    // Now accepts an optional "reason".
    public function logVoid(Request $request)
    {
        $data = $request->validate([
            'item_name'     => 'required|string',
            'price'         => 'required|numeric',
            'qty'           => 'required|integer|min:1',
            'authorized_by' => 'nullable|string',
            'reason'        => 'nullable|string|max:255',
        ]);

        $row = [
            'item_name'     => $data['item_name'],
            'price'         => $data['price'],
            'qty'           => $data['qty'],
            'amount'        => $data['price'] * $data['qty'],
            'cashier_name'  => session('username') ?? session('fullname'),
            'authorized_by' => $data['authorized_by'] ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        // Save the reason only if the column exists (see the migration note).
        if (Schema::hasColumn('voided_items', 'reason')) {
            $row['reason'] = $data['reason'] ?? null;
        }

        DB::table('voided_items')->insert($row);

        // A manager name that doesn't match the verified one is suspicious
        $verified = $this->overrideMatches($data['authorized_by'] ?? null);
        $claimedButUnverified = !empty($data['authorized_by']) && !$verified;

        // LOG: void of a single cart item
        $this->audit(
            'item_voided',
            "Voided item: {$data['item_name']} x{$data['qty']} (₱" . number_format($data['price'] * $data['qty'], 2) . ')'
                . (!empty($data['reason']) ? " — Reason: {$data['reason']}" : '')
                . ($claimedButUnverified ? ' — manager override not verified' : ''),
            'warning',
            [
                'item_name'         => mb_substr($data['item_name'], 0, 150),
                'price'             => (float) $data['price'],
                'qty'               => (int) $data['qty'],
                'amount'            => (float) ($data['price'] * $data['qty']),
                'reason'            => $data['reason'] ?? null,
                'authorized_by'     => $data['authorized_by'] ?? null,
                'override_verified' => $verified,
            ],
            $claimedButUnverified
        );

        $this->checkRepeatedVoids();

        return response()->json(['success' => true]);
    }

    public function saveTransaction(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated. Please log in again.'], 401);
        }

        if ($this->isDayLocked()) {
            return response()->json([
                'success' => false,
                'locked'  => true,
                'message' => 'Transactions are closed for today after cut-off. Please try again tomorrow.',
            ]);
        }

        $data = $request->validate([
            'customer_name'    => 'nullable|string',
            'service_type'     => 'nullable|string',
            'payment_method'   => 'nullable|string',
            'reference_number' => 'nullable|string',
            'voucher_code'     => 'nullable|string|max:30',
            'gross'            => 'required|numeric',
            'discount'         => 'nullable|numeric',
            'tax'              => 'nullable|numeric',
            'total'            => 'required|numeric',
            'tendered'         => 'nullable|numeric',
            'vatable_sales'        => 'nullable|numeric',
            'vat_exempt_sales'     => 'nullable|numeric',
            'pwd_senior_type'      => 'nullable|string|in:Senior Citizen,PWD',
            'pwd_senior_name'      => 'nullable|string',
            'pwd_senior_id_number' => 'nullable|string',
            // For the system log: manual discount and PWD/Senior are tracked separately
            'manual_discount'  => 'nullable|numeric|min:0',
            'special_discount' => 'nullable|numeric|min:0',
            'special_type'     => 'nullable|string|max:30',
            'discount_mode'    => 'nullable|string|max:20',
            'discount_value'   => 'nullable|numeric',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|integer',
            'items.*.name'     => 'required|string',
            'items.*.price'    => 'required|numeric',
            'items.*.qty'      => 'required|integer|min:1',
        ]);

        $cashierId    = (int) session('user_id');
        $customerName = trim($data['customer_name'] ?? '') ?: 'Walk-in Customer';
        $serviceType  = trim($data['service_type'] ?? 'Walk-In');

        $paymentMap = [
            'cash'      => 'Cash',
            'card'      => 'Card',
            'gcash'     => 'GCash',
            'maya'      => 'Maya',
            'stardeals' => 'StarDeals',
            'klook'     => 'Klook',
            'online'    => 'Online',
        ];
        $rawPayment    = strtolower(trim($data['payment_method'] ?? 'cash'));
        $paymentMethod = $paymentMap[$rawPayment] ?? ucfirst($rawPayment);

        $referenceNumber = !empty($data['reference_number']) ? trim($data['reference_number']) : null;

        // === PWD/Senior + VAT breakdown ===
        $vatableSales   = $data['vatable_sales']    ?? null;
        $vatExemptSales = $data['vat_exempt_sales'] ?? null;
        $pwdSeniorType  = $data['pwd_senior_type']      ?? null;
        $pwdSeniorName  = $data['pwd_senior_name']      ?? null;
        $pwdSeniorIdNum = $data['pwd_senior_id_number'] ?? null;
        // ===================================

        $gross        = (float) ($data['gross']    ?? 0);
        $discount     = (float) ($data['discount'] ?? 0);
        $vat          = (float) ($data['tax']      ?? 0);
        $total        = (float) ($data['total']    ?? 0);
        $cashReceived = (float) ($data['tendered'] ?? $total);
        $changeAmount = max(0, $cashReceived - $total);
        $subtotal     = $gross - $discount;
        $items        = $data['items'];

        // === PREPAID ONLINE BOOKING VOUCHER ===
        // The server is the authority here: the item, price, and total are taken
        // from the bookings table using the voucher code, not from what the
        // browser sent. So the cashier/browser cannot change the amount.
        $voucherCode    = !empty($data['voucher_code']) ? strtoupper(trim($data['voucher_code'])) : null;
        $voucherBooking = null;

        if ($voucherCode) {
            $voucherBooking = DB::table('bookings')->where('voucher_code', $voucherCode)->first();

            if (!$voucherBooking) {
                // LOG: voucher code that does not exist (possible code guessing)
                $this->audit(
                    'voucher_invalid',
                    "Voucher code not found at checkout: {$voucherCode}",
                    'warning',
                    ['voucher_code' => $voucherCode, 'reason' => 'not_found', 'stage' => 'checkout']
                );

                return response()->json(['success' => false, 'message' => 'Voucher not found.']);
            }
            if ($voucherBooking->status !== 'confirmed') {
                // LOG: already-used or invalid voucher
                $this->audit(
                    'voucher_invalid',
                    "Attempted to use invalid voucher {$voucherCode} (status: {$voucherBooking->status})",
                    'warning',
                    [
                        'voucher_code' => $voucherCode,
                        'reason'       => 'status_' . $voucherBooking->status,
                        'booking_id'   => $voucherBooking->id,
                        'stage'        => 'checkout',
                    ]
                );

                return response()->json(['success' => false, 'message' => 'Voucher is already used or no longer valid.']);
            }

            $info = app(\App\Http\Controllers\User\BookingController::class)
                ->packageInfo($voucherBooking->service, $voucherBooking->package);

            $vPrice = round((float) $voucherBooking->price, 2);

            $items = [[
                'id'    => 0,
                'name'  => $info['service_name'] . ' — ' . $info['package_name'] . ' (' . $voucherBooking->tier . ' pax)',
                'price' => $vPrice,
                'qty'   => 1,
            ]];

            $subtotal        = $vPrice;
            $discount        = 0.0;
            $total           = $vPrice;
            $vatableSales    = round($vPrice / 1.12, 2);
            $vat             = round($vPrice - $vatableSales, 2);
            $vatExemptSales  = 0;
            $pwdSeniorType   = null;
            $pwdSeniorName   = null;
            $pwdSeniorIdNum  = null;
            $paymentMethod   = 'Online';
            $referenceNumber = $voucherCode;
            $cashReceived    = $vPrice;
            $changeAmount    = 0;
        }
        // ======================================

        try {
            $transactionId = DB::transaction(function () use (
                $cashierId,
                $customerName,
                $serviceType,
                $subtotal,
                $discount,
                $vat,
                $total,
                $paymentMethod,
                $referenceNumber,
                $cashReceived,
                $changeAmount,
                $items,
                $vatableSales,
                $vatExemptSales,
                $pwdSeniorType,
                $pwdSeniorName,
                $pwdSeniorIdNum,
                $voucherBooking
            ) {
                // Re-check + lock the voucher inside the transaction so it cannot
                // be double-redeemed even if two cashiers check out at the same time.
                if ($voucherBooking) {
                    $locked = DB::table('bookings')
                        ->where('id', $voucherBooking->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$locked || $locked->status !== 'confirmed') {
                        throw new \RuntimeException('Voucher is already used or no longer valid.');
                    }
                }

                $transactionId = DB::table('sales_transactions')->insertGetId([
                    'cashier_id'         => $cashierId,
                    'customer_name'      => $customerName,
                    'service_type'       => $serviceType,
                    'subtotal'           => $subtotal,
                    'discount_amount'    => $discount,
                    'vat_amount'         => $vat,
                    'total_amount'       => $total,
                    'payment_method'     => $paymentMethod,
                    'reference_number'   => $referenceNumber,
                    'cash_received'      => $cashReceived,
                    'change_amount'      => $changeAmount,
                    'transaction_status' => 'Completed',
                    'vatable_sales'        => $vatableSales,
                    'vat_exempt_sales'     => $vatExemptSales,
                    'pwd_senior_type'      => $pwdSeniorType,
                    'pwd_senior_name'      => $pwdSeniorName,
                    'pwd_senior_id_number' => $pwdSeniorIdNum,
                    'created_at'         => now(),
                ]);

                foreach ($items as $item) {
                    $price = (float) $item['price'];
                    $qty   = (int)   $item['qty'];

                    DB::table('transaction_items')->insert([
                        'transaction_id' => $transactionId,
                        // id = 0 is the voucher line (no product in the products table) → NULL
                        'product_id'     => ((int) $item['id']) ?: null,
                        'product_name'   => trim($item['name']),
                        'price'          => $price,
                        'quantity'       => $qty,
                        'subtotal'       => $price * $qty,
                    ]);

                    // Deduct stock for products in categories where
                    // is_stock_tracked = 1 in the categories table.
                    // Zone/category-agnostic: new categories added from
                    // Inventory are picked up automatically.
                    DB::table('products')
                        ->where('product_id', (int) $item['id'])
                        ->whereIn('category_id', function ($q) {
                            $q->select('category_id')->from('categories')->where('is_stock_tracked', 1);
                        })
                        ->whereNotNull('stock')
                        ->where('stock', '>', 0)
                        ->decrement('stock', $qty);
                }

                // Mark the voucher as redeemed (booking status = done)
                if ($voucherBooking) {
                    DB::table('bookings')
                        ->where('id', $voucherBooking->id)
                        ->update([
                            'status'                  => 'done',
                            'redeemed_at'             => now(),
                            'redeemed_by'             => $cashierId,
                            'redeemed_transaction_id' => $transactionId,
                            'updated_at'              => now(),
                        ]);
                }

                return $transactionId;
            });
        } catch (\Throwable $e) {
            // LOG: saving the transaction failed (e.g. voucher double-redeem)
            $this->audit(
                'transaction_failed',
                'Failed to save transaction' . ($voucherCode ? " (voucher {$voucherCode})" : ''),
                'warning',
                [
                    'error'        => mb_substr($e->getMessage(), 0, 200),
                    'voucher_code' => $voucherCode,
                    'total'        => $total,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to save transaction: ' . $e->getMessage(),
            ], 500);
        }

        // LOG: voucher redeemed (old status → new status)
        $invoiceRef = 'Inv #' . str_pad($transactionId, 11, '0', STR_PAD_LEFT);

        if ($voucherBooking) {
            $this->audit(
                'voucher_redeemed',
                "Redeemed voucher {$voucherCode} on {$invoiceRef} (₱" . number_format($total, 2) . ')',
                'info',
                [
                    'voucher_code'   => $voucherCode,
                    'booking_id'     => $voucherBooking->id,
                    'transaction_id' => $transactionId,
                    'amount'         => $total,
                    'old'            => ['booking_status' => 'confirmed'],
                    'new'            => ['booking_status' => 'done'],
                ]
            );
        }

        // LOG: discounts (vouchers excluded, their discount is always 0).
        // Manual discount (typed by the cashier) and PWD/Senior are logged separately.
        // Only the PWD/Senior type is logged, never the name or ID number.
        if ($discount > 0) {
            $manual   = round((float) ($data['manual_discount']  ?? 0), 2);
            $special  = round((float) ($data['special_discount'] ?? 0), 2);
            $specType = $data['special_type'] ?? null;
            $mode     = $data['discount_mode'] ?? null;
            $value    = isset($data['discount_value']) ? (float) $data['discount_value'] : null;
            $pct      = ($gross > 0 && $manual > 0) ? round(($manual / $gross) * 100, 2) : 0;
            $maxPct   = (float) config('system_log.pos_discount_anomaly_percent', 25);

            // Breakdown doesn't match the total discount = the request may have been tampered with
            $mismatch = abs(($manual + $special) - $discount) > 0.05;

            $flags = [];
            if ($manual > 0 && $pct >= $maxPct) { $flags[] = "{$pct}% of gross"; }
            if ($mismatch)                      { $flags[] = 'discount breakdown does not match total'; }
            $flagText = $flags ? ' — ' . implode(', ', $flags) : '';

            if ($manual > 0) {
                // Manual discount: the cashier typed the amount.
                // Records whether a manager override (verifyManager) was active at the time.
                $overrideBy   = $this->activeOverrideName();
                $overrideText = $overrideBy ? " (manager override: {$overrideBy})" : '';

                $this->audit(
                    'manual_discount',
                    "Manual discount of ₱" . number_format($manual, 2) . " ({$pct}% of gross) on {$invoiceRef}{$overrideText}{$flagText}",
                    'warning',
                    [
                        'manager_override' => $overrideBy,
                        'transaction_id' => $transactionId,
                        'gross'          => $gross,
                        'manual'         => $manual,
                        'manual_pct'     => $pct,
                        'mode'           => $mode,
                        'value_typed'    => $value,
                        'discount_total' => $discount,
                        'total'          => $total,
                        'payment_method' => $paymentMethod,
                    ],
                    !empty($flags)
                );

                $this->checkRepeatedManualDiscounts();
            }

            if ($special > 0) {
                // PWD/Senior: type only, never the name or ID number
                $this->audit(
                    'pwd_senior_discount',
                    ($specType ?: 'PWD/Senior') . " discount of ₱" . number_format($special, 2) . " on {$invoiceRef}"
                        . ($manual <= 0 ? $flagText : ''),
                    'info',
                    [
                        'transaction_id'  => $transactionId,
                        'special'         => $special,
                        'pwd_senior_type' => $specType,
                        'total'           => $total,
                    ],
                    ($manual <= 0 && !empty($flags))
                );
            }

            if ($manual <= 0 && $special <= 0) {
                // Discount without a breakdown: the browser JS is outdated, or the request was tampered with
                $this->audit(
                    'discount_applied',
                    "Discount of ₱" . number_format($discount, 2) . " on {$invoiceRef} — no breakdown received",
                    'warning',
                    ['transaction_id' => $transactionId, 'discount' => $discount, 'gross' => $gross, 'total' => $total],
                    true
                );
            }
        }

        return response()->json([
            'success'        => true,
            'transaction_id' => $transactionId,
            'message'        => 'Transaction saved successfully.',
        ]);
    }

    public function getTransaction(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $invoice = $request->query('invoice');
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice number required.']);
        }

        $transactionId = (int) $invoice;

        $txn = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->where('t.transaction_id', $transactionId)
            ->select('t.*', 'u.fullname as cashier_name')
            ->first();

        if (!$txn) {
            return response()->json(['success' => false, 'message' => 'Invoice #' . $invoice . ' not found.']);
        }

        // The void flow also uses this lookup (?purpose=void).
        $isVoidLookup = $request->query('purpose') === 'void';

        // Void lookup: not allowed once the invoice is included in a printed
        // sales report (cut-off). Only present sales can be voided.
        if ($isVoidLookup && !empty($txn->cutoff_id)) {
            $this->audit(
                'void_blocked',
                'Blocked void attempt on Inv #' . str_pad($transactionId, 11, '0', STR_PAD_LEFT)
                    . ' — already included in a printed sales report',
                'warning',
                ['transaction_id' => $transactionId, 'cutoff_id' => (int) $txn->cutoff_id, 'stage' => 'lookup'],
                true
            );

            return response()->json([
                'success' => false,
                'message' => 'This invoice is already included in a printed sales report and can no longer be voided.',
            ]);
        }

        if ($isVoidLookup && $txn->transaction_status === 'Voided') {
            return response()->json(['success' => false, 'message' => 'This transaction is already voided.']);
        }

        // LOG: receipt reprint. Skipped for void lookups.
        if (!$isVoidLookup) {
            $this->logReprint($txn);
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $transactionId)
            ->get()
            ->map(fn($i) => [
                'name'  => $i->product_name,
                'price' => (float) $i->price,
                'qty'   => (int)   $i->quantity,
            ])->toArray();

        return response()->json([
            'success'     => true,
            'transaction' => [
                'transaction_id'   => $txn->transaction_id,
                'invoice_number'   => str_pad($txn->transaction_id, 11, '0', STR_PAD_LEFT),
                'customer_name'    => $txn->customer_name,
                'service_type'     => $txn->service_type,
                'cashier_name'     => $txn->cashier_name ?? session('username'),
                'payment_method'   => $txn->payment_method,
                'reference_number' => $txn->reference_number ?? null,
                'gross'            => (float) $txn->subtotal + (float) $txn->discount_amount,
                'discount'         => (float) $txn->discount_amount,
                'total'            => (float) $txn->total_amount,
                'tendered'         => (float) ($txn->cash_received ?? $txn->total_amount),
                'created_at'       => $txn->created_at,
                'items'            => $items,
                'vatable_sales'        => (float) ($txn->vatable_sales ?? ($txn->total_amount / 1.12)),
                'vat_exempt_sales'     => (float) ($txn->vat_exempt_sales ?? 0),
                'pwd_senior_type'      => $txn->pwd_senior_type ?? null,
                'pwd_senior_name'      => $txn->pwd_senior_name ?? null,
                'pwd_senior_id_number' => $txn->pwd_senior_id_number ?? null,
            ],
        ]);
    }

    // ================= ONLINE BOOKING VOUCHER LOOKUP =================
    // The POS looks up a booking by voucher code and returns what was
    // availed (service/package/pax) and how much it costs.
    public function lookupVoucher(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $code = strtoupper(trim((string) $request->query('code', '')));
        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Please enter a voucher code.']);
        }

        $b = DB::table('bookings as b')
            ->leftJoin('users as u', 'u.user_id', '=', 'b.user_id')
            ->where('b.voucher_code', $code)
            ->select('b.*', 'u.fullname as customer_name')
            ->first();

        if (!$b) {
            // LOG: voucher code not found on lookup (possible code guessing)
            $this->audit(
                'voucher_invalid',
                'Voucher code not found on lookup: ' . mb_substr($code, 0, 30),
                'warning',
                ['voucher_code' => mb_substr($code, 0, 30), 'reason' => 'not_found', 'stage' => 'lookup']
            );

            return response()->json(['success' => false, 'message' => 'Voucher code not found.']);
        }
        if ($b->status === 'done') {
            // LOG: tried to use an already-redeemed voucher again
            $this->audit(
                'voucher_invalid',
                "Attempted lookup of already-redeemed voucher {$code}",
                'warning',
                ['voucher_code' => $code, 'reason' => 'already_redeemed', 'booking_id' => $b->id, 'stage' => 'lookup']
            );

            return response()->json(['success' => false, 'message' => 'This voucher was already redeemed.']);
        }
        if ($b->status !== 'confirmed') {
            return response()->json(['success' => false, 'message' => 'Voucher is not valid (status: ' . $b->status . ').']);
        }

        $info = app(\App\Http\Controllers\User\BookingController::class)
            ->packageInfo($b->service, $b->package);

        $visitDate = \Illuminate\Support\Carbon::parse($b->visit_date);

        return response()->json([
            'success' => true,
            'voucher' => [
                'code'          => $b->voucher_code,
                'customer_name' => $b->customer_name,
                'item_name'     => $info['service_name'] . ' — ' . $info['package_name'] . ' (' . $b->tier . ' pax)',
                'price'         => (float) $b->price,
                'visit_date'    => $visitDate->format('M j, Y'),
                'visit_time'    => $b->visit_time,
                'is_today'      => $visitDate->isToday(),
            ],
        ]);
    }

    // ================= CASHFLOW =================
    public function getCashflow(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        // total_amount is the correct column in sales_transactions (not "total")
        $cashSales = DB::table('sales_transactions')
            ->whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $movementsIn = DB::table('cash_movements')
            ->whereDate('created_at', $date)->where('type', 'in')->sum('amount');
        $movementsOut = DB::table('cash_movements')
            ->whereDate('created_at', $date)->where('type', 'out')->sum('amount');

        $movements = DB::table('cash_movements')
            ->whereDate('created_at', $date)->orderByDesc('created_at')->get();

        return response()->json([
            'rows' => $movements->map(fn($m) => [
                'date' => $m->created_at,
                'description' => $m->description,
                'cash_in'  => $m->type === 'in'  ? $m->amount : 0,
                'cash_out' => $m->type === 'out' ? $m->amount : 0,
            ]),
            'cash_sales' => $cashSales,
            'net' => ($cashSales + $movementsIn) - $movementsOut,
        ]);
    }

    public function addCashMovement(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:191',
        ]);

        DB::table('cash_movements')->insert([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'user_id' => session('user_id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // LOG: cash going into or out of the drawer
        $this->audit(
            'cash_movement',
            ($request->type === 'in' ? 'Cash in' : 'Cash out') . ' of ₱' . number_format((float) $request->amount, 2),
            $request->type === 'out' ? 'warning' : 'info',
            [
                'type'        => $request->type,
                'amount'      => (float) $request->amount,
                'description' => mb_substr((string) $request->description, 0, 191),
            ]
        );

        return response()->json(['success' => true]);
    }

    // ================= SHARED NOTIFICATION HELPER ================
    // Same pattern as InventoryController::notify(). Wrapped in try/catch so a
    // broadcast failure never breaks the main action. $message may be multi-line (\n).
    private function notify(string $title, string $message, ?string $url = null, string $type = 'pos')
    {
        try {
            $notif = \App\Models\Notification::create([
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'url'     => $url ?: route('pos.index'),
            ]);
            broadcast(new \App\Events\NewSystemNotification($notif));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification/broadcast failed: ' . $e->getMessage());
        }
    }

    // ================= SALES REPORT HELPERS =================
    private function getItemsByTransaction(\Illuminate\Support\Collection $transactionIds)
    {
        // Uses leftJoin (not join) to products/categories so items still appear
        // in the report even if their product or category was deleted; they
        // fall into the "Other" zone.
        return DB::table('transaction_items as ti')
            ->leftJoin('products as p', 'p.product_id', '=', 'ti.product_id')
            ->leftJoin('categories as c', 'c.category_id', '=', 'p.category_id')
            ->whereIn('ti.transaction_id', $transactionIds)
            ->select(
                'ti.*',
                DB::raw('LOWER(c.zone) as item_zone'),
                DB::raw('LOWER(c.category_name) as item_category')
            )
            ->get()
            ->groupBy('transaction_id')
            ->map(function ($items) {
                return $items->map(fn($i) => [
                    'name'     => $i->product_name,
                    'price'    => (float) $i->price,
                    'qty'      => (int)   $i->quantity,
                    'zone'     => $i->item_zone ?: 'other',
                    'category' => $i->item_category ?: '',
                ])->toArray();
            })->toArray();
    }

    // Item-level voids only. Old "Full Txn Void" rows are excluded because a
    // full-transaction void is now counted as a voided transaction instead
    // (otherwise it would be counted twice).
    // $cutoffId = null → present report (not yet cut off); otherwise that cut-off.
    private function getVoidedItems(?int $cutoffId = null)
    {
        $q = DB::table('voided_items')->where('item_name', 'not like', '%(Full Txn Void%');

        return $cutoffId === null
            ? $q->whereNull('cutoff_id')->get()
            : $q->where('cutoff_id', $cutoffId)->get();
    }

    // Fully voided transactions (for the present report or a specific cut-off).
    private function getVoidedTransactions(?int $cutoffId = null)
    {
        $q = DB::table('sales_transactions')->where('transaction_status', 'Voided');

        return $cutoffId === null
            ? $q->whereNull('cutoff_id')->get()
            : $q->where('cutoff_id', $cutoffId)->get();
    }

    // ================= VOUCHER REDEMPTION SUMMARY =================
    // Counts how many bookings/vouchers were redeemed in the transactions
    // included in the report, and which item was availed. Based on
    // bookings.redeemed_transaction_id (not payment_method), so the count stays
    // correct however payment methods are configured in the future.
    private function getVoucherRedemptions(\Illuminate\Support\Collection $transactionIds)
    {
        $bookings = DB::table('bookings')
            ->whereIn('redeemed_transaction_id', $transactionIds)
            ->get();

        $items = [];
        $total = 0;

        foreach ($bookings as $b) {
            $info = app(\App\Http\Controllers\User\BookingController::class)
                ->packageInfo($b->service, $b->package);

            $name = $info['service_name'] . ' — ' . $info['package_name'] . ' (' . $b->tier . ' pax)';

            if (!isset($items[$name])) {
                $items[$name] = ['name' => $name, 'qty' => 0, 'total' => 0];
            }
            $items[$name]['qty']   += 1;
            $items[$name]['total'] += (float) $b->price;
            $total += (float) $b->price;
        }

        return [
            'count' => $bookings->count(),
            'items' => array_values($items),
            'total' => $total,
        ];
    }

    private function buildSalesReport(\Illuminate\Support\Collection $transactions, array $itemsByTxn)
    {
        // === VOUCHER/BOOKING SUMMARY ===
        $vouchers = $this->getVoucherRedemptions($transactions->pluck('transaction_id'));
        // ================================

        $overallItems = [];
        $overallTotal = 0;
        $overallDiscount = 0;
        $overallPayments = ['cash' => 0, 'card' => 0, 'gcash' => 0, 'maya' => 0, 'stardeals' => 0, 'klook' => 0, 'online' => 0];
        $byCashier = [];

        // Fixed order/labels so zones always appear in a consistent order in the
        // report, even if one has no sales (empty ones are filtered out later).
        // Zones come straight from categories.zone in the database.
        $byZone = [
            'skataya'      => ['zone_label' => 'Skataya',      'items' => [], 'total_sales' => 0],
            'snackbar'     => ['zone_label' => 'Snackbar',     'items' => [], 'total_sales' => 0],
            'piyestarides' => ['zone_label' => 'Piyestarides', 'items' => [], 'total_sales' => 0],
            'dinolakbay'   => ['zone_label' => 'Dinolakbay',   'items' => [], 'total_sales' => 0],
        ];

        foreach ($transactions as $t) {
            $items = $itemsByTxn[$t->transaction_id] ?? [];
            $cashierName = $t->cashier_name ?? 'Unknown';

            if (!isset($byCashier[$cashierName])) {
                $byCashier[$cashierName] = [
                    'cashier_name' => $cashierName,
                    'items' => [],
                    'total_sales' => 0,
                    'total_discount' => 0,
                    'klook_total' => 0,
                    'stardeals_total' => 0,
                    'payment_breakdown' => ['cash' => 0, 'card' => 0, 'gcash' => 0, 'maya' => 0, 'stardeals' => 0, 'klook' => 0, 'online' => 0],
                ];
            }

            foreach ($items as $it) {
                $name = $it['name'];
                $qty = $it['qty'];
                $line = $it['price'] * $it['qty'];

                // Each category now has its own zone in the DB (Snackbar included),
                // so no extra splitting is needed.
                $zoneKey = $it['zone'] ?? 'other';

                if (!isset($overallItems[$name])) $overallItems[$name] = ['name' => $name, 'qty' => 0, 'total' => 0];
                $overallItems[$name]['qty'] += $qty;
                $overallItems[$name]['total'] += $line;

                if (!isset($byCashier[$cashierName]['items'][$name])) {
                    $byCashier[$cashierName]['items'][$name] = ['name' => $name, 'qty' => 0, 'total' => 0];
                }
                $byCashier[$cashierName]['items'][$name]['qty'] += $qty;
                $byCashier[$cashierName]['items'][$name]['total'] += $line;

                // === ZONE BREAKDOWN ===
                if (!isset($byZone[$zoneKey])) {
                    $byZone[$zoneKey] = ['zone_label' => ucwords($zoneKey), 'items' => [], 'total_sales' => 0];
                }
                if (!isset($byZone[$zoneKey]['items'][$name])) {
                    $byZone[$zoneKey]['items'][$name] = ['name' => $name, 'qty' => 0, 'total' => 0];
                }
                $byZone[$zoneKey]['items'][$name]['qty'] += $qty;
                $byZone[$zoneKey]['items'][$name]['total'] += $line;
                $byZone[$zoneKey]['total_sales'] += $line;
                // ======================
            }

            $overallTotal += $t->total_amount;
            $overallDiscount += $t->discount_amount;
            $pm = strtolower($t->payment_method ?: 'cash');
            if (isset($overallPayments[$pm])) $overallPayments[$pm] += $t->total_amount;

            $byCashier[$cashierName]['total_sales'] += $t->total_amount;
            $byCashier[$cashierName]['total_discount'] += $t->discount_amount;
            if ($pm === 'klook') $byCashier[$cashierName]['klook_total'] += $t->total_amount;
            if ($pm === 'stardeals') $byCashier[$cashierName]['stardeals_total'] += $t->total_amount;
            if (isset($byCashier[$cashierName]['payment_breakdown'][$pm])) {
                $byCashier[$cashierName]['payment_breakdown'][$pm] += $t->total_amount;
            }
        }

        return [
            'overall' => [
                'items' => array_values($overallItems),
                'total_sales' => $overallTotal,
                'total_discount' => $overallDiscount,
                'payment_breakdown' => $overallPayments,
            ],
            'vouchers' => $vouchers,
            'cashiers' => array_map(function ($c) {
                $c['items'] = array_values($c['items']);
                return $c;
            }, array_values($byCashier)),
            'zones' => array_values(array_map(function ($z) {
                $z['items'] = array_values($z['items']);
                return $z;
            }, array_filter($byZone, fn($z) => count($z['items']) > 0))),
        ];
    }

    // ================= PRINT SALES REPORT (cut-off) =================
    public function printSalesReportData(Request $request)
    {
        $authorizedBy = $request->input('authorized_by') ?: (session('username') ?? 'Manager');
        $lastCutoff = DB::table('sales_cutoffs')->orderByDesc('id')->first();
        $periodStart = $lastCutoff->period_end ?? now()->startOfDay();
        $periodEnd = now();

        $transactions = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->whereNull('t.cutoff_id')
            ->where('t.transaction_status', '!=', 'Voided')
            ->select('t.*', 'u.fullname as cashier_name')
            ->get();

        $voidedItems = $this->getVoidedItems();
        $voidedTxns  = $this->getVoidedTransactions();

        if ($transactions->isEmpty() && $voidedItems->isEmpty() && $voidedTxns->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No new transactions have been made yet.']);
        }

        $cutoffNumber = ($lastCutoff->cutoff_number ?? 0) + 1;

        $cutoffId = DB::table('sales_cutoffs')->insertGetId([
            'cutoff_number' => $cutoffNumber,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'performed_by' => session('user_id'),
            'performed_by_name' => $authorizedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Close everything: completed AND voided transactions, plus item-level voids.
        // After this, these transactions can no longer be voided.
        DB::table('sales_transactions')
            ->whereIn('transaction_id', $transactions->pluck('transaction_id'))
            ->update(['cutoff_id' => $cutoffId]);

        DB::table('sales_transactions')
            ->whereIn('transaction_id', $voidedTxns->pluck('transaction_id'))
            ->update(['cutoff_id' => $cutoffId]);

        DB::table('voided_items')
            ->whereIn('id', $voidedItems->pluck('id'))
            ->update(['cutoff_id' => $cutoffId]);

            DB::table('products')
        ->whereIn('category_id', function ($q) {
            $q->select('category_id')->from('categories')->where('is_stock_tracked', 1);
        })
        ->update([
            'beginning_stock' => DB::raw('stock'),
            'actual_count'    => null,
            'counted_at'      => null,
        ]);
        $voidedTxnCount = $voidedTxns->count();
        $voidedTxnTotal = (float) $voidedTxns->sum('total_amount');

        $itemsByTxn = $this->getItemsByTransaction($transactions->pluck('transaction_id'));
        $report = $this->buildSalesReport($transactions, $itemsByTxn);

        // LOG: cut-off / sales report (this closes the day, so it matters)
        $this->audit(
            'sales_cutoff_created',
            "Generated cut-off #{$cutoffNumber}: " . $transactions->count() . ' transaction(s), ₱'
                . number_format($report['overall']['total_sales'], 2),
            'warning',
            [
                'cutoff_id'          => $cutoffId,
                'cutoff_number'      => $cutoffNumber,
                'transactions'       => $transactions->count(),
                'total_sales'        => (float) $report['overall']['total_sales'],
                'total_discount'     => (float) $report['overall']['total_discount'],
                'void_count'         => $voidedItems->count(),
                'void_total'         => (float) $voidedItems->sum('amount'),
                'voided_txn_count'   => $voidedTxnCount,
                'voided_txn_total'   => $voidedTxnTotal,
                'authorized_by'      => $authorizedBy, // comes from the browser, not verified here
            ]
        );

        // ── Notify: sales report / cut-off was successfully printed ──
        $periodStartLabel = \Illuminate\Support\Carbon::parse($periodStart)->format('M d, Y h:i A');
        $periodEndLabel   = \Illuminate\Support\Carbon::parse($periodEnd)->format('M d, Y h:i A');

        $lines = [
            "Cut-off #: {$cutoffNumber}",
            "Period: {$periodStartLabel} — {$periodEndLabel}",
            "Authorized by: {$authorizedBy}",
            "Transactions: " . $transactions->count(),
            "Total Sales: ₱" . number_format($report['overall']['total_sales'], 2),
            "Total Discount: ₱" . number_format($report['overall']['total_discount'], 2),
            "Vouchers Redeemed: " . $report['vouchers']['count'] . " (₱" . number_format($report['vouchers']['total'], 2) . ")",
            "Voided Items: " . $voidedItems->count() . " (₱" . number_format($voidedItems->sum('amount'), 2) . ")",
            "Voided Transactions: {$voidedTxnCount} (₱" . number_format($voidedTxnTotal, 2) . ")",
        ];
        $this->notify(
            'Sales Report Printed',
            "The daily sales cut-off report was generated and printed.\n" . implode("\n", $lines),
            route('pos.index')
        );

        return response()->json([
            'success' => true,
            'cutoff_number' => $cutoffNumber,
            'period_end' => $periodEnd,
            'authorized_by' => $authorizedBy,
            'void_count' => $voidedItems->count(),
            'void_total' => $voidedItems->sum('amount'),
            'voided_txn_count' => $voidedTxnCount,
            'voided_txn_total' => $voidedTxnTotal,
            'report' => $report,
        ]);
    }

    public function presentReport()
    {
        $transactions = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->whereNull('t.cutoff_id')
            ->where('t.transaction_status', '!=', 'Voided')
            ->select('t.*', 'u.fullname as cashier_name')
            ->get();

        $voidedItems = $this->getVoidedItems();
        $voidedTxns  = $this->getVoidedTransactions();

        if ($transactions->isEmpty() && $voidedItems->isEmpty() && $voidedTxns->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No sales have been recorded today yet.']);
        }

        $itemsByTxn = $this->getItemsByTransaction($transactions->pluck('transaction_id'));

        return response()->json([
            'success' => true,
            'void_count' => $voidedItems->count(),
            'void_total' => $voidedItems->sum('amount'),
            'voided_txn_count' => $voidedTxns->count(),
            'voided_txn_total' => (float) $voidedTxns->sum('total_amount'),
            'report' => $this->buildSalesReport($transactions, $itemsByTxn),
        ]);
    }

    public function historicalCutoffs(Request $request)
    {
        $date = $request->query('date');
        $q = DB::table('sales_cutoffs')->orderByDesc('id');
        if ($date) $q->whereDate('period_end', $date);
        return response()->json(['cutoffs' => $q->get()]);
    }

    public function cutoffDetail(int $id)
    {
        $cutoff = DB::table('sales_cutoffs')->find($id);
        if (!$cutoff) return response()->json(['success' => false, 'message' => 'Not found.']);

        $transactions = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->where('t.cutoff_id', $id)
            ->where('t.transaction_status', '!=', 'Voided')
            ->select('t.*', 'u.fullname as cashier_name')
            ->get();

        $voidedItems = $this->getVoidedItems($id);
        $voidedTxns  = $this->getVoidedTransactions($id);

        $itemsByTxn = $this->getItemsByTransaction($transactions->pluck('transaction_id'));

        return response()->json([
            'success' => true,
            'cutoff_number' => $cutoff->cutoff_number,
            'period_end' => $cutoff->period_end,
            'authorized_by' => $cutoff->performed_by_name,
            'void_count' => $voidedItems->count(),
            'void_total' => $voidedItems->sum('amount'),
            'voided_txn_count' => $voidedTxns->count(),
            'voided_txn_total' => (float) $voidedTxns->sum('total_amount'),
            'report' => $this->buildSalesReport($transactions, $itemsByTxn),
        ]);
    }

    private function buildReportFooter(string $authorizedBy, int $voidCount, float $voidTotal, bool $isPreview, $voidedTxnCount = 0, $voidedTxnTotal = 0)
    {
        return "
        <div class=\"report-receipt\" style=\"border-top:2px dashed #ccc;margin-top:6px;padding-top:6px;\">
            <div class=\"receipt-vat-section\">
                <div class=\"vat-row\"><span>Voided Item(s)</span><span>{$voidCount}</span></div>
                <div class=\"vat-row\"><span>Voided Amount</span><span>−₱" . number_format($voidTotal, 2) . "</span></div>
                <div class=\"vat-row\"><span>Voided Transaction(s)</span><span>{$voidedTxnCount}</span></div>
                <div class=\"vat-row\"><span>Voided Txn Amount</span><span>−₱" . number_format($voidedTxnTotal, 2) . "</span></div>
            </div>
            <hr class=\"dashed\">
            <div style=\"text-align:center;font-size:12px;font-weight:700;margin-top:4px;\">
                " . ($isPreview ? 'Prepared by' : 'Cut-off Authorized by') . ": " . htmlspecialchars($authorizedBy) . "
            </div>
        </div>";
    }

    // ================= DAY LOCK (cut-off) =================
    private function isDayLocked(): bool
    {
        return DB::table('sales_cutoffs')
            ->whereDate('period_end', now()->toDateString())
            ->exists();
    }

    public function cutoffStatus()
    {
        $locked = $this->isDayLocked();
        return response()->json([
            'locked'  => $locked,
            'message' => $locked
                ? 'Transactions are closed for today after cut-off. New transactions open tomorrow.'
                : null,
        ]);
    }

    public function voidTransaction(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $data = $request->validate([
            'invoice_number' => 'required|integer',
            'reason'         => 'required|string|max:255',
            'authorized_by'  => 'required|string',
        ]);

        $transactionId = (int) $data['invoice_number'];
        $invoiceRef    = 'Inv #' . str_pad($transactionId, 11, '0', STR_PAD_LEFT);

        $txn = DB::table('sales_transactions')->where('transaction_id', $transactionId)->first();

        if (!$txn) {
            return response()->json(['success' => false, 'message' => 'Invoice #' . $transactionId . ' not found.']);
        }

        if ($txn->transaction_status === 'Voided') {
            return response()->json(['success' => false, 'message' => 'This transaction is already voided.']);
        }

        // NOT ALLOWED: the invoice is already in a printed sales report (cut-off),
        // so it is "past" sales. Only present sales (no cut-off yet) can be voided.
        if (!empty($txn->cutoff_id)) {
            $this->audit(
                'void_blocked',
                "Blocked void attempt on {$invoiceRef} — already included in a printed sales report",
                'warning',
                [
                    'transaction_id' => $transactionId,
                    'cutoff_id'      => (int) $txn->cutoff_id,
                    'authorized_by'  => $data['authorized_by'],
                    'stage'          => 'void',
                ],
                true
            );

            return response()->json([
                'success' => false,
                'message' => 'This invoice is already included in a printed sales report and can no longer be voided.',
            ]);
        }

        // Get the items BEFORE voiding; they are used for the "voided receipt".
        $items = DB::table('transaction_items')
            ->where('transaction_id', $transactionId)
            ->get()
            ->map(fn($i) => [
                'name'  => $i->product_name,
                'price' => (float) $i->price,
                'qty'   => (int)   $i->quantity,
            ])->toArray();

        // For the log: find out BEFORE voiding whether a voucher will be restored
        $linkedVoucher = DB::table('bookings')
            ->where('redeemed_transaction_id', $transactionId)
            ->first();

        try {
            DB::transaction(function () use ($transactionId, $data) {
                // Re-check under a lock: if a cut-off or another void happens at
                // the same moment, this cannot slip through.
                $locked = DB::table('sales_transactions')
                    ->where('transaction_id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || $locked->transaction_status === 'Voided') {
                    throw new \RuntimeException('This transaction is already voided.');
                }
                if (!empty($locked->cutoff_id)) {
                    throw new \RuntimeException('This invoice is already included in a printed sales report and can no longer be voided.');
                }

                // Mark as Voided. The record is not deleted, for the audit trail
                // (BIR-compliant: every void must be traceable).
                DB::table('sales_transactions')
                    ->where('transaction_id', $transactionId)
                    ->update([
                        'transaction_status' => 'Voided',
                        'void_reason'        => $data['reason'],
                        'voided_by'          => $data['authorized_by'],
                        'voided_at'          => now(),
                    ]);

                // If this was an online-booking voucher, restore it to
                // "confirmed" so the customer can use it again.
                DB::table('bookings')
                    ->where('redeemed_transaction_id', $transactionId)
                    ->update([
                        'status'                  => 'confirmed',
                        'redeemed_at'             => null,
                        'redeemed_by'             => null,
                        'redeemed_transaction_id' => null,
                        'updated_at'              => now(),
                    ]);

                // Restore the stock of tracked items that was deducted at checkout.
                $rows = DB::table('transaction_items')->where('transaction_id', $transactionId)->get();

                foreach ($rows as $item) {
                    if (!$item->product_id) continue; // voucher line, no product/stock

                    DB::table('products')
                        ->where('product_id', $item->product_id)
                        ->whereIn('category_id', function ($q) {
                            $q->select('category_id')->from('categories')->where('is_stock_tracked', 1);
                        })
                        ->whereNotNull('stock')
                        ->increment('stock', $item->quantity);
                }

                // NOTE: items are intentionally NOT written to voided_items anymore.
                // A full-transaction void is counted under "Voided Transaction(s)"
                // in the report; writing the items here too would double-count them
                // under "Voided Item(s)".
            });
        } catch (\Throwable $e) {
            // LOG: the void failed
            $this->audit(
                'void_failed',
                "Failed to void {$invoiceRef}",
                'warning',
                ['transaction_id' => $transactionId, 'error' => mb_substr($e->getMessage(), 0, 200)]
            );

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // LOG: the whole transaction was voided.
        // Anomaly when it is from a different day or the manager was not verified.
        $sameDay  = \Illuminate\Support\Carbon::parse($txn->created_at)->isToday();
        $verified = $this->overrideMatches($data['authorized_by']);

        $voidFlags = [];
        if (!$sameDay)  { $voidFlags[] = 'from a different day'; }
        if (!$verified) { $voidFlags[] = 'manager override not verified'; }

        $this->audit(
            'transaction_voided',
            "Voided {$invoiceRef} (₱" . number_format((float) $txn->total_amount, 2) . ")"
                . ($voidFlags ? ' — ' . implode(', ', $voidFlags) : ''),
            'warning',
            [
                'transaction_id'    => $transactionId,
                'reason'            => $data['reason'],
                'authorized_by'     => $data['authorized_by'],
                'override_verified' => $verified,
                'payment_method'    => $txn->payment_method,
                'voucher_restored'  => $linkedVoucher ? $linkedVoucher->voucher_code : null,
                'stock_restored'    => true,
                'old'               => ['status' => $txn->transaction_status, 'total' => (float) $txn->total_amount],
                'new'               => ['status' => 'Voided'],
            ],
            !empty($voidFlags)
        );

        $this->checkRepeatedVoids();

        $this->notify(
            'Transaction Voided',
            "Invoice #" . str_pad($transactionId, 11, '0', STR_PAD_LEFT) . " was voided.\n" .
            "Reason: {$data['reason']}\nAuthorized by: {$data['authorized_by']}",
            route('pos.index')
        );

        // Return the full details of the voided transaction so the frontend
        // can show the "Voided Receipt".
        return response()->json([
            'success' => true,
            'message' => 'Transaction voided successfully.',
            'transaction' => [
                'invoice_number' => str_pad($transactionId, 11, '0', STR_PAD_LEFT),
                'customer_name'  => $txn->customer_name,
                'service_type'   => $txn->service_type,
                'payment_method' => $txn->payment_method,
                'total'          => (float) $txn->total_amount,
                'discount'       => (float) $txn->discount_amount,
                'items'          => $items,
                'void_reason'    => $data['reason'],
                'voided_by'      => $data['authorized_by'],
                'voided_at'      => now()->format('m/d/Y h:i:s A'),
            ],
        ]);
    }
}