<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;

class DatabaseAlert extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $body,
        protected string $icon = 'heroicon-o-bell',
        protected string $color = 'info',
        protected ?string $actionUrl = null,
        protected ?string $actionText = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Use the default Laravel notification broadcast channel
     * (App.Models.User.{id} → private) so Echo listeners pick it up.
     */
    public function broadcastOn(): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        $data = [
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'iconColor' => $this->color,
            'status' => $this->color,
            'duration' => 'persistent',
            'format' => 'filament',
        ];

        if ($this->actionUrl && $this->actionText) {
            $data['actions'] = [
                [
                    'name' => 'action',
                    'label' => $this->actionText,
                    'url' => $this->actionUrl,
                    'shouldOpenUrlInNewTab' => true,
                    'color' => $this->color,
                ]
            ];
        }

        return $data;
    }
}
