<?php

namespace App\Filament\Accountant\Pages\Reports;

use App\Models\Invoice;
use App\Models\ReportLog;
use App\Services\AiReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiInvoiceReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'AI Invoice Report';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.accountant.pages.reports.ai-invoice-report';

    public ?array $data = [];
    public string $reportMarkdown = '';
    public bool $isEditing = false;

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to'   => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')->label('From')->native(false)->required(),
                DatePicker::make('date_to')->label('To')->native(false)->required(),
                Select::make('status_filter')
                    ->label('Status (leave blank for all)')
                    ->options([
                        'draft'     => 'Draft',
                        'sent'      => 'Sent',
                        'signed'    => 'Signed',
                        'paid'      => 'Paid',
                        'overdue'   => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ])
                    ->placeholder('All statuses')
                    ->multiple(),
                Textarea::make('custom_instructions')
                    ->label('Custom Instructions')
                    ->placeholder('e.g. "Focus on overdue invoices", "Add currency breakdown", "Keep summary brief"')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $query = Invoice::query()
            ->with(['client:id,company_name', 'workOrder:id,reference_number'])
            ->whereBetween('issued_at', [$data['date_from'], $data['date_to'] . ' 23:59:59']);

        if (!empty($data['status_filter'])) {
            $query->whereIn('status', $data['status_filter']);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            Notification::make()->title('No invoices found for the selected period.')->warning()->send();
            return;
        }

        $service = new AiReportService();
        $this->reportMarkdown = $service->generateInvoiceReport($invoices, $data['custom_instructions'] ?? '');
        $this->isEditing = false;

        ReportLog::create([
            'report_type' => 'ai_invoice_report',
            'generated_by' => auth()->id(),
            'filters_used' => [
                'date_from'     => $data['date_from'],
                'date_to'       => $data['date_to'],
                'invoice_count' => $invoices->count(),
            ],
            'status'       => str_starts_with($this->reportMarkdown, '**Error') ? 'failed' : 'completed',
            'generated_at' => now(),
        ]);

        Notification::make()->title('Invoice report generated.')->success()->send();
    }

    public function revise(): void
    {
        $data = $this->form->getState();
        $instructions = $data['custom_instructions'] ?? '';

        if (empty($instructions)) {
            Notification::make()->title('Enter revision instructions in the Custom Instructions field.')->warning()->send();
            return;
        }

        if (empty($this->reportMarkdown)) {
            Notification::make()->title('Generate a report first before requesting revisions.')->warning()->send();
            return;
        }

        $this->reportMarkdown = (new AiReportService())->reviseReport($this->reportMarkdown, $instructions);
        Notification::make()->title('Report revised.')->success()->send();
    }

    public function toggleEdit(): void
    {
        $this->isEditing = !$this->isEditing;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => !empty($this->reportMarkdown))
                ->action(function () {
                    if (empty($this->reportMarkdown)) {
                        return;
                    }
                    $pdf = Pdf::loadView('reports.ai-report-pdf', [
                        'markdown'    => $this->reportMarkdown,
                        'generatedAt' => now()->format('d M Y H:i'),
                    ])->setPaper('a4');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'invoice-report-' . now()->format('Y-m-d-His') . '.pdf'
                    );
                }),
        ];
    }
}
