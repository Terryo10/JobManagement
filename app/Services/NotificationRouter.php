<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\Channels\FilamentDatabaseChannel;
use App\Notifications\NotificationEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;

class NotificationRouter
{
    /**
     * Fallback defaults when an event type has no entry in config/notifications.php.
     */
    private const FALLBACK_DEFAULTS = [
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ];

    public function __construct(private FilamentDatabaseChannel $database) {}

    /**
     * Resolve recipients, apply preferences, and dispatch per-channel sends.
     * Database notifications are sent synchronously; email/SMS/WhatsApp are queued.
     */
    public function dispatch(NotificationEvent $event): void
    {
        $recipients = $this->resolveRecipients($event);

        foreach ($recipients as $recipient) {
            $prefs = $this->getPreferences($recipient, $event->type);

            foreach (['database', 'mail', 'sms', 'whatsapp'] as $channel) {
                if (! ($prefs["channel_{$channel}"] ?? false)) {
                    continue;
                }

                if ($this->isDuplicate($recipient, $event, $channel)) {
                    continue;
                }

                if (in_array($channel, ['sms', 'whatsapp']) && $this->isInQuietHours($recipient)) {
                    continue;
                }

                if ($channel === 'sms' && ! $this->attemptSmsRateLimit($recipient->id)) {
                    continue;
                }

                if ($channel === 'database') {
                    // Send in-app notifications immediately — same as existing behaviour.
                    $this->database->send($recipient, $event);
                } else {
                    // Email / SMS / WhatsApp are dispatched to the queue.
                    $queue = $event->priority === 'critical' ? 'critical' : 'notifications';
                    SendNotificationJob::dispatch($recipient->id, $event, $channel)->onQueue($queue);
                }
            }
        }
    }

    // -------------------------------------------------------------------------

    private function resolveRecipients(NotificationEvent $event): \Illuminate\Support\Collection
    {
        $users = collect();

        if (! empty($event->recipientUserIds)) {
            $users = $users->merge(
                User::whereIn('id', $event->recipientUserIds)->get()
            );
        }

        foreach ($event->recipientRoles as $role) {
            $users = $users->merge(User::role($role)->get());
        }

        return $users->unique('id');
    }

    private function getPreferences(User $user, string $type): array
    {
        // Event types contain dots (e.g. "task.assigned"), which Laravel config() interprets
        // as nested keys. Load the top-level array and look up directly instead.
        $configDefaults = config('notifications')[$type] ?? self::FALLBACK_DEFAULTS;

        $defaults = [
            'channel_database' => $configDefaults['channel_database'] ?? true,
            'channel_mail'     => $configDefaults['channel_mail']     ?? true,
            'channel_sms'      => $configDefaults['channel_sms']      ?? false,
            'channel_whatsapp' => $configDefaults['channel_whatsapp'] ?? false,
        ];

        $pref = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id, 'notification_type' => $type],
            $defaults,
        );

        return array_merge($defaults, $pref->only([
            'channel_database', 'channel_mail', 'channel_sms', 'channel_whatsapp',
        ]));
    }

    private function isDuplicate(User $user, NotificationEvent $event, string $channel): bool
    {
        if (! $event->idempotencyKey) {
            return false;
        }

        return NotificationLog::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('event_type', $event->type)
            ->where('channel', $channel)
            ->where('status', '!=', 'failed')
            ->whereJsonContains('payload->idempotency_key', $event->idempotencyKey)
            ->exists();
    }

    /**
     * Enforce per-user quiet hours for SMS/WhatsApp only.
     * Reads from users.notification_quiet_hours (JSON: {"start":"22:00","end":"07:00","tz":"Africa/Harare"}).
     */
    private function isInQuietHours(User $user): bool
    {
        $quietHours = $user->notification_quiet_hours;

        if (! $quietHours || ! isset($quietHours['start'], $quietHours['end'])) {
            return false;
        }

        $tz    = $quietHours['tz'] ?? 'UTC';
        $now   = Carbon::now($tz);
        $start = Carbon::parse($quietHours['start'], $tz)->setDate($now->year, $now->month, $now->day);
        $end   = Carbon::parse($quietHours['end'], $tz)->setDate($now->year, $now->month, $now->day);

        // Handle overnight window (e.g. 22:00 – 07:00)
        if ($start->gt($end)) {
            return $now->gte($start) || $now->lte($end);
        }

        return $now->between($start, $end);
    }

    /**
     * Enforce per-user SMS rate limit: max 10 SMS per hour.
     */
    public function attemptSmsRateLimit(int $userId): bool
    {
        return RateLimiter::attempt(
            key:      "sms_notify_{$userId}",
            maxAttempts: 10,
            callback: fn () => true,
            decaySeconds: 3600,
        );
    }
}
