<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelContract;
use App\Models\User;
use App\Notifications\DatabaseAlert;
use App\Notifications\NotificationEvent;

class FilamentDatabaseChannel implements NotificationChannelContract
{
    public function send(User $recipient, NotificationEvent $event): void
    {
        $recipient->notify(new DatabaseAlert(
            title:      $event->title,
            body:       $event->body,
            icon:       $event->icon,
            color:      $event->color,
            actionUrl:  $event->actionUrl,
            actionText: $event->actionText,
        ));
    }
}
