<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{
    // ─── SHARED NOTIFICATION HELPER ────────────────────────
    // Parehong pattern gaya ng notify() sa InventoryController at
    // PosController — naka try/catch para hindi ito makasira sa
    // pangunahing booking action (create/update/delete) kahit mag-fail
    // ang broadcast (hal. kapag hindi tumatakbo ang Reverb server).
    private function notify($title, $message, $url = null)
    {
        try {
            $notif = \App\Models\Notification::create([
                'type'    => 'booking',
                'title'   => $title,
                'message' => $message,
                'url'     => $url ?: route('reservations.index'),
            ]);
            broadcast(new \App\Events\NewSystemNotification($notif));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('BookingObserver notification/broadcast failed: ' . $e->getMessage());
        }
    }

    /**
     * Kumpletong detalye ng isang booking bilang lines ng text — ginagamit
     * ng created/updated/deleted para laging kumpleto ang info sa popup.
     */
    private function bookingDetailLines(Booking $booking): array
    {
        $customerName = $booking->display_customer->fullname ?? 'Unknown Customer';

        return [
            "Customer: {$customerName}",
            "Service: " . $this->readable($booking->service),
            "Package: " . $this->readable($booking->package),
            "Tier: {$booking->tier}",
            "Visit Date: " . $this->formatDate($booking->visit_date),
            "Visit Time: {$booking->visit_time}",
            "Price: ₱" . number_format((float) $booking->price, 2),
            "Status: " . $this->readable($booking->status),
        ];
    }

    /**
     * Turns "dino_adventure" / "pending_payment" into "DinoLakbay" /
     * "Pending Payment" for display in the notification popup.
     */
    private function readable(?string $value): string
    {
        if (!$value) {
            return '—';
        }

        return ucwords(str_replace('_', ' ', $value));
    }

    /**
     * Formats visit_date for display regardless of whether the Booking
     * model casts it to a Carbon instance or leaves it as a plain string
     * — never throws either way.
     */
    private function formatDate($value): string
    {
        if (!$value) {
            return '—';
        }

        if ($value instanceof \Carbon\Carbon || $value instanceof \Illuminate\Support\Carbon) {
            return $value->format('M d, Y');
        }

        try {
            return \Illuminate\Support\Carbon::parse((string) $value)->format('M d, Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $lines = $this->bookingDetailLines($booking);

        $this->notify(
            'New Booking/Reservation',
            "A new booking was made.\n" . implode("\n", $lines)
        );
    }

    /**
     * Handle the Booking "updated" event.
     * Covers both plain field edits AND cancellations (status -> cancelled)
     * — cancellations get their own title so they stand out.
     */
    public function updated(Booking $booking): void
    {
        // getChanges() reflects the fields that changed in THIS save —
        // reliable inside an Eloquent observer without needing a
        // separately-fetched "old" copy.
        $changes = $booking->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return; // walang tunay na binago, iwasan ang walang-silbing notif
        }

        $isCancelled = array_key_exists('status', $changes)
            && str_contains(strtolower((string) $booking->status), 'cancel');

        $dateFields  = ['visit_date'];
        $priceFields = ['price'];
        $labelFields = ['service', 'package', 'status']; // human-readable underscore fields

        $changeLines = [];
        foreach ($changes as $field => $newValue) {
            $original = $booking->getOriginal($field);
            $label    = ucwords(str_replace('_', ' ', $field));

            if (in_array($field, $dateFields, true)) {
                $changeLines[] = "{$label}: " . $this->formatDate($original) . " → " . $this->formatDate($newValue);
            } elseif (in_array($field, $priceFields, true)) {
                $changeLines[] = "{$label}: ₱" . number_format((float) $original, 2) . " → ₱" . number_format((float) $newValue, 2);
            } elseif (in_array($field, $labelFields, true)) {
                $changeLines[] = "{$label}: " . $this->readable($original) . " → " . $this->readable($newValue);
            } else {
                $changeLines[] = "{$label}: {$original} → {$newValue}";
            }
        }

        $lines   = $this->bookingDetailLines($booking);
        $lines[] = "Changed: " . implode(', ', $changeLines);

        if ($isCancelled) {
            $this->notify('Booking Cancelled', "A booking was cancelled.\n" . implode("\n", $lines));
        } else {
            $this->notify('Booking Updated', "A booking was updated.\n" . implode("\n", $lines));
        }
    }

    /**
     * Handle the Booking "deleted" event.
     * Fires when a booking record is actually removed (not just marked
     * cancelled — that's covered by updated() above).
     */
    public function deleted(Booking $booking): void
    {
        $lines = $this->bookingDetailLines($booking);

        $this->notify(
            'Booking Deleted',
            "A booking was removed from the system.\n" . implode("\n", $lines)
        );
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        //
    }
}