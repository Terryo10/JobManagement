<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelContract;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\NotificationEvent;
use App\Services\InfobipClient;
use Throwable;

class InfobipSmsChannel implements NotificationChannelContract
{
    public function __construct(private InfobipClient $client) {}

    public function send(User $recipient, NotificationEvent $event): void
    {
        // Phone number lives on the users table; the preference table only stores channel on/off.
        $phone = $recipient->phone_number ?? null;

        if (! $phone) {
            return;
        }

        // Sanitize phone to E.164 format (keep only + and digits)
        $phone = preg_replace('/[^\d+]/', '', $phone);

        $text = strip_tags("{$event->title}\n{$event->body}");

        try {
            $response = $this->client->sendSms($phone, $text);

            NotificationLog::create([
                'event_type'          => $event->type,
                'notifiable_type'     => User::class,
                'notifiable_id'       => $recipient->id,
                'subject_type'        => $event->subjectType,
                'subject_id'          => $event->subjectId,
                'channel'             => 'sms',
                'status'              => 'sent',
                'provider_message_id' => $response['messages'][0]['messageId'] ?? null,
                'payload'             => [
                    'idempotency_key' => $event->idempotencyKey,
                    'subject'         => $event->title,
                    'body'            => $event->body,
                ],
            ]);
        } catch (Throwable $e) {
            NotificationLog::create([
                'event_type'      => $event->type,
                'notifiable_type' => User::class,
                'notifiable_id'   => $recipient->id,
                'subject_type'    => $event->subjectType,
                'subject_id'      => $event->subjectId,
                'channel'         => 'sms',
                'status'          => 'failed',
                'error'           => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
