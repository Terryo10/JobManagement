<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\Channels\InfobipSmsChannel;
use App\Notifications\Channels\InfobipWhatsAppChannel;
use App\Notifications\Channels\MailChannel;
use App\Notifications\NotificationEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public int               $userId,
        public NotificationEvent $event,
        public string            $channel,
    ) {}

    public function handle(
        MailChannel             $mail,
        InfobipSmsChannel       $sms,
        InfobipWhatsAppChannel  $whatsapp,
    ): void {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $adapter = match ($this->channel) {
            'mail'      => $mail,
            'sms'       => $sms,
            'whatsapp'  => $whatsapp,
            default     => null,
        };

        $adapter?->send($user, $this->event);
    }

    public function failed(Throwable $e): void
    {
        // Only log here if retries are exhausted (the channel itself logs on each attempt).
        // This final record marks the job as permanently failed.
        NotificationLog::create([
            'event_type'      => $this->event->type,
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->userId,
            'subject_type'    => $this->event->subjectType,
            'subject_id'      => $this->event->subjectId,
            'channel'         => $this->channel,
            'status'          => 'failed',
            'error'           => "Job exhausted retries: {$e->getMessage()}",
        ]);
    }
}
