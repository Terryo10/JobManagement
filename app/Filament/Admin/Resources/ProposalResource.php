<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProposalResource\Pages;
use App\Models\Proposal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Proposals';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->disabled(),
                Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->disabled(),
                Forms\Components\Select::make('lead_id')->relationship('lead', 'contact_name')->disabled(),
                Forms\Components\Select::make('type')->options([
                    'pitch' => 'Pitch Deck', 'proposal' => 'Full Proposal', 'quotation' => 'Quotation',
                ])->disabled(),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved',
                    'accepted' => 'Accepted', 'rejected' => 'Rejected',
                ])->disabled(),
                Forms\Components\TextInput::make('value')->numeric()->prefix('$')->disabled(),
                Forms\Components\TextInput::make('currency')->disabled(),
                Forms\Components\DatePicker::make('submitted_at')->disabled(),
                Forms\Components\DatePicker::make('valid_until')->disabled(),
                Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull()->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client')->searchable(),
            Tables\Columns\TextColumn::make('preparedBy.name')->label('Prepared By'),
            Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'submitted' => 'info', 'approved' => 'success',
                'accepted' => 'success', 'rejected' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('value')->money(fn ($record) => $record->currency)->sortable(),
            Tables\Columns\TextColumn::make('submitted_at')->date()->sortable(),
            Tables\Columns\TextColumn::make('valid_until')->date()->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved',
                'accepted' => 'Accepted', 'rejected' => 'Rejected',
            ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Proposal')
                ->modalDescription('Are you sure you want to approve this proposal?')
                ->visible(fn (Proposal $record) => in_array($record->status, ['submitted']))
                ->action(fn (Proposal $record) => $record->update(['status' => 'approved'])),
            Tables\Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Proposal')
                ->modalDescription('Are you sure you want to reject this proposal?')
                ->visible(fn (Proposal $record) => in_array($record->status, ['submitted']))
                ->action(fn (Proposal $record) => $record->update(['status' => 'rejected'])),
        ])
        ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Proposal Details')->schema([
                Infolists\Components\TextEntry::make('title'),
                Infolists\Components\TextEntry::make('client.company_name')->label('Client'),
                Infolists\Components\TextEntry::make('lead.contact_name')->label('Lead'),
                Infolists\Components\TextEntry::make('preparedBy.name')->label('Prepared By'),
                Infolists\Components\TextEntry::make('type')->badge(),
                Infolists\Components\TextEntry::make('status')->badge()->color(fn ($state) => match ($state) {
                    'draft' => 'gray', 'submitted' => 'info', 'approved' => 'success',
                    'accepted' => 'success', 'rejected' => 'danger', default => 'gray',
                }),
                Infolists\Components\TextEntry::make('value')->money(fn ($record) => $record->currency),
                Infolists\Components\TextEntry::make('submitted_at')->date(),
                Infolists\Components\TextEntry::make('valid_until')->date(),
            ])->columns(3),
            Infolists\Components\Section::make('Content')->schema([
                Infolists\Components\TextEntry::make('content')->html(),
                Infolists\Components\TextEntry::make('notes'),
            ]),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProposals::route('/'),
            'view'  => Pages\ViewProposal::route('/{record}'),
        ];
    }
}
