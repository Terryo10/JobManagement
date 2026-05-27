<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PrazSubmissionResource\Pages;
use App\Filament\Admin\Resources\PrazSubmissionResource\RelationManagers;
use App\Models\PrazSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrazSubmissionResource extends Resource
{
    protected static ?string $model = PrazSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'PRAZ Submissions';
    protected static ?string $modelLabel = 'PRAZ Submission';
    protected static ?string $pluralModelLabel = 'PRAZ Submissions';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submission Details')
                ->description('Tender and procurement information')
                ->icon('heroicon-o-building-library')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder('e.g. Billboard Tender – Harare CBD'),
                    Forms\Components\TextInput::make('tender_number')
                        ->label('Tender / Bid Number')
                        ->maxLength(100)
                        ->placeholder('e.g. PRAZ/2026/GOV/001'),
                    Forms\Components\Select::make('category')
                        ->options([
                            'goods'       => 'Goods',
                            'services'    => 'Services',
                            'works'       => 'Works',
                            'consultancy' => 'Consultancy',
                        ])
                        ->default('services')
                        ->required(),
                    Forms\Components\TextInput::make('procuring_entity')
                        ->label('Procuring Entity')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. Ministry of Information, ZINARA, Ecocash'),
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'company_name')
                        ->searchable()
                        ->preload()
                        ->hint('Optional — if submitting on behalf of a client'),
                    Forms\Components\TextInput::make('value')
                        ->label('Bid Value')
                        ->numeric()
                        ->prefix('$')
                        ->maxValue(999999999.99),
                    Forms\Components\TextInput::make('currency')
                        ->default('USD')
                        ->maxLength(10),
                    Forms\Components\DateTimePicker::make('submission_deadline')
                        ->label('Submission Deadline')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y, H:i'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft'        => 'Draft',
                            'submitted'    => 'Submitted',
                            'under_review' => 'Under Review',
                            'approved'     => 'Approved',
                            'rejected'     => 'Rejected',
                            'expired'      => 'Expired',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\DateTimePicker::make('submitted_at')
                        ->label('Date Submitted')
                        ->native(false)
                        ->displayFormat('d M Y, H:i')
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['submitted', 'under_review', 'approved', 'rejected'])),
                ])->columns(2),

            Forms\Components\Section::make('Description & Notes')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->label('Submission Description')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('outcome_notes')
                        ->label('Outcome / Feedback Notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => in_array($get('status'), ['approved', 'rejected'])),
                    Forms\Components\Textarea::make('notes')
                        ->label('Internal Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Attachments')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Upload Files')
                        ->disk('contabo')
                        ->directory('documents/praz-submissions')
                        ->visibility('private')
                        ->acceptedFileTypes(\App\Support\DocumentFileTypes::all())
                        ->maxSize(\App\Support\DocumentFileTypes::SIZE_50MB)
                        ->multiple()
                        ->reorderable()
                        ->storeFileNamesIn('attachment_names')
                        ->placeholder('Drag & drop your files here or Browse'),
                ])
                ->visibleOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Ref #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->title),
                Tables\Columns\TextColumn::make('procuring_entity')
                    ->label('Procuring Entity')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'goods'       => 'info',
                        'services'    => 'success',
                        'works'       => 'warning',
                        'consultancy' => 'gray',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft'        => 'gray',
                        'submitted'    => 'info',
                        'under_review' => 'warning',
                        'approved'     => 'success',
                        'rejected'     => 'danger',
                        'expired'      => 'gray',
                        default        => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        'expired'  => 'heroicon-o-clock',
                        default    => null,
                    }),
                Tables\Columns\TextColumn::make('submission_deadline')
                    ->label('Deadline')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->is_overdue ? 'danger' : null)
                    ->icon(fn ($record) => $record->is_overdue ? 'heroicon-o-exclamation-triangle' : null)
                    ->description(fn ($record) => $record->is_overdue ? 'OVERDUE' : null),
                Tables\Columns\TextColumn::make('value')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('preparedBy.name')
                    ->label('Prepared By')
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('submission_deadline', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'        => 'Draft',
                        'submitted'    => 'Submitted',
                        'under_review' => 'Under Review',
                        'approved'     => 'Approved',
                        'rejected'     => 'Rejected',
                        'expired'      => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'goods'       => 'Goods',
                        'services'    => 'Services',
                        'works'       => 'Works',
                        'consultancy' => 'Consultancy',
                    ]),
                Tables\Filters\TernaryFilter::make('overdue')
                    ->label('Overdue Only')
                    ->queries(
                        true: fn ($query) => $query
                            ->where('submission_deadline', '<', now())
                            ->whereNotIn('status', ['submitted', 'under_review', 'approved', 'rejected']),
                        false: fn ($query) => $query
                            ->where(function ($q) {
                                $q->where('submission_deadline', '>=', now())
                                  ->orWhereIn('status', ['submitted', 'under_review', 'approved', 'rejected']);
                            }),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('markSubmitted')
                    ->label('Mark Submitted')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Submitted')
                    ->modalDescription('This will set the status to "Submitted" and record the current date/time as the submission date.')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        $record->update([
                            'status'       => 'submitted',
                            'submitted_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Download Submission PDF')
                    ->action(function ($record) {
                        $record->load('client', 'preparedBy');
                        $pdf = Pdf::loadView('pdf.praz-submission', ['submission' => $record]);
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "praz-submission-{$record->reference_number}.pdf"
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrazSubmissions::route('/'),
            'create' => Pages\CreatePrazSubmission::route('/create'),
            'view'   => Pages\ViewPrazSubmission::route('/{record}'),
            'edit'   => Pages\EditPrazSubmission::route('/{record}/edit'),
        ];
    }
}
