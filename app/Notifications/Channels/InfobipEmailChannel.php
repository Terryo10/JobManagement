<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelContract;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\NotificationEvent;
use App\Services\InfobipClient;
use Illuminate\Support\Facades\View;
use Throwable;

class InfobipEmailChannel implements NotificationChannelContract
{
    public function __construct(private InfobipClient $client) {}

    public function send(User $recipient, NotificationEvent $event): void
    {
        if (! $recipient->email) {
            return;
        }

        try {
            $html = View::make('emails.notification', [
                'title'      => $event->title,
                'body'       => $event->body,
                'actionUrl'  => $event->actionUrl,
                'actionText' => $event->actionText,
                'color'      => $event->color,
            ])->render();

            $this->client->sendEmail($recipient->email, $event->title, $html);

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
