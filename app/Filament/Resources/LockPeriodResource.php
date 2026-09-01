<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LockPeriodResource\Pages;
use App\Models\LockPeriod;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LockPeriodResource extends Resource
{
    protected static ?string $model = LockPeriod::class;
    
    // Konfigurasi Navigasi Side Menu
    protected static string|UnitEnum|null $navigationGroup = 'Menu Transaksi';
    protected static ?string $navigationLabel = 'Lock Period';
    protected static ?string $pluralModelLabel = 'Lock Period';
    protected static ?string $modelLabel = 'Lock Period';
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Select::make('warehouseid')
                    ->relationship('warehouse', 'warehouse_name')
                    ->required(),

                Components\Select::make('year')
                    ->options(array_combine(range(date('Y') - 5, date('Y') + 5), range(date('Y') - 5, date('Y') + 5)))
                    ->default(date('Y'))
                    ->required(),

                Components\Select::make('month')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ])
                    ->default((int) date('m'))
                    ->required(),

                Components\Toggle::make('is_locked')
                    ->label('Lock Status')
                    ->default(true),

                Components\Hidden::make('locked_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->label('Gudang')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                        default => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_locked')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Locked By')
                    ->default('-'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Update')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouseid')
                    ->relationship('warehouse', 'warehouse_name'),

                Tables\Filters\SelectFilter::make('year')
                    ->options(array_combine(range(date('Y') - 5, date('Y') + 5), range(date('Y') - 5, date('Y') + 5))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLockPeriods::route('/'),
            'create' => Pages\CreateLockPeriod::route('/create'),
            'edit' => Pages\EditLockPeriod::route('/{record}/edit'),
        ];
    }
}