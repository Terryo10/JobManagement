<?php

namespace App\Filament\Marketing\Pages\Reports;

use App\Models\ReportLog;
use App\Models\WorkOrder;
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

class AiReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'AI Report Generator';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.marketing.pages.reports.ai-report';

    public ?array $data = [];

    public string $reportMarkdown = '';
    public bool $isEditing = false;

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')
                    ->label('From')
                    ->native(false)
                    ->required(),
                DatePicker::make('date_to')
                    ->label('To')
                    ->native(false)
                    ->required(),
                Select::make('work_order_ids')
                    ->label('Work Orders (leave blank for all in range)')
                    ->multiple()
                    ->options(fn () => WorkOrder::orderBy('reference_number', 'desc')
                        ->get(['id', 'reference_number', 'title'])
                        ->mapWithKeys(fn ($wo) => [$wo->id => "{$wo->reference_number} – {$wo->title}"]))
                    ->searchable()
                    ->preload(false)
                    ->placeholder('All work orders in date range'),
                Textarea::make('custom_instructions')
                    ->label('Custom Instructions')
                    ->placeholder('e.g. "Focus on budget overruns", "Add a risk section", "Keep it brief"')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $query = WorkOrder::query()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed')])
            ->with(['client:id,company_name', 'assignedDepartment:id,name'])
            ->whereBetween('created_at', [$data['date_from'], $data['date_to'] . ' 23:59:59']);

        if (!empty($data['work_order_ids'])) {
            $query->whereIn('id', $data['work_order_ids']);
        }

        $workOrders = $query->get();

        if ($workOrders->isEmpty()) {
            Notification::make()->title('No work orders found for the selected period.')->warning()->send();
            return;
        }

        $service = new AiReportService();
        $this->reportMarkdown = $service->generateReport($workOrders, $data['custom_instructions'] ?? '');
        $this->isEditing = false;

        ReportLog::create([
            'report_type' => 'ai_generated',
            'generated_by' => auth()->id(),
            'filters_used' => [
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'work_order_count' => $workOrders->count(),
            ],
            'status' => str_starts_with($this->reportMarkdown, '**Error') ? 'failed' : 'completed',
            'generated_at' => now(),
        ]);

        Notification::make()->title('Report generated successfully.')->success()->send();
    }

    public function revise(): void
    {
        $data = $this->form->getState();
        $instructions = $data['custom_instructions'] ?? '';

        if (empty($instructions)) {
            Notification::make()->title('Please enter revision instructions in the Custom Instructions field.')->warning()->send();
            return;
        }

        if (empty($this->reportMarkdown)) {
            Notification::make()->title('Generate a report first before requesting revisions.')->warning()->send();
            return;
        }

        $service = new AiReportService();
        $this->reportMarkdown = $service->reviseReport($this->reportMarkdown, $instructions);

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
                        'markdown' => $this->reportMarkdown,
                        'generatedAt' => now()->format('d M Y H:i'),
                    ])->setPaper('a4');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'ai-report-' . now()->format('Y-m-d-His') . '.pdf'
                    );
                }),
        ];
    }
}
