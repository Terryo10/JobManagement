<?php
namespace App\Filament\Marketing\Resources\NetworkingEventResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Marketing\Resources\NetworkingEventResource;
use Filament\Resources\Pages\EditRecord;
class EditNetworkingEvent extends EditRecord
{
    protected static string $resource = NetworkingEventResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
