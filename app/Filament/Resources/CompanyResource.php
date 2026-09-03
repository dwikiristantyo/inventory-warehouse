<?php

namespace App\Filament\Resources;

use UnitEnum;
use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Schemas\Schema;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan System';
    protected static ?string $navigationLabel = 'Perusahaan';
    protected static ?string $pluralModelLabel = 'Perusahaan';
    protected static ?string $modelLabel = 'Perusahaan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('company_name')
                    ->label('Nama Perusahaan')
                    ->required()
                    ->maxLength(255),
                Components\TextInput::make('alias')
                    ->label('Alias')
                    ->maxLength(50),
                Components\TextInput::make('business_line')
                    ->label('Business Line')
                    ->maxLength(255),
                Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Components\Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alias')
                    ->label('Alias')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_line')
                    ->label('Business Line')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
{
    /** @var \App\Models\User $user */
    $user = auth()->user();
    return $user ? $user->hasMenuAccess(10) : false; // Ganti '8' sesuai menu_id Perusahaan
}
}