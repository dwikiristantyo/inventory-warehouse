<?php

namespace App\Filament\Resources;

use UnitEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Company;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan System';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->columns(2)
                    ->schema([
                        TextInput::make('username')
                            ->label('Username')
                            ->placeholder('Masukkan username')
                            ->autocomplete(false)
                            ->required()
                            ->maxLength(255),

                        TextInput::make('nik')
                            ->label('NIK')
                            ->placeholder('Masukkan NIK')
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('contoh@domain.com')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                    ]),

                Section::make('Hak Akses & Role')
                    ->columns(2)
                    ->schema([
                        Select::make('usergroupid')
                            ->label('Role / User Group')
                            ->relationship('userGroup', 'description')
                            ->searchable()
                            ->preload(),

                        Select::make('companies')
                            ->label('Akses Perusahaan')
                            ->relationship(
                                name: 'companies',
                                titleAttribute: 'company_name',
                                // Menyeleksi companyid (PK), alias, company_name, dan business_line
                                modifyQueryUsing: fn (Builder $query) => $query->select([
                                    'companies.companyid', 
                                    'companies.alias', 
                                    'companies.company_name', 
                                    'companies.business_line'
                                ])
                            )
                            ->getOptionLabelFromRecordUsing(fn (Company $record) => "{$record->alias} - {$record->company_name} - {$record->business_line}")
                            ->getSearchResultsUsing(function (string $search) {
                                return Company::where('companies.alias', 'like', "%{$search}%")
                                    ->orWhere('companies.company_name', 'like', "%{$search}%")
                                    ->orWhere('companies.business_line', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (Company $company) => [
                                        $company->companyid => "{$company->alias} - {$company->company_name} - {$company->business_line}"
                                    ]);
                            })
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Select::make('warehouses')
                            ->label('Akses Gudang / Warehouse')
                            ->relationship('warehouses', 'warehouse_name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('userGroup.description')
                    ->label('Role')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}