<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\PrazSubmissionResource\Pages;
use App\Filament\Marketing\Resources\PrazSubmissionResource\RelationManagers;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;
use App\Models\PrazSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrazSubmissionResource extends Resource
{
    use EnforcesAdminDelete;

    protected static ?string $model = PrazSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Pipeline';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'PRAZ Submissions';
    protected static ?string $modelLabel = 'PRAZ Submission';
    protected static ?string $pluralModelLabel = 'PRAZ Submissions';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submission Details')
                ->description('Provide the description, optional client, and internal notes.')
                ->icon('heroicon-o-building-library')
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->label('Submission Description')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'company_name')
                        ->searchable()
                        ->preload()
                        ->hint('Optional — if submitting on behalf of a client')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Internal Notes')
                        ->rows(4)
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
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->html()
                    ->tooltip(fn ($record) => strip_tags($record->description)),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
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
                            ->whereNotNull('submission_deadline')
                            ->where('submission_deadline', '<', now())
                            ->whereNotIn('status', ['submitted', 'under_review', 'approved', 'rejected']),
                        false: fn ($query) => $query
                            ->where(function ($q) {
                                $q->whereNull('submission_deadline')
                                  ->orWhere('submission_deadline', '>=', now())
                                  ->orWhereIn('status', ['submitted', 'under_review', 'approved', 'rejected']);
                            }),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markSubmitted')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->button()
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
