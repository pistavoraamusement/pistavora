<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NewSystemNotification implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin-notifications')];
    }

    public function broadcastAs(): string
    {
        return 'new-notification';
    }

    public function broadcastWith(): array
    {
        return [
            'id'      => $this->notification->id,
            'type'    => $this->notification->type,
            'title'   => $this->notification->title,
            'message' => $this->notification->message,
            'url'     => $this->notification->url,
            'time'    => $this->notification->time ?: 'now',
        ];
    }
}
