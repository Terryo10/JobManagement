<?php

namespace App\Filament\Shared\Pages;

use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseJobCardReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Job Card Report';

    protected static ?string $title = 'Job Card Report';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.job-card-report';

    abstract protected static function workOrderResourceClass(): string;

    protected function reportQuery(array $filters = []): Builder
    {
        return WorkOrder::query()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'completed')])
            ->with(['client:id,company_name', 'assignedDepartment:id,name'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Generate PDF Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('status')->options([
                        'pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold',
                        'completed' => 'Completed', 'cancelled' => 'Cancelled',
                    ])->placeholder('All statuses'),
                    Forms\Components\Select::make('category')->options([
                        'media' => 'Media', 'civil_works' => 'Civil Works',
                        'energy' => 'Energy', 'warehouse' => 'Warehouse',
                    ])->placeholder('All categories'),
                    Forms\Components\DatePicker::make('date_from')->label('Created From'),
                    Forms\Components\DatePicker::make('date_to')->label('Created To')->afterOrEqual('date_from'),
                ])
                ->action(function (array $data) {
                    $records = $this->reportQuery($data)->get();
                    $pdf = Pdf::loadView('reports.job-summary-pdf', [
                        'records' => $records,
                        'generatedAt' => now()->format('d M Y H:i'),
                        'filterStatus' => $data['status'] ?? null,
                        'filterCategory' => $data['category'] ?? null,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        'job-card-report-'.now()->format('Y-m-d').'.pdf'
                    );
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->reportQuery())
            ->columns([
                TextColumn::make('reference_number')->label('Job Card #')->searchable()->sortable(),
                TextColumn::make('title')->limit(30)->searchable(),
                TextColumn::make('client.company_name')->label('Client'),
                TextColumn::make('assignedDepartment.name')->label('Department')->placeholder('—'),
                TextColumn::make('category')->badge()->color(fn ($state) => match ($state) {
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
                TextColumn::make('deadline')->date()->placeholder('—')->sortable(),
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
                Tables\Filters\Filter::make('created_at')->form([
                    Forms\Components\DatePicker::make('from'),
                    Forms\Components\DatePicker::make('until'),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                    ->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(function (WorkOrder $record) {
                        $resource = static::workOrderResourceClass();

                        return $resource::getUrl('view', ['record' => $record]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
