<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\TransactionHeader;
use App\Models\Warehouse;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Gudang', Warehouse::where('status', 'active')->count()),
            Stat::make('Total Item Barang', Item::where('status', 'active')->count()),
            Stat::make('Total Transaksi', TransactionHeader::where('status', '!=', 'X')->count()),
        ];
    }
}