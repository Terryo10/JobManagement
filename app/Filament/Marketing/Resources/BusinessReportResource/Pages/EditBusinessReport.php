<?php
namespace App\Filament\Marketing\Resources\BusinessReportResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Marketing\Resources\BusinessReportResource;
use Filament\Resources\Pages\EditRecord;
class EditBusinessReport extends EditRecord
{
    protected static string $resource = BusinessReportResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
