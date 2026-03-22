<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\StaffAvailability;
use App\Models\StockLevel;
use App\Models\Task;
use App\Models\WorkOrder;
use App\Notifications\Channels\FilamentDatabaseChannel;
use App\Observers\ExpenseObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\StaffAvailabilityObserver;
use App\Observers\StockLevelObserver;
use App\Observers\TaskObserver;
use App\Observers\WorkOrderObserver;
use App\Services\InfobipClient;
use App\Services\NotificationRouter;
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
        WorkOrder::observe(WorkOrderObserver::class);
        Task::observe(TaskObserver::class);
        StaffAvailability::observe(StaffAvailabilityObserver::class);
        StockLevel::observe(StockLevelObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        Expense::observe(ExpenseObserver::class);
    }
}
