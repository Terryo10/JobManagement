<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\BusinessReportResource\Pages;
use App\Models\BusinessReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class BusinessReportResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = BusinessReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Reports';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('type')->options([
                    'client_report' => 'Client Report (e.g., Zupco, Ecocash)',
                    'internal_report' => 'Internal Report',
                    'proposal_analysis' => 'Proposal Analysis',
                    'growth_strategy' => 'Growth Strategy',
                ])->default('client_report')->required(),
                Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload()
                    ->hint('Leave blank for internal reports'),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft', 'final' => 'Final', 'submitted' => 'Submitted',
                ])->default('draft')->required(),
                Forms\Components\DatePicker::make('period_from'),
                Forms\Components\DatePicker::make('period_to'),
                Forms\Components\RichEditor::make('content')->columnSpanFull()->required(),
                Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'final' => 'success', 'submitted' => 'primary', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('period_from')->date()->sortable(),
            Tables\Columns\TextColumn::make('period_to')->date()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('type')->options([
                'client_report' => 'Client Report', 'internal_report' => 'Internal Report',
                'proposal_analysis' => 'Proposal Analysis', 'growth_strategy' => 'Growth Strategy',
            ]),
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'final' => 'Final', 'submitted' => 'Submitted',
            ]),
        ])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Marketing\Resources\BusinessReportResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBusinessReports::route('/'),
            'create' => Pages\CreateBusinessReport::route('/create'),
            'view'   => Pages\ViewBusinessReport::route('/{record}'),
            'edit'   => Pages\EditBusinessReport::route('/{record}/edit'),
        ];
    }
}
