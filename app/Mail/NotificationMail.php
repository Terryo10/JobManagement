<?php

namespace App\Mail;

use App\Notifications\NotificationEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly NotificationEvent $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->event->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notification', with: [
            'title'      => $this->event->title,
            'body'       => $this->event->body,
            'actionUrl'  => $this->event->actionUrl,
            'actionText' => $this->event->actionText,
            'color'      => $this->event->color,
        ]);
    }
}
