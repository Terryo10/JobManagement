<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\FinancialApprovalResource\Pages;
use App\Models\FinancialApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialApprovalResource extends Resource
{
    protected static ?string $model = FinancialApproval::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('approvable_type')->required(),
            Forms\Components\TextInput::make('approvable_id')->numeric()->required(),
            Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])->default('pending')->required(),
            Forms\Components\Select::make('requested_by')->relationship('requestedBy', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('reviewed_by')->relationship('reviewedBy', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('reviewed_at'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('approvable_type')->label('Type'),
            Tables\Columns\TextColumn::make('approvable_id')->label('ID'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('requestedBy.name')->label('Requested By'),
            Tables\Columns\TextColumn::make('reviewedBy.name')->label('Reviewed By'),
            Tables\Columns\TextColumn::make('reviewed_at')->dateTime()->sortable(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])])
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
            'index'  => Pages\ListFinancialApprovals::route('/'),
            'create' => Pages\CreateFinancialApproval::route('/create'),
            'edit'   => Pages\EditFinancialApproval::route('/{record}/edit'),
        ];
    }
}
