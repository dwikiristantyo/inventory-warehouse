<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdjustmentResource\Pages;
use App\Models\Item;
use App\Models\TransactionHeader;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AdjustmentResource extends Resource
{
    protected static ?string $model = TransactionHeader::class;
    protected static string|UnitEnum|null $navigationGroup = 'Menu Transaksi';
    protected static ?string $navigationLabel = 'Adjustment';
    protected static ?string $pluralModelLabel = 'Adjustment';
    protected static ?string $modelLabel = 'Adjustment';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Hidden::make('trans_type')->default('ADJ'),
                Components\TextInput::make('trans_no')
                    ->default('ADJ-' . date('YmdHis'))
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Components\DatePicker::make('trans_date')
                    ->default(now())
                    ->required(),
                Components\Select::make('warehouseid')
                    ->relationship('warehouse', 'warehouse_name')
                    ->required(),
                Components\Textarea::make('remark')->columnSpanFull(),

                Components\Repeater::make('details')
                    ->relationship('details')
                    ->schema([
                        Components\Select::make('item_code')
                            ->relationship('item', 'description')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    $set('uom1_label', null);
                                    $set('uom2_label', null);
                                    return;
                                }

                                $item = Item::where('item_code', $state)->first();
                                if ($item) {
                                    $set('uom1_label', $item->uom1);
                                    $set('uom2_label', $item->uom2);
                                }
                            }),

                        Components\TextInput::make('qty_uom1')
                            ->label(function (Get $get) {
                                $uom1 = $get('uom1_label');
                                return $uom1 ? "Qty 1 ({$uom1})" : 'Qty 1';
                            })
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Components\TextInput::make('qty_uom2')
                            ->label(function (Get $get) {
                                $uom2 = $get('uom2_label');
                                return $uom2 ? "Qty 2 ({$uom2})" : 'Qty 2';
                            })
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Components\Hidden::make('uom1_label'),
                        Components\Hidden::make('uom2_label'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(TransactionHeader::query()->where('trans_type', 'ADJ')->where('status', '!=', 'X'))
            ->defaultSort('created_at', 'desc')
            ->paginated([30, 50, 100, 'all'])
            ->columns([
                Tables\Columns\TextColumn::make('trans_no')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('trans_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')->sortable(),
                Tables\Columns\TextColumn::make('remark')->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'A',
                        'success' => 'P',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'A' => 'Aktif',
                        'P' => 'Posted',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('trans_date')
                    ->form([
                        Components\DatePicker::make('from'),
                        Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('trans_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('trans_date', '<=', $data['until']));
                    }),
                Tables\Filters\SelectFilter::make('warehouseid')
                    ->relationship('warehouse', 'warehouse_name'),
            ])
            ->actions([
                EditAction::make()
                    ->hidden(fn (TransactionHeader $record) => $record->status === 'P' || $record->isPeriodLocked()),

                Action::make('unpost')
                    ->label('Un-Post')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (TransactionHeader $record) => $record->status === 'P' && ! $record->isPeriodLocked())
                    ->action(function (TransactionHeader $record) {
                        $record->update(['status' => 'A']);
                        Notification::make()->title('Status berhasil diubah ke Aktif')->success()->send();
                    }),

                DeleteAction::make()
                    ->hidden(fn (TransactionHeader $record) => $record->status === 'P' || $record->isPeriodLocked())
                    ->action(fn (TransactionHeader $record) => $record->update(['status' => 'X'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdjustments::route('/'),
            'create' => Pages\CreateAdjustment::route('/create'),
            'edit' => Pages\EditAdjustment::route('/{record}/edit'),
        ];
    }
}