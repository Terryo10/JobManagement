<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FieldWorkerResource\Pages;
use App\Models\FieldWorker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FieldWorkerResource extends Resource
{
    protected static ?string $model = FieldWorker::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Field Workers';
    protected static ?string $modelLabel = 'Field Worker';
    protected static ?string $pluralModelLabel = 'Field Workers';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Field Worker Details')
                ->description('Field workers are not system users. They cannot log in but can be assigned to tasks and receive email/SMS notifications.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->options([
                            'Internal' => 'Internal',
                            'External' => 'External',
                        ])
                        ->required()
                        ->helperText('Internal: company employee. External: contractor or freelancer.'),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->maxLength(255)
                        ->nullable()
                        ->helperText('Used for email notifications when assigned to a task.'),
                    // Phone: country-code selector + local number — stored combined in phone_number
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('phone_prefix')
                                ->label('Code')
                                ->options(self::dialCodes())
                                ->default('+263')
                                ->searchable()
                                ->native(false)
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('phone_local')
                                ->label('Phone Number')
                                ->tel()
                                ->placeholder('712 345 678')
                                ->maxLength(15)
                                ->helperText('Used for SMS/WhatsApp notifications.')
                                ->columnSpan(2),
                        ])
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Internal' => 'success',
                        'External' => 'warning',
                        default    => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->placeholder('—')
                    ->copyable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Internal' => 'Internal',
                        'External' => 'External',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            Infolists\Components\Section::make('Field Worker Details')->schema([
                Infolists\Components\TextEntry::make('name')->weight('bold'),
                Infolists\Components\TextEntry::make('type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Internal' => 'success',
                        'External' => 'warning',
                        default    => 'gray',
                    }),
                Infolists\Components\TextEntry::make('email')->placeholder('—')->copyable(),
                Infolists\Components\TextEntry::make('phone_number')->label('Phone')->placeholder('—')->copyable(),
                Infolists\Components\TextEntry::make('created_at')->label('Registered')->date(),
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
            'index'  => Pages\ListFieldWorkers::route('/'),
            'create' => Pages\CreateFieldWorker::route('/create'),
            'edit'   => Pages\EditFieldWorker::route('/{record}/edit'),
            'view'   => Pages\ViewFieldWorker::route('/{record}'),
        ];
    }

    // ─── Phone helpers (same pattern as UserResource) ────────────────────────

    /** Merge phone_prefix + phone_local into phone_number before saving. */
    public static function mergePhoneNumber(array $data): array
    {
        $prefix = $data['phone_prefix'] ?? '';
        $local  = preg_replace('/\D/', '', $data['phone_local'] ?? '');

        $data['phone_number'] = ($prefix && $local) ? $prefix . $local : null;

        unset($data['phone_prefix'], $data['phone_local']);

        return $data;
    }

    /**
     * Split a stored E.164-style number (e.g. +2637123456) back into prefix + local
     * when filling the edit form. Falls back to +263 / raw value when no match found.
     */
    public static function splitPhoneNumber(array $data): array
    {
        $phone = $data['phone_number'] ?? '';

        $data['phone_prefix'] = '+263';
        $data['phone_local']  = '';

        if ($phone) {
            $codes = array_keys(self::dialCodes());
            usort($codes, fn ($a, $b) => strlen($b) - strlen($a)); // longest first

            foreach ($codes as $code) {
                if (str_starts_with($phone, $code)) {
                    $data['phone_prefix'] = $code;
                    $data['phone_local']  = substr($phone, strlen($code));
                    break;
                }
            }

            if ($data['phone_local'] === '') {
                $data['phone_local'] = $phone;
            }
        }

        return $data;
    }

    public static function dialCodes(): array
    {
        return [
            '+263' => '🇿🇼 Zimbabwe (+263)',
            '+27'  => '🇿🇦 South Africa (+27)',
            '+260' => '🇿🇲 Zambia (+260)',
            '+267' => '🇧🇼 Botswana (+267)',
            '+258' => '🇲🇿 Mozambique (+258)',
            '+265' => '🇲🇼 Malawi (+265)',
            '+264' => '🇳🇦 Namibia (+264)',
            '+268' => '🇸🇿 Eswatini (+268)',
            '+266' => '🇱🇸 Lesotho (+266)',
            '+255' => '🇹🇿 Tanzania (+255)',
            '+254' => '🇰🇪 Kenya (+254)',
            '+256' => '🇺🇬 Uganda (+256)',
            '+251' => '🇪🇹 Ethiopia (+251)',
            '+233' => '🇬🇭 Ghana (+233)',
            '+234' => '🇳🇬 Nigeria (+234)',
            '+225' => '🇨🇮 Côte d\'Ivoire (+225)',
            '+20'  => '🇪🇬 Egypt (+20)',
            '+212' => '🇲🇦 Morocco (+212)',
            '+44'  => '🇬🇧 United Kingdom (+44)',
            '+1'   => '🇺🇸 USA / Canada (+1)',
            '+61'  => '🇦🇺 Australia (+61)',
            '+91'  => '🇮🇳 India (+91)',
            '+86'  => '🇨🇳 China (+86)',
            '+971' => '🇦🇪 UAE (+971)',
            '+49'  => '🇩🇪 Germany (+49)',
            '+33'  => '🇫🇷 France (+33)',
            '+55'  => '🇧🇷 Brazil (+55)',
        ];
    }
}
