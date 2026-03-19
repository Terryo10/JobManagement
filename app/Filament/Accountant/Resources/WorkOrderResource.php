<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Work Orders';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Job Card')->tabs([

                // ─── TAB 1: GENERAL INFORMATION ──────────────────────────────
                Forms\Components\Tabs\Tab::make('General Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Section::make('General Information')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('reference_number')
                                    ->label('Reference Number')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generated on save')
                                    ->hiddenOn('create'),
                                Forms\Components\Select::make('status')
                                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                                    ->default('pending')
                                    ->required(),
                                Forms\Components\Select::make('category')
                                    ->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse'])
                                    ->required(),
                                Forms\Components\Select::make('priority')
                                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                                    ->default('normal')
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Organisation')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('client_id')
                                    ->relationship('client', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Client / Organisation')
                                    ->reactive()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('lead_id', null)),
                                Forms\Components\Select::make('assigned_department_id')
                                    ->relationship('assignedDepartment', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->label('Department'),
                                Forms\Components\Select::make('lead_id')
                                    ->relationship('lead', 'contact_name', fn ($query, Forms\Get $get) =>
                                        $get('client_id') ? $query->where('client_id', $get('client_id')) : $query->whereRaw('1 = 0')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (Forms\Get $get) => ! $get('client_id'))
                                    ->helperText('Select a client first')
                                    ->label('Lead'),
                            ]),
                    ]),

                // ─── TAB 2: DESIGN JOB CARD ──────────────────────────────────
                Forms\Components\Tabs\Tab::make('Design Job Card')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Forms\Components\Section::make('Design Job Card')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Job Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Project Description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Forms\Components\DatePicker::make('details.date_order_received')
                                    ->label('Date Order Received')
                                    ->native(false),
                                Forms\Components\DatePicker::make('deadline')
                                    ->label('Deadline')
                                    ->native(false),
                            ]),
                    ]),

                // ─── TAB 3: PROCUREMENT ───────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Procurement')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Forms\Components\Section::make('Logistics & Procurement Details')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Textarea::make('details.logistics')
                                    ->label('Logistics')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.procurement_details')
                                    ->label('Procurement Details')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Supplier Information')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('details.supplier_name')
                                    ->label('Supplier Name')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('details.supplier_contact')
                                    ->label('Supplier Contact')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('details.supplier_address')
                                    ->label('Supplier Address')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Materials & Costs')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Textarea::make('details.material_specifications')
                                    ->label('Material Specifications')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('details.quantity')
                                    ->label('Quantity')
                                    ->numeric(),
                                Forms\Components\TextInput::make('details.unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('budget')
                                    ->label('Total Cost / Budget')
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('actual_cost')
                                    ->label('Actual Cost')
                                    ->numeric()
                                    ->prefix('$'),
                            ]),

                        Forms\Components\Section::make('Process & Approvals')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Textarea::make('details.procurement_process')
                                    ->label('Procurement Process')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.approval_process')
                                    ->label('Approval Process')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.procurement_timeline')
                                    ->label('Timeline')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('budget_alert_threshold')
                                    ->label('Budget Alert Threshold (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(80),
                                Forms\Components\DatePicker::make('details.procurement_deadline')
                                    ->label('Procurement Deadline')
                                    ->native(false),
                                Forms\Components\Textarea::make('details.procurement_additional_info')
                                    ->label('Additional Information')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // ─── TAB 4: PRODUCTION ────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Production')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Section::make('Production Job Card')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('details.job_number')
                                    ->label('Job Number')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('details.sign_type')
                                    ->label('Sign Type')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('details.production_quantity')
                                    ->label('Quantity')
                                    ->numeric(),
                                Forms\Components\TextInput::make('details.size_and_material')
                                    ->label('Size and Material')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('details.job_description')
                                    ->label('Job Description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Design and Content')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('details.design_file')
                                    ->label('Design File / Reference')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.text_and_graphics')
                                    ->label('Text and Graphics')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('details.colour_scheme')
                                    ->label('Colour Scheme')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Section::make('Production Requirements')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Textarea::make('details.finishing_requirements')
                                    ->label('Finishing Requirements')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\DatePicker::make('details.production_deadline')
                                    ->label('Production Deadline')
                                    ->native(false),
                            ]),
                    ]),

                // ─── TAB 5: DELIVERY & INSTALLATION ──────────────────────────
                Forms\Components\Tabs\Tab::make('Delivery & Installation')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Forms\Components\Section::make('Delivery and Installation')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Textarea::make('details.delivery_address')
                                    ->label('Delivery Address')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.installation_requirements')
                                    ->label('Installation Requirements')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.delivery_additional_info')
                                    ->label('Additional Information')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\DatePicker::make('details.delivery_deadline')
                                    ->label('Delivery Deadline')
                                    ->native(false),
                                Forms\Components\DatePicker::make('details.date_of_job_completion')
                                    ->label('Date of Job Completion')
                                    ->native(false),
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->native(false),
                            ]),
                    ]),

                // ─── TAB 6: ASSESSMENT / REPORT ──────────────────────────────
                Forms\Components\Tabs\Tab::make('Assessment / Report')
                    ->icon('heroicon-o-document-chart-bar')
                    ->schema([
                        Forms\Components\Section::make('Assessment & Report')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('details.assessment_timeframe')
                                    ->label('Timeframe')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('details.challenges')
                                    ->label('Challenges')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.client_feedback')
                                    ->label('Client Feedback')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('details.resolutions')
                                    ->label('Resolutions')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Signatures')
                            ->description('Authorisation and sign-off')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('details.signature_1_name')->label('Name')->maxLength(255),
                                Forms\Components\TextInput::make('details.signature_1_sign')->label('Signature')->maxLength(255),
                                Forms\Components\DateTimePicker::make('details.signature_1_datetime')->label('Date & Time')->native(false),

                                Forms\Components\TextInput::make('details.signature_2_name')->label('Name')->maxLength(255),
                                Forms\Components\TextInput::make('details.signature_2_sign')->label('Signature')->maxLength(255),
                                Forms\Components\DateTimePicker::make('details.signature_2_datetime')->label('Date & Time')->native(false),

                                Forms\Components\TextInput::make('details.signature_3_name')->label('Name')->maxLength(255),
                                Forms\Components\TextInput::make('details.signature_3_sign')->label('Signature')->maxLength(255),
                                Forms\Components\DateTimePicker::make('details.signature_3_datetime')->label('Date & Time')->native(false),
                            ]),
                    ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->limit(40)->searchable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client'),
            Tables\Columns\TextColumn::make('budget')->money('usd')->sortable(),
            Tables\Columns\TextColumn::make('category')->badge()
                ->color(fn ($state) => match ($state) {
                    'media' => 'primary', 'civil_works' => 'warning',
                    'energy' => 'success', 'warehouse' => 'info', default => 'gray',
                }),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Tables\Filters\SelectFilter::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse']),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('General Information')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('reference_number')->label('Reference #'),
                    Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('client.company_name')->label('Client'),
                    Infolists\Components\TextEntry::make('assignedDepartment.name')->label('Department'),
                    Infolists\Components\TextEntry::make('category')->badge(),
                    Infolists\Components\TextEntry::make('status')->badge()->color(fn ($state) => match ($state) {
                        'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                        'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
                    Infolists\Components\TextEntry::make('priority')->badge()->color(fn ($state) => match ($state) {
                        'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
                    }),
                ]),

            Infolists\Components\Section::make('Design Job Card')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('description')->label('Project Description')->columnSpanFull()->html(),
                    Infolists\Components\TextEntry::make('details.date_order_received')->label('Date Order Received')->date()->placeholder('—'),
                    Infolists\Components\TextEntry::make('deadline')->label('Deadline')->date()->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Procurement Job Card')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('details.logistics')->label('Logistics')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.procurement_details')->label('Procurement Details')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.supplier_name')->label('Supplier')->default('—'),
                    Infolists\Components\TextEntry::make('details.supplier_contact')->label('Supplier Contact')->default('—'),
                    Infolists\Components\TextEntry::make('details.material_specifications')->label('Material Specifications')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.quantity')->label('Quantity')->default('—'),
                    Infolists\Components\TextEntry::make('details.unit_price')->label('Unit Price')->money('usd')->default('—'),
                    Infolists\Components\TextEntry::make('budget')->label('Total Cost / Budget')->money('usd'),
                    Infolists\Components\TextEntry::make('actual_cost')->label('Actual Cost')->money('usd'),
                    Infolists\Components\TextEntry::make('details.procurement_deadline')->label('Procurement Deadline')->date()->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Production Job Card')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('details.job_number')->label('Job Number')->default('—'),
                    Infolists\Components\TextEntry::make('details.sign_type')->label('Sign Type')->default('—'),
                    Infolists\Components\TextEntry::make('details.production_quantity')->label('Quantity')->default('—'),
                    Infolists\Components\TextEntry::make('details.size_and_material')->label('Size & Material')->default('—'),
                    Infolists\Components\TextEntry::make('details.job_description')->label('Job Description')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.design_file')->label('Design File')->default('—'),
                    Infolists\Components\TextEntry::make('details.colour_scheme')->label('Colour Scheme')->default('—'),
                    Infolists\Components\TextEntry::make('details.text_and_graphics')->label('Text & Graphics')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.finishing_requirements')->label('Finishing Requirements')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.production_deadline')->label('Production Deadline')->date()->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Delivery & Installation')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('details.delivery_address')->label('Delivery Address')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.installation_requirements')->label('Installation Requirements')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.delivery_deadline')->label('Delivery Deadline')->date()->placeholder('—'),
                    Infolists\Components\TextEntry::make('details.date_of_job_completion')->label('Date of Completion')->date()->placeholder('—'),
                    Infolists\Components\TextEntry::make('start_date')->label('Start Date')->date(),
                ]),

            Infolists\Components\Section::make('Assessment / Report')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('details.assessment_timeframe')->label('Timeframe')->default('—'),
                    Infolists\Components\TextEntry::make('details.challenges')->label('Challenges')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.client_feedback')->label('Client Feedback')->default('—')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('details.resolutions')->label('Resolutions')->default('—')->columnSpanFull(),
                ]),

            Infolists\Components\Section::make('Signatures')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('details.signature_1_name')->label('Signatory 1 — Name')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_1_sign')->label('Signature')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_1_datetime')->label('Date & Time')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_2_name')->label('Signatory 2 — Name')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_2_sign')->label('Signature')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_2_datetime')->label('Date & Time')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_3_name')->label('Signatory 3 — Name')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_3_sign')->label('Signature')->default('—'),
                    Infolists\Components\TextEntry::make('details.signature_3_datetime')->label('Date & Time')->default('—'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Accountant\Resources\WorkOrderResource\RelationManagers\DocumentsRelationManager::class,
            \App\Filament\Accountant\Resources\WorkOrderResource\RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit'   => Pages\EditWorkOrder::route('/{record}/edit'),
            'view'   => Pages\ViewWorkOrder::route('/{record}'),
        ];
    }
}
