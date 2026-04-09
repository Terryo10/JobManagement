<?php

namespace App\Filament\Staff\Resources\AnnouncementResource\Pages;

use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Staff\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnnouncement extends ViewRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->getRecord()->created_by === auth()->id()),
            RequestDeletionAction::make()
                ->visible(fn () => $this->getRecord()->created_by === auth()->id()),
        ];
    }
}
