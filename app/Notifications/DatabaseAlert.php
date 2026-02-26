<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DatabaseAlert extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $body,
        protected string $icon = 'heroicon-o-bell',
        protected string $color = 'info',
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'iconColor' => $this->color,
            'status' => $this->color,
            'duration' => 'persistent',
            'format' => 'filament',
        ];
    }
}
