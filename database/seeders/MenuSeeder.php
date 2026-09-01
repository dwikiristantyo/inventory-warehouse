<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Dashboard', 'route' => 'filament.admin.pages.dashboard'],
            ['name' => 'Pengguna', 'route' => 'filament.admin.resources.users.index'],
            ['name' => 'Grup Pengguna', 'route' => 'filament.admin.resources.user-groups.index'],
            ['name' => 'Laporan', 'route' => null],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(['name' => $menu['name']], $menu);
        }
    }
}