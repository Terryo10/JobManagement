<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MarketingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('marketing')
            ->path('marketing')
            ->login()
            ->colors(['primary' => Color::Rose])
            ->brandName('Household Media — Marketing')
            ->navigationGroups([
                NavigationGroup::make('Dashboard')->icon('heroicon-o-home'),
                NavigationGroup::make('Pipeline')->icon('heroicon-o-funnel'),
                NavigationGroup::make('Operations')->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make('Clients')->icon('heroicon-o-user-group'),
                NavigationGroup::make('Strategy')->icon('heroicon-o-light-bulb'),
                NavigationGroup::make('Reports')->icon('heroicon-o-document-chart-bar'),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Marketing/Resources'), for: 'App\\Filament\\Marketing\\Resources')
            ->discoverPages(in: app_path('Filament/Marketing/Pages'), for: 'App\\Filament\\Marketing\\Pages')
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
            ->discoverWidgets(in: app_path('Filament/Marketing/Widgets'), for: 'App\\Filament\\Marketing\\Widgets')
            ->widgets([])
            ->plugin(\Saade\FilamentFullCalendar\FilamentFullCalendarPlugin::make())
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
