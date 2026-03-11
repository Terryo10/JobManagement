<?php

namespace App\Filament\Admin\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'leads';
    protected static ?string $title = 'Leads';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('contact_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('contact_email')->email(),
            Forms\Components\TextInput::make('contact_phone')->tel(),
            Forms\Components\TextInput::make('company_name')->maxLength(255),
            Forms\Components\Select::make('source')
                ->options(['website' => 'Website', 'referral' => 'Referral', 'cold_call' => 'Cold Call', 'social_media' => 'Social Media', 'other' => 'Other']),
            Forms\Components\Select::make('status')
                ->options(['new' => 'New', 'in_progress' => 'In Progress', 'converted' => 'Converted', 'lost' => 'Lost'])
                ->default('new')->required(),
            Forms\Components\Select::make('assigned_to')
                ->relationship('assignedTo', 'name')->searchable()->preload(),
            Forms\Components\DatePicker::make('follow_up_date'),
            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contact_name')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new' => 'info', 'in_progress' => 'warning', 'converted' => 'success', 'lost' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned'),
                Tables\Columns\TextColumn::make('follow_up_date')->date(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                $data['created_by'] = auth()->id();
                return $data;
            })])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
