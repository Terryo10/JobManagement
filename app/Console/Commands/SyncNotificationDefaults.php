<?php

namespace App\Console\Commands;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Syncs notification_preferences rows to match the current config/notifications.php defaults.
 *
 * Safe to run multiple times. Use --force to also update rows that already exist
 * (useful when shipping new config defaults to a live system).
 *
 * Usage:
 *   php artisan notifications:sync-defaults         # only creates missing rows
 *   php artisan notifications:sync-defaults --force # creates + updates all rows to config defaults
 */
class SyncNotificationDefaults extends Command
{
    protected $signature = 'notifications:sync-defaults {--force : Overwrite existing rows with config defaults}';
    protected $description = 'Sync notification preference defaults from config/notifications.php to all users';

    public function handle(): int
    {
        $eventTypes = array_keys(config('notifications', []));
        $users      = User::all();
        $force      = $this->option('force');

        $created = 0;
        $updated = 0;

        $this->info('Syncing ' . count($eventTypes) . ' event types for ' . $users->count() . ' users...');

        foreach ($users as $user) {
            foreach ($eventTypes as $type) {
                $defaults = config('notifications')[$type] ?? [];

                $row = [
                    'channel_database'  => $defaults['channel_database']  ?? true,
                    'channel_mail'      => $defaults['channel_mail']      ?? true,
                    'channel_sms'       => $defaults['channel_sms']       ?? false,
                    'channel_whatsapp'  => $defaults['channel_whatsapp']  ?? false,
                ];

                $existing = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_type', $type)
                    ->first();

                if (! $existing) {
                    NotificationPreference::create(array_merge(
                        ['user_id' => $user->id, 'notification_type' => $type],
                        $row,
                    ));
                    $created++;
                } elseif ($force) {
                    $existing->update($row);
                    $updated++;
                }
            }
        }

        $this->info("Done. Created: {$created}, Updated: {$updated}.");

        return self::SUCCESS;
    }
}
