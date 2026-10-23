<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Laporan Transaksi', 'route' => 'filament.admin.pages.report-page'],
            ['name' => 'Report Stock per Gudang', 'route' => 'filament.admin.pages.stock-per-warehouse-report-page'],
            ['name' => 'Adjustment', 'route' => 'filament.admin.resources.adjustments.index'],
            ['name' => 'Barang Masuk', 'route' => 'filament.admin.resources.goods-ins.index'],
            ['name' => 'Barang Keluar', 'route' => 'filament.admin.resources.goods-outs.index'],
            ['name' => 'Lock Period', 'route' => 'filament.admin.resources.lock-periods.index'],
            ['name' => 'Kategori', 'route' => 'filament.admin.resources.categories.index'],
            ['name' => 'Master Barang', 'route' => 'filament.admin.resources.items.index'],
            ['name' => 'Gudang', 'route' => 'filament.admin.resources.warehouses.index'],
            ['name' => 'Perusahaan', 'route' => 'filament.admin.resources.companies.index'],
            ['name' => 'Grup Pengguna', 'route' => 'filament.admin.resources.user-groups.index'],
            ['name' => 'Pengguna', 'route' => 'filament.admin.resources.users.index'],
            ['name' => 'Dashboard', 'route' => 'filament.admin.pages.dashboard'],
            ['name' => 'Stock Report by Item', 'route' => 'filament.admin.pages.stock-report-by-item-page'],
            ['name' => 'Stock Report by Warehouse', 'route' => 'filament.admin.pages.stock-report-by-warehouse-page'],
            ['name' => 'Daily Egg Matrix Report', 'route' => 'filament.admin.pages.daily-egg-matrix-report-page'],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(
                ['route' => $menu['route']],
                ['name' => $menu['name']],
            );
        }
    }
}
