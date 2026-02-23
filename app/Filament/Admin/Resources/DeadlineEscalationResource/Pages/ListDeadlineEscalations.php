<?php

namespace App\Filament\Admin\Resources\DeadlineEscalationResource\Pages;

use App\Filament\Admin\Resources\DeadlineEscalationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeadlineEscalations extends ListRecords
{
    protected static string $resource = DeadlineEscalationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
