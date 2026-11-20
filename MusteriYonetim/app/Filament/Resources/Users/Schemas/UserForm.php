<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Hesap')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label('Ad soyad')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make('Rol')
                    ->description('Rol, kullanıcının müşteri kayıtlarında neler yapabileceğini belirler.')
                    ->schema([
                        Radio::make('role')
                            ->hiddenLabel()
                            ->options(UserRole::class)
                            ->descriptions([
                                UserRole::Admin->value => 'Her şeyi yapar: müşteri siler, içe aktarır, kullanıcı ve faaliyet yönetir.',
                                UserRole::Editor->value => 'Müşteri ekler ve düzenler. Telefonu tam görür. Silemez, içe aktaramaz.',
                                UserRole::Viewer->value => 'Yalnız görüntüler. Telefon numarasını maskeli görür.',
                            ])
                            ->default(UserRole::Viewer->value)
                            // Son yönetici rolünü düşüremez; yoksa kullanıcı yönetimi kilitlenir.
                            ->disableOptionWhen(fn (string $value, ?User $record) => $value !== UserRole::Admin->value && self::isLastAdmin($record))
                            ->helperText(fn (?User $record) => self::isLastAdmin($record) ? 'Bu hesap tek yönetici. Rolünü değiştirmek için önce başka bir yönetici ekleyin.' : null)
                            ->rule(fn (?User $record) => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                                $role = $value instanceof UserRole ? $value->value : $value;
                                if ($role !== UserRole::Admin->value && self::isLastAdmin($record)) {
                                    $fail('Tek yöneticinin rolü değiştirilemez. Önce başka bir yönetici ekleyin.');
                                }
                            })
                            ->required(),
                    ]),
                Section::make('Parola')
                    ->visibleOn('create')
                    ->schema([
                        Toggle::make('davet_gonder')
                            ->label('Davet e-postası gönder')
                            ->helperText('Kullanıcı parolasını e-postadaki bağlantıyla kendisi belirler.')
                            ->default(true)
                            ->live()
                            ->dehydrated(false),
                        TextInput::make('password')
                            ->label('Geçici parola')
                            ->password()
                            ->revealable()
                            ->minLength(12)
                            ->required(fn (Get $get) => ! $get('davet_gonder'))
                            ->visible(fn (Get $get) => ! $get('davet_gonder')),
                    ]),
            ]);
    }

    private static function isLastAdmin(?User $record): bool
    {
        return $record?->exists === true
            && $record->getOriginal('role') === UserRole::Admin
            && User::where('role', UserRole::Admin)->count() === 1;
    }
}
