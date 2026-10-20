<?php
/**
 * apply_voucher_lock.php
 * Run from your laravel-app root: php apply_voucher_lock.php
 *
 * Ang POS side (PosController::checkout / lookupVoucher) ay tama na —
 * hindi na maga-gamit ulit ang isang voucher kapag naka-'done' na ang
 * status ng booking niya (naka-lock na rin sa loob ng DB transaction).
 *
 * Ang kulang: ang user-side reschedule (GET /user/bookings/{id}/reschedule
 * at POST /user/bookings/{id}/reschedule) ay walang status check kahit
 * saan — kaya kahit "done" na (nagamit na) o "cancelled" na ang booking,
 * puwede pa rin itong i-reschedule. Idinadagdag ng patch na ito ang
 * parehong check na ginagamit na ng cancel() sa magkabilang function.
 */

$root = __DIR__;

function patchFile($path, $old, $new, $label) {
    if (!file_exists($path)) {
        echo "[SKIP] $label — file not found: $path\n";
        return;
    }
    $content = file_get_contents($path);
    if (strpos($content, $new) !== false) {
        echo "[OK]   $label — na-patch na dati, walang ginalaw.\n";
        return;
    }
    if (strpos($content, $old) === false) {
        echo "[WARN] $label — hindi na-match yung expected old content. I-check manually.\n";
        return;
    }
    $content = str_replace($old, $new, $content);
    file_put_contents($path, $content);
    echo "[DONE] $label — na-patch.\n";
}

$bookingControllerPath = $root . '/app/Http/Controllers/User/BookingController.php';

// ---------- 1. editReschedule() (GET — yung page/form) ----------
$oldEdit = <<<'PHP'
    public function editReschedule(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $packageLabel = $this->packages[$bookingModel->service][$bookingModel->package]['name'] ?? $bookingModel->package;

        return view('user.reschedule', ['booking' => $bookingModel, 'packageLabel' => $packageLabel]);
    }
PHP;

$newEdit = <<<'PHP'
    public function editReschedule(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if (in_array($bookingModel->status, ['done', 'cancelled'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', $bookingModel->status === 'done'
                    ? 'This voucher has already been used and can no longer be rescheduled.'
                    : 'Cancelled bookings can no longer be rescheduled.');
        }

        $packageLabel = $this->packages[$bookingModel->service][$bookingModel->package]['name'] ?? $bookingModel->package;

        return view('user.reschedule', ['booking' => $bookingModel, 'packageLabel' => $packageLabel]);
    }
PHP;

patchFile($bookingControllerPath, $oldEdit, $newEdit, 'BookingController::editReschedule() — block redeemed/cancelled bookings from opening the reschedule form');

// ---------- 2. reschedule() (POST — yung actual save) ----------
// Kailangan din i-check dito, hindi lang sa GET page, dahil kaya pa ring
// i-POST diretso ang request kahit naka-block na ang form sa UI.
$oldSave = <<<'PHP'
    public function reschedule(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

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
PHP;

$newSave = <<<'PHP'
    public function reschedule(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if (in_array($bookingModel->status, ['done', 'cancelled'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', $bookingModel->status === 'done'
                    ? 'This voucher has already been used and can no longer be rescheduled.'
                    : 'Cancelled bookings can no longer be rescheduled.');
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
PHP;

patchFile($bookingControllerPath, $oldSave, $newSave, 'BookingController::reschedule() — block redeemed/cancelled bookings from actually saving a reschedule');

echo "\nTapos na. I-check ang dalawang [DONE]/[OK] lines sa itaas.\n";
