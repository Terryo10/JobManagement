<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelContract;
use App\Mail\NotificationMail;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\NotificationEvent;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailChannel implements NotificationChannelContract
{
    public function send(User $recipient, NotificationEvent $event): void
    {
        if (! $recipient->email) {
            return;
        }

        try {
            Mail::to($recipient->email)->send(new NotificationMail($event));

            NotificationLog::create([
                'event_type'      => $event->type,
                'notifiable_type' => User::class,
                'notifiable_id'   => $recipient->id,
                'subject_type'    => $event->subjectType,
                'subject_id'      => $event->subjectId,
                'channel'         => 'mail',
                'status'          => 'sent',
                'payload'         => ['idempotency_key' => $event->idempotencyKey],
            ]);
        } catch (Throwable $e) {
            NotificationLog::create([
                'event_type'      => $event->type,
                'notifiable_type' => User::class,
                'notifiable_id'   => $recipient->id,
                'subject_type'    => $event->subjectType,
                'subject_id'      => $event->subjectId,
                'channel'         => 'mail',
                'status'          => 'failed',
                'error'           => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
