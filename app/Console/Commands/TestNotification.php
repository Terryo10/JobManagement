<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'notifications:test
                            {--user= : User ID or email to send to (defaults to first super_admin)}
                            {--channel=database : Channels to test: database,mail,sms,whatsapp (comma-separated)}
                            {--phone= : Override phone number for SMS test}';

    protected $description = 'Send a test notification through specified channels to verify configuration';

    public function handle(): int
    {
        $userQuery = $this->option('user');
        $user = $userQuery
            ? User::where('id', $userQuery)->orWhere('email', $userQuery)->firstOrFail()
            : User::role('super_admin')->firstOrFail();

        $this->info("Sending test notification to: {$user->name} <{$user->email}>");

        // Temporarily override phone number on the users table
        if ($this->option('phone')) {
            $user->update(['phone_number' => $this->option('phone')]);
            $this->line("  Phone set to: " . $this->option('phone'));
        }

        $channels = array_map('trim', explode(',', $this->option('channel')));
        $this->line("  Channels: " . implode(', ', $channels));

        // Force-enable the requested channels for this test type
        \App\Models\NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'notification_type' => 'test.notification'],
            array_merge(
                ['channel_database' => false, 'channel_mail' => false, 'channel_sms' => false, 'channel_whatsapp' => false],
                collect($channels)->mapWithKeys(fn ($c) => ["channel_{$c}" => true])->toArray()
            )
        );

        $logsBefore = NotificationLog::count();

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'test.notification',
            title:            '🔔 Test Notification',
            body:             'This is a test from the JobManagement notification system. If you see this, the channel is working.',
            icon:             'heroicon-o-check-circle',
            color:            'success',
            actionUrl:        url('/'),
            actionText:       'Open App',
            recipientUserIds: [$user->id],
            priority:         'high',
        ));

        $logsAfter = NotificationLog::count();
        $newLogs   = $logsAfter - $logsBefore;

        $this->info("✅ Dispatched. New log entries: {$newLogs}");
        $this->line("   Note: email/SMS/WhatsApp are queued — run 'php artisan queue:work' if not using sync driver.");

        return self::SUCCESS;
    }
}
