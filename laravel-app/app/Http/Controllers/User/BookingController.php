<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\BookingCatalog;
use App\Support\BookingWizard;
use App\Support\BookingPricingOverrides;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * ------------------------------------------------------------------
     * Wizard catalog additions
     * ------------------------------------------------------------------
     * Skataya Group Bundle: PHP 996 flat, covers 5 guests (4 + 1 free),
     *   shown as the 4th box next to the 1-hour, 2-hour and all-day passes.
     *   It is flagged "recommended" in the wizard once the group is 5+.
     *
     * PiyestaRides "Try Every Ride": the card that already exists in the
     *   catalog (PHP 600 per person) is kept as is, and its "What's included"
     *   list is replaced with the full details below. TER_PRICE is only used
     *   as a fallback if no Try Every Ride card exists in the catalog.
     */
    protected const BUNDLE_PRICE = 996;
    protected const BUNDLE_PACKAGE = 'group_bundle_996';
    protected const TER_PRICE = 600;
    protected const TER_PACKAGE = 'try_every_ride';
    protected const PARTY_MIN_GUESTS = 10;   // a Party booking needs at least 10 guests (same as PARTY_MIN in the wizard JS)

    /**
     * Services, packages, add-ons and inclusions — pulled from
     * App\Support\BookingCatalog with any admin-saved price overrides
     * (App\Support\BookingPricingOverrides) layered on top, so this
     * customer-facing controller and BookingCmsController always show
     * the exact same prices. Previously these were hardcoded on this
     * class directly, which is why CMS edits never showed up here and
     * "reset to default" had nothing to reset on this side either.
     */
    protected array $services;
    protected array $packages;
    protected array $addons;
    protected array $inclusions;

    public function __construct()
    {
        $this->services   = BookingCatalog::services();
        $this->packages   = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());
        $this->addons     = BookingPricingOverrides::applyToAddons(BookingCatalog::baseAddons());
        $this->inclusions = BookingCatalog::inclusions();
    }

    /**
     * Human-readable package name. Uses the catalog name when it exists,
     * then special cases for packages added in withExtraItems(), then a
     * cleaned-up version of the raw key (group_bundle_996 -> Group Bundle 996).
     */
    private function packageLabel(?string $service, ?string $package): string
    {
        $package = (string) $package;
        $name = $this->packages[$service][$package]['name'] ?? null;
        if ($name) {
            return $name;
        }

        $special = [
            self::BUNDLE_PACKAGE => 'Group Bundle 996',
            self::TER_PACKAGE    => 'Try Every Ride',
        ];

        return $special[$package] ?? ucwords(str_replace('_', ' ', $package));
    }

    /**
     * Receipt lines for a booking (name, qty, unit price, free units, total).
     * Old bookings without items fall back to a single package line.
     */
    private function bookingLines(Booking $booking): array
    {
        $lines = collect($booking->items ?? [])->map(function ($i) {
            $name = ($i['package'] ?? '') === self::BUNDLE_PACKAGE
                ? 'Group Bundle 996'
                : (string) ($i['name'] ?? '');

            return [
                'name'       => $name,
                'qty'        => (int) ($i['qty'] ?? 1),
                'unit_price' => (float) ($i['unit_price'] ?? 0),
                'free'       => (int) ($i['free_units'] ?? 0),
                'total'      => (float) ($i['line_total'] ?? 0),
            ];
        })->values()->all();

        if (!count($lines)) {
            $lines = [[
                'name'       => $this->packageLabel($booking->service, $booking->package),
                'qty'        => (int) $booking->tier,
                'unit_price' => (float) $booking->price / max(1, (int) $booking->tier),
                'free'       => 0,
                'total'      => (float) $booking->price,
            ]];
        }

        return $lines;
    }

    /**
     * Adjusts the wizard catalog:
     *   - rollerfever    : Group Bundle (P996) replaces any old group_bundle_2hour card, so the tab shows
     *                      exactly 4 boxes (1 hr, 2 hrs, all day, bundle)
     *   - field_of_rides : full "What's included" details on the Try Every Ride card
     * Used by BOTH create() (what the customer sees) and submit() (what the server validates and prices),
     * so the two can never disagree.
     */
    private function withExtraItems(array $catalog): array
    {
        if (isset($catalog['rollerfever']['items']) && is_array($catalog['rollerfever']['items'])) {
            $items = array_values(array_filter(
                $catalog['rollerfever']['items'],
                fn ($i) => !in_array($i['package'] ?? null, ['group_bundle_2hour', self::BUNDLE_PACKAGE], true)
            ));

            $items[] = [
                'key'           => 'rollerfever_' . self::BUNDLE_PACKAGE,
                'service'       => 'rollerfever',
                'package'       => self::BUNDLE_PACKAGE,
                'name'          => 'Group Bundle',
                'desc'          => '2 hours of skating for 5 guests. Buy 4, get 1 free.',
                'emoji'         => "\u{1F6FC}",
                'image'         => 'group-bundle.jpg',
                'badge'         => null,
                'unit_price'    => (float) self::BUNDLE_PRICE,
                'flat'          => false,
                'tiers'         => [],
                'days'          => null,
                'inclusions'    => [
                    '2-hour skate pass for 5 guests',
                    'Buy 4, get 1 free',
                ],
                // pack settings used by the wizard
                'pack'          => true,
                'unit'          => 'bundle',
                'unit_label'    => 'Bundles',
                'covers'        => 5,
                'recommend_min' => 5,
            ];

            $catalog['rollerfever']['items'] = $items;
        }

        // PiyestaRides: complete the existing "Try Every Ride" card (matched by name).
        if (isset($catalog['field_of_rides']['items']) && is_array($catalog['field_of_rides']['items'])) {
            $terDesc = "Try every ride \u{2014} up to 10 rides, except Viking. Shareable, no expiration.";
            $terInclusions = [
                "Try every ride \u{2014} maximum of 10 rides, except Viking",
                'Shareable',
                'No expiration',
                'One ride each on every attraction listed on the price board',
                'Minimum height requirement applies per ride (posted at each attraction)',
                "Guests under 4ft must be accompanied by a paying guardian \u{2014} no chaperone-only riders",
                'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
                'Management reserves the right to refuse service to guests who do not follow safety guidelines',
            ];

            $found = false;
            foreach ($catalog['field_of_rides']['items'] as $idx => $i) {
                if (stripos((string) ($i['name'] ?? ''), 'try every ride') !== false) {
                    $catalog['field_of_rides']['items'][$idx]['desc']       = $terDesc;
                    $catalog['field_of_rides']['items'][$idx]['inclusions'] = $terInclusions;
                    $found = true;
                    break;
                }
            }

            // Fallback: no Try Every Ride card in the catalog yet, so add one (per person, like the other passes).
            if (!$found && self::TER_PRICE > 0) {
                $catalog['field_of_rides']['items'][] = [
                    'key'        => 'field_of_rides_' . self::TER_PACKAGE,
                    'service'    => 'field_of_rides',
                    'package'    => self::TER_PACKAGE,
                    'name'       => "Try Every Ride \u{2014} All Access Pass",
                    'desc'       => $terDesc,
                    'emoji'      => "\u{1F3A2}",
                    'image'      => null,
                    'badge'      => null,
                    'unit_price' => (float) self::TER_PRICE,
                    'flat'       => false,
                    'tiers'      => [],
                    'days'       => null,
                    'inclusions' => $terInclusions,
                ];
            }
        }

        return $catalog;
    }

    /**
     * Show the booking form (date + package selection).
     *
     * GET /user/booking
     */
    public function create(Request $request)
    {
        $dates = $this->upcomingDates();
        $services = $this->services;
        $packages = $this->packages;
        $inclusions = $this->inclusions;
        $addons = $this->addons;

        // Step-by-step wizard data (cards, deal rules) + optional resume of a
        // submitted booking: /app/booking?booking=ID opens straight on the voucher step.
        $catalog = $this->withExtraItems(BookingWizard::catalog($packages, $addons, $services));
        $deals   = BookingWizard::deals();
        $resume  = null;

        if ($request->filled('booking') && session()->has('user_id')) {
            $existing = Booking::where('user_id', session('user_id'))->find((int) $request->query('booking'));
            if ($existing) {
                $resume = $this->wizardSummary($existing);
            }
        }

        return view('user.booking', compact('dates', 'services', 'packages', 'inclusions', 'addons', 'catalog', 'deals', 'resume'));
    }

    /**
     * Store a new booking request.
     *
     * POST /user/booking
     */
    public function store(Request $request): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['required', 'date_format:H:i'],
            'service'    => ['required', 'string', 'in:' . implode(',', array_keys($this->services))],
            'package'    => ['required', 'string'],
            'tier'       => ['required', 'integer'],
        ]);

        $servicePackages = $this->packages[$validated['service']] ?? [];

        if (!array_key_exists($validated['package'], $servicePackages)) {
            return back()
                ->withInput()
                ->withErrors(['package' => 'Please choose a valid package for this service.']);
        }

        $package = $servicePackages[$validated['package']];

        if (!array_key_exists($validated['tier'], $package['tiers'])) {
            return back()
                ->withInput()
                ->withErrors(['tier' => 'Please choose a valid pax tier for this package.']);
        }

        $booking = Booking::create([
            'user_id'    => session('user_id'),
            'service'    => $validated['service'],
            'package'    => $validated['package'],
            'tier'       => $validated['tier'],
            'price'      => $package['tiers'][$validated['tier']],
            'visit_date' => $validated['visit_date'],
            'visit_time' => $validated['visit_time'],
            'status'     => 'pending_payment',
        ]);

        // Remember which service (DinoLakbay vs Skataya vs Field of
        // Rides) this booking is for, so the Waiver step later on can show
        // the correct waiver text. Also remember the booking id so the
        // receipt shown after the waiver is signed knows which booking to
        // display.
        session([
            'booking_service' => $validated['service'],
            'booking_id'       => $booking->id,
        ]);

        // Booking flow is: Booking -> Payment -> Waiver (last).
        return redirect()
            ->route('user.bookings.review', $booking->id)
            ->with('success', 'Booking details saved. Choose a payment method to continue.');
    }

    /**
     * Show the customer's bookings list.
     *
     * GET /user/bookings
     */
    public function index(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // Search (?q=...): matches package / attraction names, service,
        // booking reference (WP-000027), date, status, pax and — only for
        // approved bookings — the voucher code. Every word typed must match.
        $q = trim((string) $request->query('q', ''));

        $formatted = Booking::where('user_id', session('user_id'))
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($booking) {
                $row      = $this->formatBooking($booking);
                $approved = in_array($booking->status, ['confirmed', 'done'], true);

                $row['_hay'] = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                    $row['category'],
                    $row['package'],
                    $row['date'],
                    $booking->visit_date->format('F j Y l'),
                    'WP-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
                    $row['status_label'],
                    $row['pax'] . ' pax',
                    $approved ? $booking->voucher_code : null,
                    collect($row['lines'])->pluck('name')->implode(' '),
                    collect($booking->addons ?? [])->pluck('name')->implode(' '),
                ])));

                return $row;
            });

        if ($q !== '') {
            $terms = preg_split('/\s+/', \Illuminate\Support\Str::lower($q));
            $formatted = $formatted
                ->filter(fn ($row) => collect($terms)->every(fn ($t) => str_contains($row['_hay'], $t)))
                ->values();
        }

        $perPage = 5;
        $page    = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
            $formatted->forPage($page, $perPage)->values(),
            $formatted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = $this->bookingStats(session('user_id'));

        return view('user.bookings', compact('bookings', 'stats', 'q'));
    }

    /**
     * Personal booking stats for the "My Bookings" page — the customer's
     * own activity, not overall business performance: total visits,
     * upcoming vs completed reservations, total spend, most-booked
     * package, and a booking-count trend. "Visits" here means
     * 'confirmed' + 'done' bookings — pending/awaiting-verification ones
     * aren't a real visit yet, and cancelled ones are excluded.
     */
    private function bookingStats(int $userId): array
    {
        $upcoming = Booking::where('user_id', $userId)->where('status', 'confirmed')->get();
        $completed = Booking::where('user_id', $userId)->where('status', 'done')->get();
        $counted = $upcoming->concat($completed);

        $favoritePackageKey = $counted
            ->countBy(fn ($booking) => $booking->service . '|' . $booking->package)
            ->sortDesc()
            ->keys()
            ->first();

        $favoritePackageName = null;
        $favoritePackageCount = 0;

        if ($favoritePackageKey) {
            [$favService, $favPackage] = explode('|', $favoritePackageKey, 2);
            $favoritePackageName = $this->packageLabel($favService, $favPackage);
            $favoritePackageCount = $counted->filter(
                fn ($booking) => $booking->service === $favService && $booking->package === $favPackage
            )->count();
        }

        $bookingsByMonth = $counted->groupBy(fn ($booking) => $booking->visit_date->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $monthLabels = [];
        $monthValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthLabels[] = $month->format('M');
            $monthValues[] = (int) ($bookingsByMonth[$month->format('Y-m')] ?? 0);
        }

        // Category breakdown (DinoLakbay / Skataya / Field of
        // Rides) — a proportion-of-whole question, so this feeds a donut
        // chart rather than the bar chart above (which is a trend over
        // time and reads better as bars).
        $bookingsByCategory = $counted->countBy(fn ($booking) => $booking->service);

        $categoryLabels = [];
        $categoryValues = [];

        foreach ($bookingsByCategory as $serviceCode => $count) {
            $categoryLabels[] = $this->services[$serviceCode]['name'] ?? ucfirst(str_replace('_', ' ', $serviceCode));
            $categoryValues[] = $count;
        }

        return [
            'total_visits'            => $counted->count(),
            'upcoming_reservations'   => $upcoming->count(),
            'completed_reservations'  => $completed->count(),
            'total_spending'          => $counted->sum('price'),
            'favorite_package'        => $favoritePackageName,
            'favorite_package_count'  => $favoritePackageCount,
            'history_labels'          => $monthLabels,
            'history_values'          => $monthValues,
            'category_labels'         => $categoryLabels,
            'category_values'         => $categoryValues,
        ];
    }

    /**
     * Show a summary of a single booking before payment/confirmation.
     *
     * GET /user/bookings/{booking}/review
     */
    public function review(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        return view('user.booking-review', ['booking' => $this->formatBooking($bookingModel)]);
    }

    public function receipt(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $service = $this->services[$bookingModel->service] ?? null;

        return view('user.booking-receipt', [
            'booking' => [
                'id'             => $bookingModel->id,
                'service'        => $bookingModel->service,
                'reference'      => 'WP-' . str_pad((string) $bookingModel->id, 6, '0', STR_PAD_LEFT),
                'items'          => $bookingModel->items ?? [],
                'addons'         => $bookingModel->addons ?? [],
                'discount'       => (float) $bookingModel->discount_total,
                'service_name'   => $service['name'] ?? ucfirst(str_replace('_', ' ', $bookingModel->service)),
                'package_name'   => $this->packageLabel($bookingModel->service, $bookingModel->package),
                'date'           => $bookingModel->visit_date->format('M j, Y'),
                'time'           => $bookingModel->visit_time ? Carbon::parse($bookingModel->visit_time)->format('g:i A') : null,
                'pax'            => $bookingModel->tier,
                'price'          => '₱' . number_format((float) $bookingModel->price, 2),
                'payment_method' => strtoupper($bookingModel->payment_method ?? ''),
                'status'         => $bookingModel->status,
                'status_label'   => $this->statusMeta($bookingModel->status, $bookingModel->payment_rejection_reason)['label'],
                // The voucher code is only shown once the admin has approved the payment.
                'voucher_code'   => in_array($bookingModel->status, ['confirmed', 'done'], true)
                    ? $bookingModel->voucher_code
                    : null,
            ],
        ]);
    }

    /**
     * Submit the chosen payment method + proof of payment for a booking
     * (QR Ph, GCash, or Maya). Submitting proof does NOT confirm the
     * booking: it moves to "awaiting_verification" (shown to the customer
     * as "Waiting for Approval") until an admin checks the proof and
     * approves it (ReservationController@approvePayment -> 'confirmed')
     * or rejects it (back to 'pending_payment' with a reason so the
     * customer can upload a correct proof).
     * Booking flow: Booking -> Payment -> Waiver -> Waiting for Approval.
     *
     * POST /user/bookings/{booking}/payment
     */
    public function confirmPayment(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:qrph,gcash,maya'],
            'receipt'        => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'receipt.required' => 'Please upload a screenshot or receipt of your payment first.',
            'receipt.mimes'    => 'Proof of payment must be an image (JPG/PNG/WEBP) or PDF.',
            'receipt.max'      => 'Proof of payment must be 5MB or smaller.',
        ]);

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        // Proof can only be (re)submitted while the booking is still waiting
        // for payment or for admin approval — never against an already
        // approved/cancelled/done booking.
        if (!in_array($bookingModel->status, ['pending_payment', 'awaiting_verification'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', 'This booking is no longer awaiting payment.');
        }

        $path = $request->file('receipt')->store('payment-proofs', 'public');

        $bookingModel->update([
            'payment_method'           => $validated['payment_method'],
            'receipt_path'             => $path,
            'payment_proof_path'       => $path,
            'payment_submitted_at'     => now(),
            'payment_verified_at'      => null,
            'payment_rejection_reason' => null,
            'status'                   => 'awaiting_verification',
        ]);

        $this->emailProofReceived($bookingModel, $validated['payment_method']);

        return redirect()
            ->route('user.waiver')
            ->with('success', 'Payment proof sent! Please sign the waiver. Your booking will be confirmed once our team approves your payment.');
    }

    /**
     * Email the customer right after they send their proof of payment:
     * "we got it, it is waiting for approval, you can still reschedule".
     * Plain text through the app's configured mailer (same approach as the
     * 2FA / password emails), wrapped in try/catch so a mail problem never
     * blocks the booking flow.
     */
    private function emailProofReceived(Booking $booking, string $method): void
    {
        $user  = $booking->user;
        $email = $user->email ?? null;

        if (!$email) {
            return;
        }

        $methodLabel = ['qrph' => 'QR Ph', 'gcash' => 'GCash', 'maya' => 'Maya'][$method] ?? strtoupper($method);
        $serviceName = $this->services[$booking->service]['name'] ?? ucwords(str_replace('_', ' ', (string) $booking->service));
        $packageName = $this->packageLabel($booking->service, $booking->package);
        $when        = $booking->visit_date->format('F j, Y') . ($booking->visit_time ? ' at ' . $booking->visit_time : '');
        $price       = '₱' . number_format((float) $booking->price, 2);
        $name        = $user->fullname ?? 'there';
        $myBookings  = route('user.bookings');

        $body = "Hi {$name},\n\n"
              . "We received your proof of payment. Our team will check it, and we will email you as soon as your booking is confirmed.\n\n"
              . "Attraction: {$serviceName}\n"
              . "Package: {$packageName}\n"
              . "Date: {$when}\n"
              . "Guests: {$booking->tier}\n"
              . "Amount: {$price} via {$methodLabel}\n"
              . "Status: Waiting for Approval\n\n"
              . "While your booking is waiting for approval you can still reschedule or cancel it from My Bookings. "
              . "Once it is approved, it can no longer be rescheduled.\n\n"
              . "Your voucher code will be sent to you after approval.\n\n"
              . "My Bookings: {$myBookings}\n\n"
              . "Thank you,\nPistavora";

        try {
            Mail::raw($body, function ($message) use ($email) {
                $message->to($email)->subject('We received your Pistavora payment proof');
            });
        } catch (\Throwable $e) {
            Log::error('Proof received email failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an existing booking.
     *
     * POST /user/bookings/{booking}/cancel
     */
    public function cancel(int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if (in_array($bookingModel->status, ['confirmed', 'done'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', 'Confirmed bookings can no longer be cancelled. Please reschedule instead.');
        }

        $bookingModel->update(['status' => 'cancelled']);

        return redirect()
            ->route('user.bookings')
            ->with('success', 'Booking cancelled.');
    }

    /**
     * Show the date/time picker to reschedule an existing booking.
     *
     * GET /user/bookings/{booking}/reschedule
     */
    public function editReschedule(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if (!$this->canReschedule($bookingModel->status)) {
            return redirect()
                ->route('user.bookings')
                ->with('error', $this->rescheduleBlockedMessage($bookingModel->status));
        }

        $packageLabel = $this->packageLabel($bookingModel->service, $bookingModel->package);

        return view('user.reschedule', ['booking' => $bookingModel, 'packageLabel' => $packageLabel]);
    }

    /**
     * Save the new date/time chosen for an existing booking.
     *
     * POST /user/bookings/{booking}/reschedule
     */
    public function reschedule(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if (!$this->canReschedule($bookingModel->status)) {
            return redirect()
                ->route('user.bookings')
                ->with('error', $this->rescheduleBlockedMessage($bookingModel->status));
        }

        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['required', 'date_format:H:i'],
        ]);

        $bookingModel->update([
            'visit_date' => $validated['visit_date'],
            'visit_time' => $validated['visit_time'],
        ]);

        return redirect()
            ->route('user.bookings')
            ->with('success', 'Booking rescheduled.');
    }

    /**
     * Rescheduling is allowed only until the admin approves the payment:
     * while the booking is still waiting for payment, or waiting for
     * approval. Once it is confirmed (approved), done, or cancelled, the
     * schedule is locked.
     */
    private function canReschedule(string $status): bool
    {
        return in_array($status, ['pending_payment', 'awaiting_verification'], true);
    }

    private function rescheduleBlockedMessage(string $status): string
    {
        return match ($status) {
            'confirmed' => 'This booking has already been approved, so it can no longer be rescheduled.',
            'done'      => 'This voucher has already been used and can no longer be rescheduled.',
            'cancelled' => 'Cancelled bookings can no longer be rescheduled.',
            default     => 'This booking can no longer be rescheduled.',
        };
    }

    /**
     * Shape a Booking model into the display array expected by the booking
     * views: ['id','category','package','date','pax','status_label','status_class'].
     */
    private function formatBooking(Booking $booking): array
    {
        $meta = $this->statusMeta($booking->status, $booking->payment_rejection_reason);

        return [
            'id'               => $booking->id,
            'service'          => $booking->service,
            'reference'        => 'WP-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
            'time_label'       => $booking->visit_time ? Carbon::parse($booking->visit_time)->format('g:i A') : null,
            'category'         => $this->services[$booking->service]['name'] ?? ucfirst(str_replace('_', ' ', $booking->service)),
            'package'          => $this->packageLabel($booking->service, $booking->package),
            'date'             => $booking->visit_date->format('M j, Y'),
            'time'             => $booking->visit_time,
            'pax'              => $booking->tier,
            'price'            => '₱' . number_format((float) $booking->price, 2),
            'status'           => $booking->status,
            'status_label'     => $meta['label'],
            'status_class'     => $meta['class'],
            'voucher_code'     => $booking->voucher_code,
            'can_reschedule'   => $this->canReschedule($booking->status),
            'rejection_reason' => $booking->status === 'pending_payment' ? $booking->payment_rejection_reason : null,

            // Itemized receipt lines shown on the My Bookings tickets
            'lines'            => $this->bookingLines($booking),
            'extras'           => collect($booking->addons ?? [])->map(fn ($e) => [
                'name'       => $e['name'] ?? '',
                'qty'        => (int) ($e['qty'] ?? 1),
                'unit_price' => (float) ($e['unit_price'] ?? 0),
                'total'      => (float) ($e['line_total'] ?? 0),
            ])->values()->all(),
            'discount'         => (float) $booking->discount_total,

            // Used by the clickable "Rejected" popup on My Bookings
            'approval_status'  => $booking->approval_status,
            'reject_reason'    => $booking->reject_reason,
            'reject_note'      => $booking->reject_note,
        ];
    }

    /**
     * Map a booking status to its display label + tag color class.
     * 'awaiting_verification' is what the customer sees as "Waiting for
     * Approval"; a 'pending_payment' booking that has a rejection reason
     * means the admin rejected the proof and the customer must re-upload.
     */
    private function statusMeta(string $status, ?string $rejectionReason = null): array
    {
        if ($status === 'pending_payment' && $rejectionReason) {
            return ['label' => 'Payment rejected', 'class' => 'rose'];
        }

        return match ($status) {
            'pending_payment'       => ['label' => 'Pending payment', 'class' => 'amber'],
            'awaiting_verification' => ['label' => 'Waiting for Approval', 'class' => 'amber'],
            'confirmed'             => ['label' => 'Confirmed', 'class' => 'green'],
            'done'                  => ['label' => 'Done', 'class' => 'green'],
            'rejected'              => ['label' => 'Rejected', 'class' => 'rose'],
            'cancelled'             => ['label' => 'Cancelled', 'class' => 'rose'],
            default                 => ['label' => ucfirst(str_replace('_', ' ', $status)), 'class' => 'amber'],
        };
    }

    /**
     * Step-by-step wizard submit: creates the booking AND stores the QR
     * payment proof in one request, so the booking lands straight in
     * "awaiting_verification" (shown as "Waiting for Approval") for the
     * admin to review. Prices are recomputed here from the server catalog;
     * nothing price-related is trusted from the browser.
     *
     * Party rules (enforced here as well as in the UI):
     *   - party_type "party"                  -> party packages (flat price) ONLY
     *   - party_type solo / couple / group    -> per-person passes ONLY, no party packages
     *
     * Pack rules (Group Bundle): qty is the number of bundles, not the number of
     * guests. A bundle covers 5 guests, so qty may not exceed ceil(guests / 5).
     *
     * NOTE: if the bookings.party_type column is an ENUM, add 'party' to it
     * (see the migration note that comes with this file).
     *
     * POST /app/booking/submit   (multipart, expects JSON back)
     */
    public function submit(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['message' => 'Your session expired. Please log in again.'], 401);
        }

        $validated = $request->validate([
            'party_type' => ['required', 'in:solo,couple,group,party'],
            'guests'     => ['required', 'integer', 'min:1', 'max:100'],
            'items'      => ['required', 'string'],
            'extras'     => ['nullable', 'string'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'visit_time' => ['required', 'date_format:H:i'],
            'receipt'    => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'receipt.required' => 'Please upload a screenshot or receipt of your payment first.',
            'receipt.mimes'    => 'Proof of payment must be an image (JPG/PNG/WEBP) or PDF.',
            'receipt.max'      => 'Proof of payment must be 5MB or smaller.',
        ]);

        $guests = (int) $validated['guests'];
        $isParty = $validated['party_type'] === 'party';
        $expected = ['solo' => 1, 'couple' => 2];
        if (isset($expected[$validated['party_type']]) && $guests !== $expected[$validated['party_type']]) {
            return response()->json(['message' => 'Guest count does not match the party type.'], 422);
        }
        if ($validated['party_type'] === 'group' && $guests < 3) {
            return response()->json(['message' => 'A group needs at least 3 guests.'], 422);
        }
        if ($validated['party_type'] === 'party' && $guests < self::PARTY_MIN_GUESTS) {
            return response()->json(['message' => 'A party needs at least ' . self::PARTY_MIN_GUESTS . ' guests.'], 422);
        }

        // Opening hours: 9AM weekdays / 10AM weekends, last start 8:30PM.
        $date = Carbon::parse($validated['visit_date']);
        $minutes = (int) substr($validated['visit_time'], 0, 2) * 60 + (int) substr($validated['visit_time'], 3, 2);
        $openAt = $date->isWeekend() ? 10 * 60 : 9 * 60;
        if ($minutes < $openAt || $minutes > 20 * 60 + 30) {
            return response()->json(['message' => 'Please pick a time within park hours.'], 422);
        }
        if ($date->isToday() && $minutes <= now()->hour * 60 + now()->minute) {
            return response()->json(['message' => 'That time has already passed today.'], 422);
        }

        // Same catalog the wizard showed (incl. Group Bundle and TER), so lookups and prices match.
        $catalog = $this->withExtraItems(BookingWizard::catalog($this->packages, $this->addons, $this->services));
        $lookup  = BookingWizard::lookup($catalog);

        $rawItems  = json_decode($validated['items'], true);
        $rawExtras = json_decode($validated['extras'] ?? '[]', true);
        if (!is_array($rawItems) || !count($rawItems) || !is_array($rawExtras)) {
            return response()->json(['message' => 'Please pick at least one attraction.'], 422);
        }

        $lines = [];
        foreach ($rawItems as $row) {
            $key = (string) ($row['key'] ?? '');
            $qty = (int) ($row['qty'] ?? 0);
            $item = $lookup['items'][$key] ?? null;
            if (!$item || $qty < 1 || (empty($item['flat']) && $qty > $guests)) {
                return response()->json(['message' => 'One of the selected attractions is no longer available. Please review your picks.'], 422);
            }
            // Pack items (Group Bundle): one bundle covers N guests, so you never need more than ceil(guests / N).
            if (!empty($item['pack']) && !empty($item['covers']) && $qty > (int) ceil($guests / (int) $item['covers'])) {
                return response()->json(['message' => $item['name'] . ' covers ' . (int) $item['covers'] . ' guests per bundle. Please lower the quantity.'], 422);
            }
            if (!empty($item['flat'])) {
                // Party package: flat price for the tier that fits the party size.
                $tier = BookingWizard::tierFor($item, $guests);
                if (!$tier) {
                    return response()->json(['message' => $item['name'] . ' fits a limited number of guests. Please reduce your guests or pick per-person passes.'], 422);
                }
                if (($item['days'] ?? null) === 'weekday' && $date->isWeekend()) {
                    return response()->json(['message' => $item['name'] . ' is only available Monday to Friday.'], 422);
                }
                if (($item['days'] ?? null) === 'weekend' && !$date->isWeekend()) {
                    return response()->json(['message' => $item['name'] . ' is only available on weekends.'], 422);
                }
                $lines[$key] = ['qty' => 1] + array_merge($item, [
                    'unit_price' => (float) $tier['price'],
                    'tier_pax'   => (int) $tier['pax'],
                    'name'       => $item['name'] . ' (up to ' . (int) $tier['pax'] . ' guests)',
                ]);
                continue;
            }
            $lines[$key] = ['qty' => $qty] + $item;
        }

        // Party bookings may only contain party packages; every other party
        // type may only contain per-person passes.
        $hasPartyPackage = collect($lines)->contains(fn ($l) => !empty($l['flat']));
        $hasPersonalPass = collect($lines)->contains(fn ($l) => empty($l['flat']));
        if ($isParty && $hasPersonalPass) {
            return response()->json(['message' => 'A Party booking can only include party packages. Please remove the per-person passes.'], 422);
        }
        if (!$isParty && $hasPartyPackage) {
            return response()->json(['message' => 'Party packages can only be booked with the Party option. Please go back and choose Party.'], 422);
        }

        // A party package already includes play/skate access: one per service, no per-person passes with it.
        $flatPerService = [];
        foreach ($lines as $l) {
            if (!empty($l['flat'])) {
                $flatPerService[$l['service']] = ($flatPerService[$l['service']] ?? 0) + 1;
            }
        }
        foreach ($lines as $l) {
            $svc = $l['service'];
            if (empty($l['flat']) && isset($flatPerService[$svc])) {
                return response()->json(['message' => 'A party package already includes access, so it cannot be combined with per-person passes for the same attraction.'], 422);
            }
        }
        if (count(array_filter($flatPerService, fn ($n) => $n > 1))) {
            return response()->json(['message' => 'Please choose only one party package per attraction.'], 422);
        }

        $extras = [];
        foreach ($rawExtras as $row) {
            $key = (string) ($row['key'] ?? '');
            $qty = (int) ($row['qty'] ?? 0);
            if ($qty < 1) {
                continue;
            }
            if (!isset($lookup['extras'][$key]) || $qty > 100) {
                return response()->json(['message' => 'One of the selected extras is no longer available.'], 422);
            }
            $extras[$key] = ['qty' => $qty] + $lookup['extras'][$key];
        }

        $priced = BookingWizard::price(array_values($lines), array_values($extras), $guests);

        // The biggest line decides the "primary" service/package kept in the
        // legacy columns, so admin pages and the waiver keep working.
        $primary = collect($priced['lines'])->sortByDesc('line_total')->first();

        $path = $request->file('receipt')->store('payment-proofs', 'public');

        $booking = Booking::create([
            'user_id'              => session('user_id'),
            'service'              => $primary['service'],
            'package'              => $primary['package'],
            'tier'                 => $guests,
            'party_type'           => $validated['party_type'],
            'price'                => (int) round($priced['total']),
            'discount_total'       => $priced['discount_total'],
            'items'                => collect($priced['lines'])->map(fn ($l) => [
                'key' => $l['key'], 'service' => $l['service'], 'package' => $l['package'],
                'name' => $l['name'], 'unit_price' => $l['unit_price'], 'qty' => $l['qty'],
                'free_units' => $l['free_units'], 'line_total' => $l['line_total'],
            ])->values()->all(),
            'addons'               => collect($priced['extras'])->map(fn ($e) => [
                'key' => $e['key'], 'name' => $e['name'], 'unit_price' => $e['unit_price'],
                'qty' => $e['qty'], 'line_total' => $e['line_total'],
            ])->values()->all(),
            'visit_date'           => $validated['visit_date'],
            'visit_time'           => $validated['visit_time'],
            'payment_method'       => 'qrph',
            'receipt_path'         => $path,
            'payment_proof_path'   => $path,
            'payment_submitted_at' => now(),
            'status'               => 'awaiting_verification',
        ]);

        // The waiver (next step after this) reads these from the session.
        session([
            'booking_service' => $primary['service'],
            'booking_id'      => $booking->id,
        ]);

        $this->emailProofReceived($booking, 'qrph');

        return response()->json([
            'booking' => $this->wizardSummary($booking),
            'priced'  => ['total' => $priced['total']],
        ]);
    }

    /**
     * Polled by wizard step 5 so the voucher un-blurs as soon as the admin
     * approves the payment.
     *
     * GET /app/booking/{booking}/status
     */
    public function status(int $booking)
    {
        if (!session()->has('user_id')) {
            return response()->json(['message' => 'Please log in again.'], 401);
        }

        $model = Booking::where('user_id', session('user_id'))->find($booking);
        abort_if(!$model, 404);

        return response()->json($this->wizardSummary($model));
    }

    /**
     * Wizard-shaped view of a booking. The real voucher code is included
     * ONLY once the booking is approved (confirmed/done) — before that it
     * never leaves the server, so "blurred" can't be defeated by view-source.
     */
    private function wizardSummary(Booking $booking): array
    {
        $meta     = $this->statusMeta($booking->status, $booking->payment_rejection_reason);
        $approved = in_array($booking->status, ['confirmed', 'done'], true);

        // The Group Bundle is counted in bundles, not guests.
        $unitByPackage = [
            self::BUNDLE_PACKAGE => 'bundle',
        ];

        $items = collect($booking->items ?? [])->map(fn ($i) => [
            'name' => (($i['package'] ?? '') === self::BUNDLE_PACKAGE) ? 'Group Bundle 996' : $i['name'],
            'qty'  => $i['qty'], 'free' => $i['free_units'] ?? 0,
            'total' => (float) $i['line_total'],
            'unit'  => $unitByPackage[$i['package'] ?? ''] ?? null,
        ])->values()->all();

        // Bookings made with the old form have no items — fall back to the package.
        if (!count($items)) {
            $items = [[
                'name'  => $this->packageLabel($booking->service, $booking->package),
                'qty'   => (int) $booking->tier, 'free' => 0, 'total' => (float) $booking->price,
                'unit'  => null,
            ]];
        }

        $time = $booking->visit_time ? Carbon::parse($booking->visit_time)->format('g:i A') : null;

        return [
            'id'               => $booking->id,
            'reference'        => 'WP-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
            'status'           => $booking->status,
            'status_label'     => $meta['label'],
            'approved'         => $approved,
            'rejected'         => $booking->status === 'pending_payment' && (bool) $booking->payment_rejection_reason,
            'rejection_reason' => $booking->status === 'pending_payment' ? $booking->payment_rejection_reason : null,
            'voucher_code'     => $approved ? $booking->voucher_code : null,
            'guests'           => (int) $booking->tier,
            'party_type'       => $booking->party_type,
            'items'            => $items,
            'extras'           => collect($booking->addons ?? [])->map(fn ($e) => [
                'name' => $e['name'] ?? '', 'qty' => $e['qty'] ?? 1, 'total' => (float) ($e['line_total'] ?? 0),
            ])->values()->all(),
            'discount'         => (float) $booking->discount_total,
            'total'            => (float) $booking->price,
            'date_label'       => $booking->visit_date->format('D, M j, Y'),
            'time_label'       => $time,
        ];
    }

    public function packageInfo(string $service, string $package): array
    {
        return [
            'service_name' => $this->services[$service]['name'] ?? ucfirst(str_replace('_', ' ', $service)),
            'package_name' => $this->packageLabel($service, $package),
        ];
    }

    /**
     * Build the next 7 days for the date strip on the booking page.
     */
    protected function upcomingDates(): array
    {
        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $day = Carbon::today()->addDays($i);

            $dates[] = [
                'value' => $day->toDateString(),
                'day'   => $day->format('D'),
                'num'   => $day->format('j'),
            ];
        }

        return $dates;
    }
}