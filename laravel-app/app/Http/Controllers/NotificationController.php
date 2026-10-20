<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Standalone notifications dashboard.
     * Supports ?status=all|unread|read, ?type=all|booking|inventory|pos
     * and ?q=search term (matches title or message).
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $type = $request->query('type', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = Notification::latestFirst();

        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        if (in_array($type, ['booking', 'inventory', 'pos'], true)) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            // Escape LIKE wildcards so "%" and "_" are searched literally.
            $like = '%' . addcslashes($search, '\\%_') . '%';

            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('message', 'like', $like);
            });
        }

        $notifications = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => Notification::count(),
            'unread'    => Notification::unread()->count(),
            'booking'   => Notification::where('type', 'booking')->count(),
            'inventory' => Notification::where('type', 'inventory')->count(),
            'pos'       => Notification::where('type', 'pos')->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats', 'status', 'type', 'search'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Notification $notification): JsonResponse|RedirectResponse
    {
        $notification->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Mark-all-read (JSON for sidebar fetch, redirect for form submit).
     */
    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        Notification::unread()->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Notification $notification): JsonResponse|RedirectResponse
    {
        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Bulk mark-as-read for selected notifications.
     */
    public function bulkMarkRead(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:notifications,id'],
        ]);

        $updated = Notification::whereIn('id', $validated['ids'])->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'updated' => $updated]);
        }

        return back()->with('status', $updated . ' notification(s) marked as read.');
    }

    /**
     * Bulk-delete selected notifications.
     */
    public function bulkDestroy(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:notifications,id'],
        ]);

        $deleted = Notification::whereIn('id', $validated['ids'])->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'deleted' => $deleted]);
        }

        return back()->with('status', $deleted . ' notification(s) deleted.');
    }

    /**
     * Poll endpoint for the sidebar bell dropdown.
     */
    public function poll(Request $request): JsonResponse
    {
        $notifications = Notification::latestFirst()
            ->limit(6)
            ->get()
            ->map(function (Notification $n) {
                return [
                    'id'       => $n->id,
                    'title'    => $n->title,
                    'message'  => $n->message,
                    'type'     => $n->type,
                    'url'      => $n->url,
                    'is_read'  => (bool) $n->is_read,
                    'time'     => $n->created_at->diffForHumans(),
                    'read_url' => route('notifications.read', $n->id),
                ];
            });

        return response()->json([
            'unread_count'  => Notification::unread()->count(),
            'total_count'   => Notification::count(),
            'notifications' => $notifications,
        ]);
    }
}