<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SystemActivityNotification extends Notification
{
    use Queueable;

    protected string $module;
    protected string $action;
    protected string $message;
    protected ?array $meta;

    public function __construct(string $module, string $action, string $message, ?array $meta = null)
    {
        $this->module = $module;
        $this->action = $action;
        $this->message = $message;
        $this->meta = $meta;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'module' => $this->module,
            'action' => $this->action,
            'message' => $this->message,
            'meta' => $this->meta,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'module' => $this->module,
            'action' => $this->action,
            'message' => $this->message,
            'meta' => $this->meta,
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    public function broadcastOn(): array
    {
        return [new \Illuminate\Broadcasting\PrivateChannel('admin-notifications')];
    }
}