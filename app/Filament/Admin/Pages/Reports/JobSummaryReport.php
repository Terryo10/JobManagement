<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Filament\Admin\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class JobSummaryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Job Summary';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.admin.pages.reports.job-summary-report';

    public ?string $filterStatus = null;
    public ?string $filterCategory = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $records = WorkOrder::query()
                        ->withCount('tasks')
                        ->withCount(['tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed')])
                        ->with(['client:id,company_name', 'assignedDepartment:id,name'])
                        ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
                        ->when($this->filterCategory, fn ($q) => $q->where('category', $this->filterCategory))
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $pdf = Pdf::loadView('reports.job-summary-pdf', [
                        'records' => $records,
                        'generatedAt' => now()->format('d M Y H:i'),
                        'filterStatus' => $this->filterStatus,
                        'filterCategory' => $this->filterCategory,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'job-summary-' . now()->format('Y-m-d') . '.pdf'
                    );
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WorkOrder::query()
                    ->withCount('tasks')
                    ->withCount(['tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed')])
                    ->with(['client:id,company_name', 'assignedDepartment:id,name'])
                    ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
                    ->when($this->filterCategory, fn ($q) => $q->where('category', $this->filterCategory))
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('reference_number')->label('Ref #')->searchable()->sortable(),
                TextColumn::make('title')->limit(30)->searchable(),
                TextColumn::make('client.company_name')->label('Client'),
                TextColumn::make('assignedDepartment.name')->label('Dept'),
                TextColumn::make('category')->badge()
                    ->color(fn ($state) => match ($state) {
                        'media' => 'primary', 'civil_works' => 'warning',
                        'energy' => 'success', 'warehouse' => 'info', default => 'gray',
                    }),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                    'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                }),
                TextColumn::make('budget')->money('usd'),
                TextColumn::make('actual_cost')->money('usd'),
                TextColumn::make('tasks_count')->label('Tasks'),
                TextColumn::make('completed_tasks_count')->label('Done'),
                TextColumn::make('deadline')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold',
                    'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('category')->options([
                    'media' => 'Media', 'civil_works' => 'Civil Works',
                    'energy' => 'Energy', 'warehouse' => 'Warehouse',
                ]),
                Tables\Filters\SelectFilter::make('priority')->options([
                    'low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (WorkOrder $record) => WorkOrderResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
