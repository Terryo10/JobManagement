<?php

namespace App\Contracts;

use App\Models\User;
use App\Notifications\NotificationEvent;

interface NotificationChannelContract
{
    public function send(User $recipient, NotificationEvent $event): void;
}
