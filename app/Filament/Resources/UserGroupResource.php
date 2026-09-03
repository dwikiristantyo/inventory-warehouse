<?php

namespace App\Filament\Resources;

use UnitEnum;
use App\Filament\Resources\UserGroupResource\Pages;
use App\Models\UserGroup;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList; // <-- Import CheckboxList
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class UserGroupResource extends Resource
{
    protected static ?string $model = UserGroup::class;
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan System';
    protected static ?string $navigationLabel = 'Grup Pengguna';
    protected static ?string $modelLabel = 'Grup Pengguna';
    protected static ?string $pluralModelLabel = 'Grup Pengguna';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Group')
                    ->schema([
                        TextInput::make('description')
                            ->label('Nama / Deskripsi Grup')
                            ->required()
                            ->maxLength(255),
                    ]),

                // --- SECTION BARU: AKSES KATEGORI BARANG ---
                Section::make('Hak Akses Kategori Barang')
                    ->description('Pilih kategori barang yang diizinkan untuk diakses oleh grup ini.')
                    ->schema([
                        CheckboxList::make('categories')
                            ->relationship('categories', 'category_name', fn ($query) => $query->where('status', 'active'))
                            ->columns(3)
                            ->bulkToggleable()
                            ->label('Kategori Terdaftar'),
                    ]),

                Section::make('Hak Akses Menu (Detail Permission)')
                    ->schema([
                        Repeater::make('details')
                            ->relationship('details')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->relationship('menu', 'name')
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                Toggle::make('can_view')
                                    ->label('View')
                                    ->default(false),

                                Toggle::make('can_add')
                                    ->label('Add')
                                    ->default(false),

                                Toggle::make('can_edit')
                                    ->label('Edit')
                                    ->default(false),

                                Toggle::make('can_delete')
                                    ->label('Delete')
                                    ->default(false),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Akses Menu')
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usergroupid')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Nama / Deskripsi Grup')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Jumlah Kategori')
                    ->counts('categories'),

                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jumlah Akses Menu')
                    ->counts('details'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserGroups::route('/'),
            'create' => Pages\CreateUserGroup::route('/create'),
            'edit' => Pages\EditUserGroup::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
{
    /** @var \App\Models\User $user */
    $user = auth()->user();
    return $user ? $user->hasMenuAccess(11) : false; // Ganti '9' sesuai menu_id Grup Pengguna
}
}