<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\PurchaseOrderResource\Pages;
use App\Filament\Forms\Components\SignaturePad;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class PurchaseOrderResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Requisitions';
    protected static ?string $breadcrumb = 'Requisitions';
    protected static ?string $pluralLabel = 'Requisitions';
    protected static ?string $modelLabel = 'Requisition';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')
                    ->label('What do you need the money for?')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('gl_account')
                    ->label('GL Account Code')
                    ->placeholder('e.g. 5010')
                    ->maxLength(50),
                Forms\Components\TextInput::make('gl_account_name')
                    ->label('GL Account Name')
                    ->placeholder('e.g. Marketing Expenses')
                    ->maxLength(150),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Amount Requested')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('reference_number')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => 'REQ-' . now()->format('Y') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT)),
                Forms\Components\Select::make('ordered_by')
                    ->relationship('orderedBy', 'name')
                    ->label('Requested By')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id()),
                Forms\Components\Select::make('work_order_id')
                    ->relationship('workOrder', 'reference_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                    ->searchable()
                    ->preload()
                    ->label('Link to Work Order')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachment')
                    ->label('Attachment (Optional)')
                    ->directory('requisition-attachments')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Additional Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Purpose')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('workOrder.reference_number')
                    ->label('Work Order')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gl_account')
                    ->label('GL Account')
                    ->formatStateUsing(fn ($state, $record) => $state ? "{$state} — {$record->gl_account_name}" : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orderedBy.name')
                    ->label('Requested By'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft'                    => 'gray',
                        'pending_finance_approval' => 'warning',
                        'finance_approved'         => 'info',
                        'approved'                 => 'success',
                        'rejected'                 => 'danger',
                        default                    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'                    => 'Draft',
                        'pending_finance_approval' => 'Pending Your Approval',
                        'finance_approved'         => 'Awaiting Admin',
                        'approved'                 => 'Approved',
                        'rejected'                 => 'Rejected',
                        default                    => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending_finance_approval' => 'Pending My Approval',
                    'finance_approved'         => 'Awaiting Admin',
                    'approved'                 => 'Fully Approved',
                    'rejected'                 => 'Rejected',
                ]),
            ])
            ->actions([
                // Direct approve/reject – no dropdowns
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->modalHeading('Finance Approval')
                    ->modalDescription('Please draw your signature to confirm finance approval. This will be recorded on the requisition PDF.')
                    ->form([
                        SignaturePad::make('finance_signature')
                            ->label('Your Signature')
                            ->default(fn () => auth()->user()->saved_signature)
                            ->required(),
                        Forms\Components\Checkbox::make('save_signature')
                            ->label('Save this signature for future use')
                            ->default(fn () => empty(auth()->user()->saved_signature)),
                    ])
                    ->visible(fn ($record) => $record->status === 'pending_finance_approval')
                    ->action(function ($record, array $data) {
                        if ($data['save_signature'] ?? false) {
                            auth()->user()->update(['saved_signature' => $data['finance_signature']]);
                        }

                        $record->update([
                            'status'                 => 'finance_approved',
                            'finance_approved_by'    => auth()->id(),
                            'finance_signature'      => $data['finance_signature'] ?? null,
                            'finance_signature_date' => now(),
                        ]);
                        Notification::make()
                            ->title('Finance approved — sent to Admin for final sign-off.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Reject Requisition')
                    ->modalDescription('Are you sure you want to reject this requisition? The requester will be notified.')
                    ->visible(fn ($record) => $record->status === 'pending_finance_approval')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Requisition rejected. Requester has been notified.')->warning()->send();
                    }),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Download Requisition PDF')
                    ->action(function ($record) {
                        $record->load('orderedBy', 'approvedBy', 'financeApprovedBy');
                        $pdf = Pdf::loadView('pdf.payment-requisition', ['purchaseOrder' => $record]);
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "requisition-{$record->reference_number}.pdf"
                        );
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('reference_number')->label('Reference'),
                Infolists\Components\TextEntry::make('orderedBy.name')->label('Requested By'),
                Infolists\Components\TextEntry::make('total_amount')->label('Amount')->money('USD'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Work Order')->placeholder('—'),
                Infolists\Components\TextEntry::make('gl_account')
                    ->label('GL Account')
                    ->formatStateUsing(fn ($state, $record) => $state ? "{$state} — {$record->gl_account_name}" : '—')
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('title')->label('Purpose')->columnSpanFull(),
                Infolists\Components\TextEntry::make('attachment')
                    ->label('Attachment')
                    ->formatStateUsing(fn ($state) => $state ? 'View / Download' : '—')
                    ->url(fn ($state) => $state ? Storage::url($state) : null)
                    ->openUrlInNewTab()
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('notes')->label('Notes')->columnSpanFull()->placeholder('—'),
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
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view'   => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
