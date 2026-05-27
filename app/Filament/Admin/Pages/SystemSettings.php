<?php

namespace App\Filament\Admin\Pages;

use App\Models\SystemSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 99;
    protected static string  $view            = 'filament.admin.pages.system-settings';

    public bool $smsEnabled = true;

    public function mount(): void
    {
        $this->smsEnabled = SystemSetting::smsEnabled();
    }

    public function toggleSms(): void
    {
        $this->smsEnabled = ! $this->smsEnabled;

        SystemSetting::setValue('sms_enabled', $this->smsEnabled ? '1' : '0');

        Notification::make()
            ->title($this->smsEnabled ? 'SMS Enabled' : 'SMS Disabled')
            ->body(
                $this->smsEnabled
                    ? 'SMS notifications are now active across all dashboards.'
                    : 'All SMS notifications have been disabled platform-wide. No SMS will be sent until re-enabled.'
            )
            ->icon($this->smsEnabled ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
            ->color($this->smsEnabled ? 'success' : 'danger')
            ->send();
    }
}
