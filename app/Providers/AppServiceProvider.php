<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\StaffAvailability;
use App\Models\Task;
use App\Models\WorkOrder;
use App\Observers\ExpenseObserver;
use App\Observers\StaffAvailabilityObserver;
use App\Observers\TaskObserver;
use App\Observers\WorkOrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        WorkOrder::observe(WorkOrderObserver::class);
        Task::observe(TaskObserver::class);
        Expense::observe(ExpenseObserver::class);
        StaffAvailability::observe(StaffAvailabilityObserver::class);
    }
}
