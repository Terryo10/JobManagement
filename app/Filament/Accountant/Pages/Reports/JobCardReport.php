<?php

namespace App\Filament\Accountant\Pages\Reports;

use App\Filament\Accountant\Resources\WorkOrderResource;
use App\Filament\Shared\Pages\BaseJobCardReport;

class JobCardReport extends BaseJobCardReport
{
    protected static function workOrderResourceClass(): string
    {
        return WorkOrderResource::class;
    }
}
