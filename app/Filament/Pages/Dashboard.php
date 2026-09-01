<?php

namespace App\Filament\Pages;

use App\Models\Item;
use App\Models\TransactionHeader;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Inventory Warehouse Dashboard';
    protected static ?int $navigationSort = -2;

    // Properti $view HARUS non-static (tanpa kata 'static')
    protected string $view = 'dashboard';

    // Gunakan method getter untuk ikon navigasi agar tidak pernah bentrok dengan type-hint induk
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-home';
    }

    public function getViewData(): array
    {
        return [
            'stats' => [
                'total_warehouses' => Warehouse::count(),
                'total_items' => Item::count(),
                'total_transactions' => TransactionHeader::count(),
                'latest_warehouses' => Warehouse::with('company')->take(5)->get(),
            ]
        ];
    }
}