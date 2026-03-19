<?php

namespace App\Filament\Marketing\Resources\AnnouncementResource\Pages;

use App\Filament\Marketing\Resources\AnnouncementResource;
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
            Actions\DeleteAction::make()
                ->visible(fn () => $this->getRecord()->created_by === auth()->id())
                ->successRedirectUrl(AnnouncementResource::getUrl('index')),
        ];
    }
}
