<?php

namespace Database\Seeders;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed notification preferences for all existing users using the defaults
 * defined in config/notifications.php.
 *
 * Safe to run multiple times — uses updateOrCreate so it won't overwrite
 * preferences that users have already customised.
 */
class NotificationPreferencesSeeder extends Seeder
{
    public function run(): void
    {
        $eventTypes = array_keys(config('notifications', []));
        $users      = User::all();

        $created = 0;
        $skipped = 0;

        foreach ($users as $user) {
            foreach ($eventTypes as $type) {
                $defaults = config('notifications')[$type] ?? [];

                $pref = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_type', $type)
                    ->first();

                if ($pref) {
                    // Row already exists — leave user customisations intact.
                    $skipped++;
                    continue;
                }

                NotificationPreference::create([
                    'user_id'           => $user->id,
                    'notification_type' => $type,
                    'channel_database'  => $defaults['channel_database']  ?? true,
                    'channel_mail'      => $defaults['channel_mail']      ?? true,
                    'channel_sms'       => $defaults['channel_sms']       ?? false,
                    'channel_whatsapp'  => $defaults['channel_whatsapp']  ?? false,
                ]);

                $created++;
            }
        }

        $this->command->info("NotificationPreferences: created {$created} rows, skipped {$skipped} existing.");
    }
}
