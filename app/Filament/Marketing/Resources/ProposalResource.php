<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\ProposalResource\Pages;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Pipeline';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload(),
                Forms\Components\Select::make('lead_id')->relationship('lead', 'contact_name')->searchable()->preload(),
                Forms\Components\Select::make('type')->options([
                    'pitch' => 'Pitch Deck', 'proposal' => 'Full Proposal', 'quotation' => 'Quotation',
                ])->default('proposal')->required(),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'accepted' => 'Accepted', 'rejected' => 'Rejected',
                ])->default('draft')->required(),
                Forms\Components\TextInput::make('value')->numeric()->prefix('$')->maxValue(999999999.99),
                Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                Forms\Components\DatePicker::make('submitted_at'),
                Forms\Components\DatePicker::make('valid_until'),
                Forms\Components\RichEditor::make('content')->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client')->searchable(),
            Tables\Columns\TextColumn::make('lead.contact_name')->label('Lead')->searchable(),
            Tables\Columns\TextColumn::make('type')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'submitted' => 'info', 'approved' => 'success', 'accepted' => 'success', 'rejected' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('value')->money(fn ($record) => $record->currency)->sortable(),
            Tables\Columns\TextColumn::make('submitted_at')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'accepted' => 'Accepted', 'rejected' => 'Rejected',
            ]),
            Tables\Filters\SelectFilter::make('type')->options([
                'pitch' => 'Pitch Deck', 'proposal' => 'Full Proposal', 'quotation' => 'Quotation',
            ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->iconButton()
                ->tooltip('Download Proposal PDF')
                ->action(function ($record) {
                    $record->load('client', 'lead', 'preparedBy');
                    $pdf = Pdf::loadView('pdf.proposal', ['proposal' => $record]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        "proposal-{$record->id}.pdf"
                    );
                }),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Marketing\Resources\ProposalResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProposals::route('/'),
            'create' => Pages\CreateProposal::route('/create'),
            'edit'   => Pages\EditProposal::route('/{record}/edit'),
        ];
    }
}
