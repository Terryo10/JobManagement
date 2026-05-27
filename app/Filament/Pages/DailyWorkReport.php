<?php

namespace App\Filament\Pages;

use App\Models\ReportLog;
use App\Models\User;
use App\Services\AiReportService;
use App\Notifications\DatabaseAlert;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DailyWorkReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Daily Report';
    protected static ?string $title = 'Daily Work Report';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.daily-work-report';

    public ?array $data = [];
    public string $reportMarkdown = '';
    public bool $isEditing = false;
    public string $revisionInstructions = '';

    public function mount(): void
    {
        $this->form->fill([
            'report_date' => now()->format('Y-m-d'),
            'custom_instructions' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('report_date')
                    ->label('Report Date')
                    ->maxDate(now())
                    ->default(now())
                    ->native(false)
                    ->required(),
                Textarea::make('custom_instructions')
                    ->label('Custom Instructions / Context')
                    ->placeholder('e.g. "Highlight client meetings", "Casual tone", "Detail blockers"')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $state = $this->form->getState();
        $date = $state['report_date'];
        $instructions = $state['custom_instructions'] ?? '';

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $service = new AiReportService();
        $this->reportMarkdown = $service->generateDailyWorkReport($user, $date, $instructions);
        $this->isEditing = false;

        Notification::make()
            ->title('Daily Report draft compiled successfully!')
            ->success()
            ->send();
    }

    public function revise(): void
    {
        if (empty($this->revisionInstructions)) {
            Notification::make()
                ->title('Please enter feedback or revision instructions.')
                ->warning()
                ->send();
            return;
        }

        if (empty($this->reportMarkdown)) {
            Notification::make()
                ->title('Generate a report first before requesting revisions.')
                ->warning()
                ->send();
            return;
        }

        $service = new AiReportService();
        $this->reportMarkdown = $service->reviseReport($this->reportMarkdown, $this->revisionInstructions);
        $this->revisionInstructions = '';

        Notification::make()
            ->title('Report refined successfully!')
            ->success()
            ->send();
    }

    public function toggleEdit(): void
    {
        $this->isEditing = !$this->isEditing;
    }

    public function saveAndSubmit(): void
    {
        if (empty($this->reportMarkdown)) {
            Notification::make()
                ->title('Generate a report first before saving.')
                ->warning()
                ->send();
            return;
        }

        $state = $this->form->getState();
        $date = $state['report_date'];

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Save ReportLog
        ReportLog::create([
            'report_type' => 'daily_work_report',
            'generated_by' => $user->id,
            'filters_used' => [
                'report_date' => $date,
            ],
            'status' => 'completed',
            'generated_at' => now(),
            'report_content' => $this->reportMarkdown,
        ]);

        // Send alerts to managers
        $managers = User::role(['manager', 'super_admin'])->get();
        $notifiables = collect($managers);

        // Include department head if they are not the user themselves
        if ($user->department && $user->department->head && $user->department->head_user_id !== $user->id) {
            $notifiables->push($user->department->head);
        }

        $notifiables = $notifiables->unique('id');

        foreach ($notifiables as $manager) {
            $manager->notify(new DatabaseAlert(
                title: 'Daily Report Submitted',
                body: "{$user->name} has submitted their Daily Report for {$date}.",
                icon: 'heroicon-o-document-text',
                color: 'success',
                actionUrl: '/admin/report-logs',
                actionText: 'View Report Logs'
            ));
        }

        Notification::make()
            ->title('Daily Report submitted successfully!')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copyReport')
                ->label('Copy to Clipboard')
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->visible(fn () => !empty($this->reportMarkdown))
                ->extraAttributes([
                    'x-on:click' => 'window.navigator.clipboard.writeText($wire.reportMarkdown); $tooltip("Copied to clipboard!")',
                ])
                ->action(fn () => null),
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => !empty($this->reportMarkdown))
                ->action(function () {
                    if (empty($this->reportMarkdown)) {
                        return;
                    }

                    $pdf = Pdf::loadView('reports.daily-report-pdf', [
                        'markdown' => $this->reportMarkdown,
                        'generatedAt' => now()->format('d M Y H:i'),
                        'user' => auth()->user(),
                        'date' => $this->form->getState()['report_date'],
                    ])->setPaper('a4');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'daily-report-' . now()->format('Y-m-d-His') . '.pdf'
                    );
                }),
            Action::make('submitReport')
                ->label('Save & Submit Report')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => !empty($this->reportMarkdown))
                ->requiresConfirmation()
                ->action(function () {
                    $this->saveAndSubmit();
                }),
        ];
    }
}
