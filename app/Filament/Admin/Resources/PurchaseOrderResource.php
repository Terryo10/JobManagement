<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
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

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Requisitions';
    protected static ?string $breadcrumb = 'Requisitions';
    protected static ?string $pluralLabel = 'Requisitions';
    protected static ?string $modelLabel = 'Requisition';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')
                    ->label('What do you need the money for?')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
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
                Forms\Components\Select::make('status')
                    ->options([
                        'draft'                    => 'Draft',
                        'pending_finance_approval' => 'Pending Finance Approval',
                        'finance_approved'         => 'Finance Approved',
                        'approved'                 => 'Approved',
                        'rejected'                 => 'Rejected',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Select::make('work_order_id')
                    ->relationship('workOrder', 'reference_number')
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
                        'pending_finance_approval' => 'Awaiting Finance',
                        'finance_approved'         => 'Pending Your Approval',
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
                    'pending_finance_approval' => 'Awaiting Finance',
                    'finance_approved'         => 'Pending My Approval',
                    'approved'                 => 'Approved',
                    'rejected'                 => 'Rejected',
                ]),
            ])
            ->actions([
                // Direct approve/reject buttons – visible at the right stage
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'finance_approved')
                    ->action(function ($record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Requisition fully approved!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['pending_finance_approval', 'finance_approved']))
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Requisition rejected.')->warning()->send();
                    }),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('reference_number')->label('Reference'),
                Infolists\Components\TextEntry::make('orderedBy.name')->label('Requested By'),
                Infolists\Components\TextEntry::make('total_amount')->label('Amount')->money('usd'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Work Order')->placeholder('—'),
                Infolists\Components\TextEntry::make('financeApprovedBy.name')->label('Finance Approved By')->placeholder('—'),
                Infolists\Components\TextEntry::make('approvedBy.name')->label('Final Approved By')->placeholder('—'),
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
        return [
            \App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers\DocumentsRelationManager::class,
        ];
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
