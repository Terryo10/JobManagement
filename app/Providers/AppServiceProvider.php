<?php

namespace App\Providers;

use App\Models\AdminTask;
use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\StaffAvailability;
use App\Models\StockLevel;
use App\Models\Task;
use App\Models\WorkOrder;
use App\Notifications\Channels\FilamentDatabaseChannel;
use App\Observers\AdminTaskObserver;
use App\Observers\AnnouncementCommentObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\ExpenseObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\StaffAvailabilityObserver;
use App\Observers\StockLevelObserver;
use App\Observers\TaskObserver;
use App\Observers\WorkOrderObserver;
use App\Services\InfobipClient;
use App\Services\NotificationRouter;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InfobipClient::class);

        $this->app->singleton(NotificationRouter::class, function ($app) {
            return new NotificationRouter(
                database: $app->make(FilamentDatabaseChannel::class),
            );
        });
    }

    public function boot(): void
    {
        AdminTask::observe(AdminTaskObserver::class);
        Announcement::observe(AnnouncementObserver::class);
        AnnouncementComment::observe(AnnouncementCommentObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
        Task::observe(TaskObserver::class);
        StaffAvailability::observe(StaffAvailabilityObserver::class);
        StockLevel::observe(StockLevelObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        Expense::observe(ExpenseObserver::class);

        // Inject the Reverb Echo connection + notification sound into every panel.
        // echo.js sets window.Echo; the partial uses it to subscribe to the private
        // user channel and plays a two-tone ping on each incoming notification.
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render(
                "@vite('resources/js/echo.js')\n" .
                "@include('filament.partials.notification-sound')"
            ),
        );
    }
}
