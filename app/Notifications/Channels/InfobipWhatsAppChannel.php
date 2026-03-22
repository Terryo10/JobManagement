<?php

namespace App\Notifications\Channels;

use App\Contracts\NotificationChannelContract;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\NotificationEvent;
use App\Services\InfobipClient;
use Throwable;

class InfobipWhatsAppChannel implements NotificationChannelContract
{
    public function __construct(private InfobipClient $client) {}

    public function send(User $recipient, NotificationEvent $event): void
    {
        // Prefer a dedicated WhatsApp number; fall back to SMS phone number.
        // Both live on the users table.
        $phone = $recipient->whatsapp_number
            ?? $recipient->phone_number
            ?? null;

        if (! $phone) {
            return;
        }

        // Sanitize phone to E.164 format (keep only + and digits)
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Use an approved template when specified, otherwise send free-form text.
        // Free-form only works within a 24-hour customer-initiated session window.
        $templateName = $event->extraData['whatsapp_template'] ?? null;

        try {
            if ($templateName) {
                $response = $this->client->sendWhatsAppTemplate(
                    to:           $phone,
                    templateName: $templateName,
                    placeholders: [$event->title, $event->body],
                );
            } else {
                $response = $this->client->sendWhatsAppText(
                    to:   $phone,
                    text: strip_tags("*{$event->title}*\n{$event->body}"),
                );
            }

            NotificationLog::create([
                'event_type'          => $event->type,
                'notifiable_type'     => User::class,
                'notifiable_id'       => $recipient->id,
                'subject_type'        => $event->subjectType,
                'subject_id'          => $event->subjectId,
                'channel'             => 'whatsapp',
                'status'              => 'sent',
                'provider_message_id' => $response['messages'][0]['messageId']
                    ?? $response['messageId']
                    ?? null,
                'payload'             => ['idempotency_key' => $event->idempotencyKey],
            ]);
        } catch (Throwable $e) {
            NotificationLog::create([
                'event_type'      => $event->type,
                'notifiable_type' => User::class,
                'notifiable_id'   => $recipient->id,
                'subject_type'    => $event->subjectType,
                'subject_id'      => $event->subjectId,
                'channel'         => 'whatsapp',
                'status'          => 'failed',
                'error'           => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
