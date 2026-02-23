<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkOrderNoteResource\Pages;
use App\Models\WorkOrderNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderNoteResource extends Resource
{
    protected static ?string $model = WorkOrderNote::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\Toggle::make('is_internal')->label('Internal (Staff Only)')->default(true),
            Forms\Components\Textarea::make('body')->required()->rows(5)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Work Order')->sortable(),
            Tables\Columns\TextColumn::make('user.name')->sortable(),
            Tables\Columns\IconColumn::make('is_internal')->boolean()->label('Internal'),
            Tables\Columns\TextColumn::make('body')->limit(60),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('is_internal')])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrderNotes::route('/'),
            'create' => Pages\CreateWorkOrderNote::route('/create'),
            'edit'   => Pages\EditWorkOrderNote::route('/{record}/edit'),
        ];
    }
}
