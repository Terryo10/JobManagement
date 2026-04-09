<?php

namespace App\Filament\Accountant\Resources\AnnouncementResource\Pages;

use App\Filament\Accountant\Resources\AnnouncementResource;
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
            \App\Filament\Shared\Actions\RequestDeletionAction::make()
                ->visible(fn () => $this->getRecord()->created_by === auth()->id())
                ->successRedirectUrl(AnnouncementResource::getUrl('index')),
        ];
    }
}
