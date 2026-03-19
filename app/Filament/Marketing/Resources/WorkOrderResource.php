<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Work Orders';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(40)->searchable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client'),
            Tables\Columns\TextColumn::make('category')->badge()
                ->color(fn ($state) => match ($state) {
                    'media' => 'primary', 'civil_works' => 'warning',
                    'energy' => 'success', 'warehouse' => 'info', default => 'gray',
                }),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Tables\Filters\SelectFilter::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse']),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function ($record) {
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
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('General Information')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('reference_number')->label('Reference #'),
                    Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('client.company_name')->label('Client'),
                    Infolists\Components\TextEntry::make('assignedDepartment.name')->label('Department'),
                    Infolists\Components\TextEntry::make('details.lead_person')->label('Lead Person')->default('—'),
                    Infolists\Components\TextEntry::make('category')->badge(),
                    Infolists\Components\TextEntry::make('status')->badge()->color(fn ($state) => match ($state) {
                        'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                        'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
                    Infolists\Components\TextEntry::make('priority')->badge()->color(fn ($state) => match ($state) {
                        'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
                    }),
                ]),

            Infolists\Components\Section::make('Design Job Card')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('description')->label('Project Description')->columnSpanFull()->html(),
                    Infolists\Components\TextEntry::make('details.date_order_received')->label('Date Order Received')->date()->placeholder('—'),
                    Infolists\Components\TextEntry::make('deadline')->label('Deadline')->date()->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Financial Overview')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('budget')->label('Budget')->money('usd'),
                    Infolists\Components\TextEntry::make('actual_cost')->label('Actual Cost')->money('usd'),
                    Infolists\Components\TextEntry::make('start_date')->label('Start Date')->date(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'view'  => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }
}
