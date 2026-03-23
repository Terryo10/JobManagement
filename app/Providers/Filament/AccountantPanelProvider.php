<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AccountantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('accountant')
            ->path('accountant')
            ->login()
            ->colors(['primary' => Color::Green])
            ->brandName('Household Media — Finance')
            ->plugin(FilamentFullCalendarPlugin::make())
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Accountant/Resources'), for: 'App\\Filament\\Accountant\\Resources')
            ->discoverPages(in: app_path('Filament/Accountant/Pages'), for: 'App\\Filament\\Accountant\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\MyNotificationPreferences::class,
            ])
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label('Notification Preferences')
                    ->url(fn (): string => \App\Filament\Pages\MyNotificationPreferences::getUrl())
                    ->icon('heroicon-o-bell-alert'),
            ])
            ->discoverWidgets(in: app_path('Filament/Accountant/Widgets'), for: 'App\\Filament\\Accountant\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
