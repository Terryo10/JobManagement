<?php

namespace App\Filament\Marketing\Pages\Reports;

use App\Filament\Marketing\Resources\WorkOrderResource;
use App\Filament\Shared\Pages\BaseJobCardReport;

class JobCardReport extends BaseJobCardReport
{
    protected static function workOrderResourceClass(): string
    {
        return WorkOrderResource::class;
    }
}
