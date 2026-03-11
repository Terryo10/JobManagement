<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\WorkOrder;
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
    }
}
