<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'My Work';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_department_id', auth()->user()?->department_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reference_number')->disabled(),
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\TextInput::make('deadline')->disabled(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(40),
            Tables\Columns\TextColumn::make('client.company_name'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info', 'completed' => 'success', 'cancelled' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('priority')->badge(),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed'])])
        ->actions([Tables\Actions\ViewAction::make()]);
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
        ];
    }
}
