<?php

namespace App\Filament\Admin\Pages\Reports;

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
use Illuminate\Database\Eloquent\Builder;

class AiInvoiceReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'AI Invoice Report';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.admin.pages.reports.ai-invoice-report';

    public ?array $data = [];
    public string $reportMarkdown = '';
    public bool $isEditing = false;
    public ?int $selectedReportId = null;
    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to'   => now()->format('Y-m-d'),
        ]);

        if ($this->selectedReportId) {
            $this->loadReport();
        }
    }

    public function getReportOptions(): array
    {
        return ReportLog::query()
            ->where('report_type', 'ai_invoice_report')
            ->where('generated_by', auth()->id())
            ->where('status', 'completed')
            ->orderByDesc('generated_at')
            ->get()
            ->mapWithKeys(function (ReportLog $report) {
                $filters = $report->filters_used;
                $dateRange = "{$filters['date_from']} to {$filters['date_to']}";
                $status = !empty($filters['status_filter']) ? implode(', ', $filters['status_filter']) : 'All Statuses';
                return [$report->id => "{$report->generated_at->format('d M Y H:i')} ({$dateRange}, {$status})"];
            })
            ->toArray();
    }

    public function isGeneratingNewReport(): bool
    {
        return $this->selectedReportId === null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedReportId')
                    ->label('Load Existing Report')
                    ->options($this->getReportOptions())
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->selectedReportId = (int) $state;
                        $this->loadReport();
                    })
                    ->placeholder('Select a past report to view')
                    ->searchable()
                    ->columnSpanFull(),
                Select::make('invoice_id')
                    ->label('Specific Invoice')
                    ->options(Invoice::query()
                        ->with('client:id,company_name')
                        ->latest('issued_at')
                        ->get()
                        ->mapWithKeys(fn (Invoice $record) => [
                            $record->id => "{$record->invoice_number} — {$record->client?->company_name} ($" . number_format($record->total, 2) . ")"
                        ])
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Search by invoice number...')
                    ->hidden(fn () => !$this->isGeneratingNewReport())
                    ->live(),
                DatePicker::make('date_from')
                    ->label('From')
                    ->native(false)
                    ->required(fn ($get) => empty($get('invoice_id')))
                    ->hidden(fn ($get) => !$this->isGeneratingNewReport() || !empty($get('invoice_id'))),
                DatePicker::make('date_to')
                    ->label('To')
                    ->native(false)
                    ->required(fn ($get) => empty($get('invoice_id')))
                    ->hidden(fn ($get) => !$this->isGeneratingNewReport() || !empty($get('invoice_id'))),
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
                    ->multiple()
                    ->hidden(fn ($get) => !$this->isGeneratingNewReport() || !empty($get('invoice_id'))),
                Textarea::make('custom_instructions')
                    ->label('Custom Instructions')
                    ->placeholder('e.g. "Focus on overdue invoices", "Add currency breakdown", "Keep summary brief"')
                    ->rows(2)
                    ->columnSpanFull()
                    ->hidden(fn () => !$this->isGeneratingNewReport() && $this->reportMarkdown), // Only show if generating new or revising existing
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $query = Invoice::query()
            ->with(['client:id,company_name', 'workOrder:id,reference_number']);

        if (!empty($data['invoice_id'])) {
            $query->where('id', $data['invoice_id']);
        } else {
            $query->whereBetween('issued_at', [$data['date_from'], $data['date_to'] . ' 23:59:59']);

            if (!empty($data['status_filter'])) {
                $query->whereIn('status', $data['status_filter']);
            }
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            Notification::make()->title('No invoices found for the selected criteria.')->warning()->send();
            return;
        }

        $service = new AiReportService();
        $generatedMarkdown = $service->generateInvoiceReport($invoices, $data['custom_instructions'] ?? '');
        $this->reportMarkdown = $generatedMarkdown;
        $this->isEditing = false;

        ReportLog::create([
            'report_type'    => 'ai_invoice_report',
            'generated_by'   => auth()->id(),
            'filters_used'   => [
                'invoice_id'    => $data['invoice_id'] ?? null,
                'date_from'     => $data['date_from'] ?? null,
                'date_to'       => $data['date_to'] ?? null,
                'status_filter' => $data['status_filter'] ?? [],
                'invoice_count' => $invoices->count(),
            ],
            'status'         => str_starts_with($generatedMarkdown, '**Error') ? 'failed' : 'completed',
            'generated_at'   => now(),
            'report_content' => $generatedMarkdown,
        ]);

        Notification::make()->title('Invoice report generated.')->success()->send();
    }

    public function loadReport(): void
    {
        if (!$this->selectedReportId) {
            $this->clearReport();
            return;
        }

        $report = ReportLog::find($this->selectedReportId);

        if ($report && $report->report_content) {
            $this->reportMarkdown = $report->report_content;
            $this->form->fill([
                'date_from' => $report->filters_used['date_from'] ?? null,
                'date_to'   => $report->filters_used['date_to'] ?? null,
                'status_filter' => $report->filters_used['status_filter'] ?? [],
                'custom_instructions' => null, // Clear instructions for loaded reports
            ]);
            Notification::make()->title('Report loaded successfully.')->success()->send();
        } else {
            $this->clearReport();
            Notification::make()->title('Selected report not found or content is empty.')->danger()->send();
        }
    }

    public function clearReport(): void
    {
        $this->selectedReportId = null;
        $this->reportMarkdown = '';
        $this->isEditing = false;
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to'   => now()->format('Y-m-d'),
            'status_filter' => [],
            'custom_instructions' => '',
        ]);
        Notification::make()->title('Report cleared.')->success()->send();
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
        
        // If it was a loaded report, update its content in the database
        if ($this->selectedReportId) {
            $report = ReportLog::find($this->selectedReportId);
            if ($report) {
                $report->update(['report_content' => $this->reportMarkdown]);
            }
        }

        Notification::make()->title('Report revised.')->success()->send();
    }

    public function toggleEdit(): void
    {
        $this->isEditing = !$this->isEditing;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearReport')
                ->label('Clear Report')
                ->icon('heroicon-o-backspace')
                ->color('secondary')
                ->visible(fn () => !empty($this->reportMarkdown))
                ->action('clearReport'),
            Action::make('generateReport')
                ->label('Generate New Report')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn () => $this->isGeneratingNewReport())
                ->action('generate'),
            Action::make('reviseReport')
                ->label('Revise Report')
                ->icon('heroicon-o-pencil')
                ->color('info')
                ->visible(fn () => !empty($this->reportMarkdown) && $this->isGeneratingNewReport() === false)
                ->action('revise'),
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
