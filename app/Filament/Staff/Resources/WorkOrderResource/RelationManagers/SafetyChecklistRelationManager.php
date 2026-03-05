<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SafetyChecklistRelationManager extends RelationManager
{
    protected static string $relationship = 'safetyRecords';

    protected static ?string $title = 'Safety Checklist';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('checklist_item')
                ->required()
                ->maxLength(255)
                ->label('Checklist Item')
                ->columnSpanFull()
                ->placeholder('e.g. PPE worn, site secured, equipment inspected'),
            Forms\Components\Toggle::make('is_complete')
                ->label('Completed')
                ->default(false)
                ->live()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    if ($state) {
                        $set('completed_at', now()->toDateTimeString());
                        $set('completed_by', auth()->id());
                    } else {
                        $set('completed_at', null);
                    }
                }),
            Forms\Components\Hidden::make('completed_by')
                ->default(fn () => auth()->id()),
            Forms\Components\DateTimePicker::make('completed_at')
                ->label('Completed At')
                ->visible(fn (Forms\Get $get) => $get('is_complete')),
            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_complete')
                    ->label('Done')
                    ->boolean(),
                Tables\Columns\TextColumn::make('checklist_item')
                    ->label('Item')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('completedBy.name')
                    ->label('Signed Off By')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_complete')
                    ->label('Status')
                    ->trueLabel('Completed')
                    ->falseLabel('Pending'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Checklist Item'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle')
                    ->label(fn ($record) => $record->is_complete ? 'Mark Pending' : 'Mark Done')
                    ->icon(fn ($record) => $record->is_complete ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_complete ? 'gray' : 'success')
                    ->action(function ($record) {
                        $record->update([
                            'is_complete'  => ! $record->is_complete,
                            'completed_by' => ! $record->is_complete ? auth()->id() : null,
                            'completed_at' => ! $record->is_complete ? now() : null,
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('No checklist items yet')
            ->emptyStateDescription('Add safety items to sign off before closing this job.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }
}
