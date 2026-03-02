<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Job Cards';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([
            \Filament\Forms\Components\Tabs::make('Job Card')->tabs([
                \Filament\Forms\Components\Tabs\Tab::make('Details')->icon('heroicon-o-information-circle')->schema([
                    \Filament\Forms\Components\TextInput::make('reference_number')->required()->maxLength(50)->unique(ignoreRecord: true),
                    \Filament\Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    \Filament\Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload()->required()
                        ->reactive()
                        ->afterStateUpdated(fn (\Filament\Forms\Set $set) => $set('lead_id', null)),
                    \Filament\Forms\Components\Select::make('category')
                        ->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse'])->required(),
                    \Filament\Forms\Components\Select::make('status')
                        ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                        ->default('pending')->required(),
                    \Filament\Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                        ->default('normal')->required(),
                    \Filament\Forms\Components\Select::make('assigned_department_id')
                        ->relationship('assignedDepartment', 'name')->searchable()->preload()->label('Department'),
                    \Filament\Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
                ])->columns(2),
                \Filament\Forms\Components\Tabs\Tab::make('Financials')->icon('heroicon-o-currency-dollar')->schema([
                    \Filament\Forms\Components\TextInput::make('budget')->numeric()->prefix('$'),
                    \Filament\Forms\Components\TextInput::make('actual_cost')->numeric()->prefix('$'),
                ])->columns(2),
                \Filament\Forms\Components\Tabs\Tab::make('Timeline')->icon('heroicon-o-clock')->schema([
                    \Filament\Forms\Components\DatePicker::make('start_date'),
                    \Filament\Forms\Components\DatePicker::make('deadline'),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(40)->searchable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client'),
            Tables\Columns\TextColumn::make('budget')->money('usd')->sortable(),
            Tables\Columns\TextColumn::make('category')->badge()
                ->color(fn ($state) => match ($state) {
                    'media' => 'primary', 'civil_works' => 'warning',
                    'energy' => 'success', 'warehouse' => 'info', default => 'gray',
                }),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Tables\Filters\SelectFilter::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse']),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Job Details')->schema([
                Infolists\Components\TextEntry::make('reference_number'),
                Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                Infolists\Components\TextEntry::make('client.company_name'),
                Infolists\Components\TextEntry::make('category')->badge(),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('priority')->badge(),
                Infolists\Components\TextEntry::make('budget')->money('usd'),
                Infolists\Components\TextEntry::make('actual_cost')->money('usd'),
                Infolists\Components\TextEntry::make('start_date')->date(),
                Infolists\Components\TextEntry::make('deadline')->date(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit'   => Pages\EditWorkOrder::route('/{record}/edit'),
            'view'   => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }
}
