<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\View\View;

class NotificationComposer
{
    /**
     * Bind data to the layout view.
     *
     * Keep this list short (take(10)) — it renders inside the sidebar/dropdown
     * on every page load, so we don't want to pull the whole table each time.
     * The full list lives on the "View all" page via NotificationController::index().
     */
    public function compose(View $view): void
    {
        $view->with('notifications', Notification::latestFirst()->take(10)->get());
    }
}