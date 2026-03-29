<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
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
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('password')->password()->revealable()->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)->dehydrated(fn ($state) => filled($state))->required(fn (string $context) => $context === 'create'),
                Forms\Components\Select::make('department_id')->relationship('department', 'name')->searchable()->preload(),
                Forms\Components\Select::make('roles')->multiple()->relationship('roles', 'name')->preload(),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('department.name')->sortable(),
            Tables\Columns\TextColumn::make('roles.name')->badge(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('department')->relationship('department', 'name'),
            Tables\Filters\TernaryFilter::make('is_active'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('resetPassword')
                ->label('Reset Password')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->modalHeading(fn (User $record) => "Reset Password for {$record->name}")
                ->modalDescription('Set a new password for this user. They will need to use this new password to log in.')
                ->modalSubmitActionLabel('Reset Password')
                ->form([
                    Forms\Components\TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->same('password_confirmation'),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->dehydrated(false),
                ])
                ->action(function (User $record, array $data): void {
                    $record->update(['password' => Hash::make($data['password'])]);

                    Notification::make()
                        ->title("Password reset for {$record->name}.")
                        ->success()
                        ->send();
                }),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /** Merge phone_prefix + phone_local into phone_number and remove virtual keys. */
    public static function mergePhoneNumber(array $data): array
    {
        $prefix = $data['phone_prefix'] ?? '';
        $local  = preg_replace('/\D/', '', $data['phone_local'] ?? '');

        $data['phone_number'] = ($prefix && $local) ? $prefix . $local : null;

        unset($data['phone_prefix'], $data['phone_local']);

        return $data;
    }

    /**
     * Split a stored E.164-style number (e.g. +2637123456) back into prefix + local.
     * Falls back to +263 / raw value when no match is found.
     */
    public static function splitPhoneNumber(array $data): array
    {
        $phone = $data['phone_number'] ?? '';

        $data['phone_prefix'] = '+263';
        $data['phone_local']  = '';

        if ($phone) {
            // Try longest prefix first to avoid partial matches (+1 vs +1868 etc.)
            $codes = array_keys(self::dialCodes());
            usort($codes, fn ($a, $b) => strlen($b) - strlen($a));

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
