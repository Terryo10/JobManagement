<?php

namespace App\Filament\Staff\Resources\DesignBriefResource\Pages;

use App\Filament\Staff\Resources\DesignBriefResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDesignBrief extends ViewRecord
{
    protected static string $resource = DesignBriefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->visible(fn ($record) => $record->designer_id === auth()->id() && $record->status !== 'completed'),
        ];
    }
}
