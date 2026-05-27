<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\DesignBriefResource\Pages;
use App\Models\DesignBrief;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class DesignBriefResource extends Resource
{
    use EnforcesAdminDelete;

    protected static ?string $model = DesignBrief::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Design Briefs';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Design Brief Info')->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('work_order_id')
                            ->relationship('workOrder', 'reference_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                            ->searchable()
                            ->preload()
                            ->label('Job Card (Work Order)'),
                        Forms\Components\Select::make('designer_id')
                            ->label('Designer')
                            ->relationship(
                                'designer',
                                'name',
                                fn ($query) => $query->whereHas(
                                    'roles',
                                    fn ($q) => $q->whereNotIn('name', ['client'])
                                )->orderBy('name')
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('— Unassigned —'),
                        Forms\Components\Select::make('created_by')
                            ->relationship('createdBy', 'name')
                            ->disabled()
                            ->label('Created By')
                            ->placeholder('— System —'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending Review',
                                'in_progress' => 'In Progress',
                                'in_review' => 'In Review',
                                'revision_requested' => 'Revision Requested',
                                'completed' => 'Completed',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('normal')
                            ->required(),
                        Forms\Components\DatePicker::make('deadline'),
                    ])->columns(1),
                ])->columnSpan(1),

                Forms\Components\Group::make([
                    Forms\Components\Section::make('Creative Specifications')->schema([
                        Forms\Components\RichEditor::make('objective')
                            ->label('Objective & Project Description')
                            ->placeholder('What is the core message and purpose of this design?')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('deliverables')
                            ->placeholder("e.g. Print-ready PDF with crop marks, raw layered PSD/AI file, social media formats")
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('dimensions_specifications')
                            ->label('Dimensions & Technical Specs')
                            ->placeholder("e.g. 6x3 meters, 300 DPI, CMYK color space")
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('target_audience')
                            ->placeholder("Who is the primary audience for this graphic?")
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(1),
                ])->columnSpan(2),

                Forms\Components\Group::make([
                    Forms\Components\Section::make('Branding & Copy Guidelines')->schema([
                        Forms\Components\Textarea::make('copy_text')
                            ->label('Wording / Text Copy')
                            ->placeholder('Exact wording, taglines, or content elements that must be included on the graphic.')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('branding_guidelines')
                            ->placeholder('Color hex codes, visual constraints, fonts, logo usage rules...')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'undo', 'redo'])
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('notes_references')
                            ->label('Visual References & Inspirations')
                            ->placeholder('Links to moodboards, competitors, or other design inspiration.')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                            ->columnSpanFull(),
                    ])->columns(1),
                ])->columnSpan(3),
            ]),
            Forms\Components\Section::make('Attachments')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Upload Files')
                        ->disk('contabo')
                        ->directory('documents/design-briefs')
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
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(45),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card'),
                Tables\Columns\TextColumn::make('designer.name')
                    ->label('Designer')
                    ->placeholder('— Unassigned —')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->placeholder('— System —'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'in_review' => 'warning',
                        'revision_requested' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'low' => 'gray',
                        'normal' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending Review',
                        'in_progress' => 'In Progress',
                        'in_review' => 'In Review',
                        'revision_requested' => 'Revision Requested',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            \App\Filament\Marketing\Resources\DesignBriefResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDesignBriefs::route('/'),
            'create' => Pages\CreateDesignBrief::route('/create'),
            'view' => Pages\ViewDesignBrief::route('/{record}'),
            'edit' => Pages\EditDesignBrief::route('/{record}/edit'),
        ];
    }
}
