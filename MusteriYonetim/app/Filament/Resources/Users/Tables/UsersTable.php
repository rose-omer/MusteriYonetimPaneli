<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Ad soyad')
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Eklenme')
                    ->date('d.m.Y')
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('role')->label('Rol')->options(UserRole::class),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->label('Düzenle'),
                DeleteAction::make()->iconButton()->label('Sil'),
            ]);
    }
}
