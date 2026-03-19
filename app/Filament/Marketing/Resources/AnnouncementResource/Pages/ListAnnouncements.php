<?php

namespace App\Filament\Marketing\Resources\AnnouncementResource\Pages;

use App\Filament\Marketing\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Post Announcement')->icon('heroicon-m-megaphone'),
        ];
    }
}
