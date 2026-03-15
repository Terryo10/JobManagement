<?php

namespace App\Filament\Admin\Resources\WorkOrderResource\Pages;

use App\Filament\Admin\Resources\WorkOrderResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkOrder extends ViewRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $record = $this->getRecord();
                    $record->load(['client', 'assignedDepartment', 'claimedBy', 'createdBy', 'lead']);

                    $pdf = Pdf::loadView('pdf.job-card', [
                        'workOrder'   => $record,
                        'generatedAt' => now()->format('d M Y H:i'),
                    ])->setPaper('a4');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'job-card-' . $record->reference_number . '.pdf'
                    );
                }),
            Actions\EditAction::make(),
        ];
    }
}
