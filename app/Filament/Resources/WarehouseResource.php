<?php

namespace App\Filament\Resources;

use UnitEnum;
use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use App\Models\Company;
use Filament\Schemas\Schema;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Gudang';
    protected static ?string $pluralModelLabel = 'Gudang';
    protected static ?string $modelLabel = 'Gudang';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Select::make('companyid')
                    ->label('Perusahaan')
                    ->relationship('company', 'company_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->alias} - {$record->company_name} - {$record->business_line}")
                    ->getSearchResultsUsing(function (string $search) {
                        return Company::where('alias', 'ilike', "%{$search}%")
                            ->orWhere('company_name', 'ilike', "%{$search}%")
                            ->orWhere('business_line', 'ilike', "%{$search}%")
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($company) => [
                                ($company->companyid ?? $company->id) => "{$company->alias} - {$company->company_name} - {$company->business_line}"
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Components\TextInput::make('warehouse_name')
                    ->label('Nama Gudang')
                    ->required()
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
                Tables\Columns\TextColumn::make('company')
                    ->label('Perusahaan')
                    ->formatStateUsing(fn ($record) => $record->company 
                        ? "{$record->company->alias} - {$record->company->company_name} - {$record->company->business_line}" 
                        : '-'
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('company', function ($q) use ($search) {
                            $q->where('alias', 'ilike', "%{$search}%")
                              ->orWhere('company_name', 'ilike', "%{$search}%")
                              ->orWhere('business_line', 'ilike', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse_name')
                    ->label('Nama Gudang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}