<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Category;
use App\Models\Item;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Barang';
    protected static ?string $pluralModelLabel = 'Barang';
    protected static ?string $modelLabel = 'Barang';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Kategori dipasangi filter hak akses sesuai usergroup & reactive
                Components\Select::make('category_code')
                    ->label('Kategori')
                    ->options(function () {
                        /** @var \App\Models\User $user */
                        $user = auth()->user();

                        if (! $user) {
                            return [];
                        }

                        // Jika Superadmin (usergroupid === 1), tampilkan semua kategori
                        if ((int) $user->usergroupid === 1) {
                            return Category::pluck('category_name', 'category_code');
                        }

                        // Tampilkan hanya kategori yang terhubung ke usergroup user login
                        return Category::whereHas('userGroups', function ($query) use ($user) {
                            $query->where('user_groups.usergroupid', $user->usergroupid);
                        })->pluck('category_name', 'category_code');
                    })
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! $state) {
                            $set('item_code', null);

                            return;
                        }

                        // Cari item terakhir dengan kode kategori ini
                        $lastItem = Item::where('category_code', $state)
                            ->orderBy('item_code', 'desc')
                            ->first();

                        if (! $lastItem) {
                            $nextNumber = 1;
                        } else {
                            // Extract angka dari kode item (misal: ATK0002 -> 2)
                            $numericPart = preg_replace('/[^0-9]/', '', $lastItem->item_code);
                            $nextNumber = intval($numericPart) + 1;
                        }

                        // Format angka menjadi 4 digit pad (contoh: ATK + 0001 = ATK0001)
                        $generatedCode = $state . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                        $set('item_code', $generatedCode);
                    }),

                // 2. Kode barang disabled / read-only & terisi otomatis
                Components\TextInput::make('item_code')
                    ->label('Kode Barang')
                    ->required()
                    ->disabled()
                    ->dehydrated() // Tetap ter-save ke database meski statusnya disabled
                    ->maxLength(50),

                Components\TextInput::make('description')
                    ->label('Deskripsi / Nama')
                    ->required()
                    ->maxLength(255),

                Components\TextInput::make('uom1')
                    ->label('UOM 1 (e.g. Box)')
                    ->required()
                    ->maxLength(50),

                Components\TextInput::make('uom2')
                    ->label('UOM 2 (e.g. Pcs)')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.category_name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('uom1')
                    ->label('Satuan 1'),

                Tables\Columns\TextColumn::make('uom2')
                    ->label('Satuan 2'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user ? $user->hasMenuAccess(8) : false;
    }
}