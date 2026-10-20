<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\BookingCatalog;
use App\Support\BookingPricingOverrides;
use App\Services\SystemLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Allowed rejection reasons (must match the options in the reject modal).
     */
    private const REJECT_REASONS = [
        'Invalid or unclear payment receipt',
        'Incorrect payment amount',
        'Selected date/time is fully booked',
        'Selected package is not available',
        'Incomplete or incorrect booking details',
        'Others',
    ];

    // ─── SYSTEM LOG HELPER ─────────────────────────────────
    // Single place that calls SystemLogger::record().
    // The app uses sessions (not Laravel Auth), so the user_id and email
    // of the staff member performing the action are passed manually.
    // When the event type ends with "_deleted", checkBulkDelete() is also
    // called. Wrapped in try/catch so a logging failure never breaks the
    // main action.
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
                'reservations'
            );

            if (str_ends_with($eventType, '_deleted')) {
                SystemLogger::checkBulkDelete($uid, 'reservations');
            }
        } catch (\Throwable $e) {
            Log::error('SystemLogger failed (Reservations): ' . $e->getMessage());
        }
    }

    // Convert a value to a plain string (for comparing old vs. new values).
    private function norm(mixed $v): string
    {
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }

        return (string) $v;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::with(['customer', 'user'])->latest()->get();

        return view('customer.reservations', compact('bookings'));
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created reservation from the "New Reservation" modal.
     * Accepts either an existing customer_id, or guest customer_name/contact
     * (matches the fallback fields shown in the blade when no customers exist yet).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => ['nullable', 'exists:customers,id'],
            'customer_name'     => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'customer_contact'  => ['nullable', 'string', 'max:50'],
            'package'           => ['required', 'string', 'max:255'],
            'pax'               => ['required', 'integer', 'min:1'],
            'reservation_date'  => ['required', 'date'],
            'reservation_time'  => ['required'],
            'notes'             => ['nullable', 'string'],
        ]);

        $booking = Booking::create([
            'customer_id'      => $validated['customer_id'] ?? null,
            'customer_name'    => $validated['customer_name'] ?? null,
            'customer_contact' => $validated['customer_contact'] ?? null,
            'package'          => $validated['package'],
            'pax'              => $validated['pax'],
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'notes'            => $validated['notes'] ?? null,
            'status'           => 'pending',
            'approval_status'  => 'pending',
        ]);

        // LOG: new reservation created by staff (no customer name or contact stored)
        $this->audit(
            'reservation_created',
            "Created reservation #{$booking->getKey()} ({$validated['package']}, {$validated['pax']} pax)",
            'info',
            [
                'new' => [
                    'booking_id'       => $booking->getKey(),
                    'package'          => mb_substr($validated['package'], 0, 100),
                    'pax'              => (int) $validated['pax'],
                    'reservation_date' => $validated['reservation_date'],
                    'status'           => 'pending',
                ],
            ]
        );

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * Return a single reservation's details as JSON.
     * Used by the expandable "Recent Visitor Logins" row on the
     * Visitor Summary page (fetch('/reservations/{id}')).
     */
    public function show(Booking $reservation)
    {
        return response()->json([
            'package'          => $reservation->package,
            'pax'              => $reservation->pax,
            'reservation_date' => optional($reservation->reservation_date)->format('M d, Y'),
            'reservation_time' => $reservation->reservation_time,
            'status'           => $reservation->status,
            'approval_status'  => $reservation->approval_status,
            'reject_reason'    => $reservation->reject_reason,
            'reject_note'      => $reservation->reject_note,
        ]);
    }

    public function edit(Booking $reservation)
    {
        //
    }

    /**
     * Used for quick status updates (confirm/paid/cancel) from the admin table.
     * The blade posts here via the inline status dropdown on each row.
     */
    public function update(Request $request, Booking $reservation)
    {
        // Quick status-only update (from the inline dropdown)
        if ($request->has('status') && !$request->has('package')) {
            $validated = $request->validate([
                'status' => ['required', 'in:pending,confirmed,paid,cancelled'],
            ]);

            $oldStatus = $reservation->status;

            $reservation->update($validated);

            // LOG: manual status change. Flagged as an anomaly when the booking
            // becomes confirmed/paid without going through payment verification
            // (awaiting_verification).
            if ($oldStatus !== $validated['status']) {
                $bypass = in_array($validated['status'], ['confirmed', 'paid'], true)
                    && $oldStatus !== 'awaiting_verification';

                $this->audit(
                    'reservation_status_changed',
                    "Changed status of reservation #{$reservation->getKey()}: {$oldStatus} → {$validated['status']}"
                        . ($bypass ? ' (did not go through payment verification)' : ''),
                    $bypass ? 'warning' : 'info',
                    [
                        'booking_id'   => $reservation->getKey(),
                        'voucher_code' => $reservation->voucher_code ?? null,
                        'old'          => ['status' => $oldStatus],
                        'new'          => ['status' => $validated['status']],
                        'via'          => 'inline_dropdown',
                    ],
                    $bypass
                );
            }

            return redirect()->route('reservations.index')->with('success', 'Reservation updated.');
        }

        // Full edit from the Edit modal
        $validated = $request->validate([
            'customer_name'     => ['required', 'string', 'max:255'],
            'customer_contact'  => ['nullable', 'string', 'max:50'],
            'package'           => ['required', 'string', 'max:255'],
            'pax'               => ['required', 'integer', 'min:1'],
            'reservation_date'  => ['required', 'date'],
            'reservation_time'  => ['required'],
            'notes'             => ['nullable', 'string'],
        ]);

        // For the log: previous values BEFORE updating
        $watch  = ['package', 'pax', 'reservation_date', 'reservation_time'];
        $before = [];
        foreach ($watch as $f) {
            $before[$f] = $this->norm($reservation->{$f});
        }
        $nameBefore = $this->norm($reservation->customer_name);

        $reservation->update($validated);

        // LOG: only the fields that changed. For the customer name and contact,
        // we only record that the name changed, never its contents.
        $oldVals = [];
        $newVals = [];
        $fresh   = $reservation->fresh();
        foreach ($watch as $f) {
            $after = $this->norm($fresh->{$f});
            if ($before[$f] !== $after) {
                $oldVals[$f] = $before[$f];
                $newVals[$f] = $after;
            }
        }
        $nameChanged = $nameBefore !== $this->norm($fresh->customer_name);

        if ($newVals || $nameChanged) {
            // Anomaly: package o pax ang binago pagkatapos mabayaran (puwedeng bumaba ang halaga)
            $paidStates   = ['confirmed', 'paid', 'awaiting_verification', 'done'];
            $afterPayment = (isset($newVals['package']) || isset($newVals['pax']))
                && in_array($reservation->status, $paidStates, true);

            $this->audit(
                'reservation_updated',
                "Edited reservation #{$reservation->getKey()}: "
                    . implode(', ', array_merge(array_keys($newVals), $nameChanged ? ['customer_name'] : []))
                    . ($afterPayment ? ' (package/pax changed after payment)' : ''),
                $afterPayment ? 'warning' : 'info',
                [
                    'booking_id'            => $reservation->getKey(),
                    'voucher_code'          => $reservation->voucher_code ?? null,
                    'old'                   => $oldVals,
                    'new'                   => $newVals,
                    'customer_name_changed' => $nameChanged,
                ],
                $afterPayment
            );
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    /**
     * Approve / reject a reservation from the admin table.
     *
     * PATCH /reservations/{reservation}/status
     * Approve body: status=approved
     * Reject body:  status=rejected, reject_reason, reject_note
     *               (reject_note required when reject_reason is "Others").
     */
    public function updateStatus(Request $request, Booking $reservation)
    {
        $validated = $request->validate([
            'status'        => ['required', Rule::in(['pending', 'confirmed', 'paid', 'cancelled', 'approved', 'rejected'])],
            'reject_reason' => [
                'required_if:status,rejected',
                'nullable',
                'string',
                Rule::in(self::REJECT_REASONS),
            ],
            'reject_note'   => [
                'required_if:reject_reason,Others',
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'reject_reason.required_if' => 'Please select a reason for rejecting this reservation.',
            'reject_note.required_if'   => 'Please add a note when the reason is "Others".',
        ]);

        // For the log: previous status BEFORE updating
        $oldStatus   = $reservation->status;
        $oldApproval = $reservation->approval_status;

        // REJECT
        if ($validated['status'] === 'rejected') {
            $reservation->update([
                'status'          => 'rejected',
                'approval_status' => 'rejected',
                'reject_reason'   => $validated['reject_reason'],
                'reject_note'     => $validated['reject_note'] ?? null,
            ]);

            // LOG: reservation rejected (with reason and note)
            $this->audit(
                'reservation_rejected',
                "Rejected reservation #{$reservation->getKey()}: {$validated['reject_reason']}",
                'warning',
                [
                    'booking_id'   => $reservation->getKey(),
                    'voucher_code' => $reservation->voucher_code ?? null,
                    'reason'       => $validated['reject_reason'],
                    'note'         => isset($validated['reject_note']) ? mb_substr($validated['reject_note'], 0, 300) : null,
                    'old'          => ['status' => $oldStatus, 'approval_status' => $oldApproval],
                    'new'          => ['status' => 'rejected', 'approval_status' => 'rejected'],
                ]
            );

            return redirect()
                ->route('reservations.index')
                ->with('success', 'Reservation rejected.');
        }

        // APPROVE
        if ($validated['status'] === 'approved') {
            $reservation->update([
                'status'              => 'confirmed',
                'approval_status'     => 'approved',
                'payment_verified_at' => $reservation->payment_verified_at ?? now(),
                'reject_reason'       => null,
                'reject_note'         => null,
            ]);

            // LOG: reservation approved. It becomes 'confirmed', so the voucher
            // can now be redeemed at the POS. Flagged as an anomaly when it did
            // not come from awaiting_verification (payment was not checked).
            $bypass = $oldStatus !== 'awaiting_verification';
            $this->audit(
                'reservation_approved',
                "Approved reservation #{$reservation->getKey()}"
                    . ($bypass ? ' (did not go through payment verification)' : ''),
                $bypass ? 'warning' : 'info',
                [
                    'booking_id'   => $reservation->getKey(),
                    'voucher_code' => $reservation->voucher_code ?? null,
                    'old'          => ['status' => $oldStatus, 'approval_status' => $oldApproval],
                    'new'          => ['status' => 'confirmed', 'approval_status' => 'approved'],
                ],
                $bypass
            );

            // Notify the customer by email (never blocks the approval if it fails)
            $emailed = $this->emailPaymentResult($reservation, true);

            return redirect()
                ->route('reservations.index')
                ->with('success', 'Reservation approved.' . ($emailed ? ' The customer was notified by email.' : ''));
        }

        // OTHER STATUSES
        $reservation->update([
            'status'        => $validated['status'],
            'reject_reason' => null,
            'reject_note'   => null,
        ]);

        // LOG: manual status change. Flagged as an anomaly when the booking
        // becomes confirmed/paid without going through payment verification.
        if ($oldStatus !== $validated['status']) {
            $bypass = in_array($validated['status'], ['confirmed', 'paid'], true)
                && $oldStatus !== 'awaiting_verification';

            $this->audit(
                'reservation_status_changed',
                "Changed status of reservation #{$reservation->getKey()}: {$oldStatus} → {$validated['status']}"
                    . ($bypass ? ' (did not go through payment verification)' : ''),
                $bypass ? 'warning' : 'info',
                [
                    'booking_id'   => $reservation->getKey(),
                    'voucher_code' => $reservation->voucher_code ?? null,
                    'old'          => ['status' => $oldStatus],
                    'new'          => ['status' => $validated['status']],
                    'via'          => 'update_status',
                ],
                $bypass
            );
        }

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation status updated.');
    }

    /**
     * Approve a customer's proof of payment: the booking becomes
     * 'confirmed' (customer can no longer reschedule, and the voucher can
     * now be redeemed at the POS).
     *
     * POST /reservations/{reservation}/approve-payment
     */
    public function approvePayment(Booking $reservation)
    {
        if ($reservation->status !== 'awaiting_verification') {
            return redirect()->route('reservations.index')
                ->with('error', 'Only bookings waiting for approval can be approved.');
        }

        $oldStatus = $reservation->status;

        $reservation->update([
            'status'                   => 'confirmed',
            'payment_verified_at'      => now(),
            'payment_rejection_reason' => null,
        ]);

        // LOG: payment approved. References only (booking ID and voucher code),
        // no email, name, or proof of payment.
        $this->audit(
            'payment_approved',
            "Approved payment for reservation #{$reservation->getKey()}",
            'info',
            [
                'booking_id'   => $reservation->getKey(),
                'voucher_code' => $reservation->voucher_code ?? null,
                'old'          => ['status' => $oldStatus],
                'new'          => ['status' => 'confirmed'],
            ]
        );

        $emailed = $this->emailPaymentResult($reservation, true);

        return redirect()->route('reservations.index')->with(
            'success',
            'Payment approved. Booking confirmed.' . ($emailed ? ' The customer was notified by email.' : '')
        );
    }

    /**
     * Reject a customer's proof of payment. The booking goes back to
     * 'pending_payment' with the reason, so the customer sees why and can
     * upload a correct proof (or reschedule / cancel).
     *
     * POST /reservations/{reservation}/reject-payment
     */
    public function rejectPayment(Request $request, Booking $reservation)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($reservation->status !== 'awaiting_verification') {
            return redirect()->route('reservations.index')
                ->with('error', 'Only bookings waiting for approval can be rejected.');
        }

        $oldStatus = $reservation->status;

        $reservation->update([
            'status'                   => 'pending_payment',
            'payment_rejection_reason' => $validated['reason'],
            'payment_verified_at'      => null,
        ]);

        // LOG: payment rejected (with reason)
        $this->audit(
            'payment_rejected',
            "Rejected payment for reservation #{$reservation->getKey()}: " . mb_substr($validated['reason'], 0, 120),
            'warning',
            [
                'booking_id'   => $reservation->getKey(),
                'voucher_code' => $reservation->voucher_code ?? null,
                'reason'       => $validated['reason'],
                'old'          => ['status' => $oldStatus],
                'new'          => ['status' => 'pending_payment'],
            ]
        );

        $emailed = $this->emailPaymentResult($reservation, false);

        return redirect()->route('reservations.index')->with(
            'success',
            'Payment rejected. The customer was asked to upload a new proof.' . ($emailed ? ' They were notified by email.' : '')
        );
    }

    /**
     * Email the customer the result of the payment check (approved or
     * rejected). Same approach as the 2FA / password emails: plain text via
     * the app's configured mailer, wrapped in try/catch so a mail problem
     * never blocks the admin's approve/reject action. Returns true when the
     * email was handed to the mailer, false if there was no email address or
     * sending failed.
     */
    private function emailPaymentResult(Booking $booking, bool $approved): bool
    {
        $customer = $booking->display_customer;
        $email    = $customer->email ?? null;

        if (!$email) {
            return false; // walk-in / guest reservation without an email
        }

        $services = BookingCatalog::services();
        $packages = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());

        $name        = $customer->fullname ?? 'there';
        $serviceName = $services[$booking->service]['name'] ?? ucwords(str_replace('_', ' ', (string) $booking->service));
        $packageName = $packages[$booking->service][$booking->package]['name'] ?? $booking->package;
        $date        = optional($booking->display_date)->format('F j, Y');
        $time        = $booking->display_time;
        $pax         = $booking->display_pax;
        $myBookings  = route('user.bookings');

        $details = "Attraction: {$serviceName}\n"
                 . "Package: {$packageName}\n"
                 . "Date: {$date}" . ($time ? " at {$time}" : '') . "\n"
                 . "Guests: {$pax}\n";

        if ($approved) {
            $subject = 'Your Pistavora booking is confirmed';
            $body = "Hi {$name},\n\n"
                  . "Good news! We checked your proof of payment and your booking is now confirmed.\n\n"
                  . $details
                  . "Voucher code: {$booking->voucher_code}\n\n"
                  . "Show this voucher code to the cashier when you arrive.\n"
                  . "Please note that confirmed bookings can no longer be rescheduled.\n\n"
                  . "You can view your booking here: {$myBookings}\n\n"
                  . "See you at Pistavora!";
        } else {
            $subject = 'Action needed: your Pistavora payment proof was not accepted';
            $body = "Hi {$name},\n\n"
                  . "We could not accept the proof of payment you sent for this booking.\n\n"
                  . $details
                  . "Reason: {$booking->payment_rejection_reason}\n\n"
                  . "Please open My Bookings, tap \"Upload New Proof\", and send a clear screenshot or photo of your payment. "
                  . "You can still reschedule or cancel this booking while it is not yet approved.\n\n"
                  . "My Bookings: {$myBookings}\n\n"
                  . "Thank you,\nPistavora";
        }

        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Payment result email failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a reservation (used by the trash-icon button + confirm modal).
     */
    public function destroy(Booking $reservation)
    {
        // For the log: capture a summary BEFORE deleting
        $old = [
            'booking_id'   => $reservation->getKey(),
            'voucher_code' => $reservation->voucher_code ?? null,
            'status'       => $reservation->status,
            'package'      => $reservation->package,
            'pax'          => $reservation->pax,
            'date'         => $this->norm($reservation->reservation_date),
            'redeemed_transaction_id' => $reservation->redeemed_transaction_id ?? null,
        ];
        $wasRedeemed = !empty($reservation->redeemed_transaction_id);

        $reservation->delete();

        // LOG: na-delete ang reservation. Warning kapag may nabayaran na o
        // may voucher na puwedeng gamitin (confirmed/paid/awaiting_verification).
        $hadMoney = $wasRedeemed
            || in_array($old['status'], ['confirmed', 'paid', 'awaiting_verification', 'done'], true);

        $this->audit(
            'reservation_deleted',
            "Deleted reservation #{$old['booking_id']} (status: {$old['status']})"
                . ($wasRedeemed ? ' — voucher was already redeemed' : ''),
            $hadMoney ? 'critical' : 'warning',
            ['old' => $old],
            $hadMoney
        );

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation deleted.');
    }
}