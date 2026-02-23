<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'My Jobs';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        // Filter to the client linked to the logged-in user by email
        return parent::getEloquentQuery()
            ->whereHas('client', fn ($q) => $q->where('email', auth()->user()?->email));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(50),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info', 'completed' => 'success', 'cancelled' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('start_date')->date(),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
        ])
        ->actions([Tables\Actions\ViewAction::make()])
        ->paginated([10, 25]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'view'  => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }
}
