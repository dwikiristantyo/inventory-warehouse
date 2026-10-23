<?php

namespace Tests\Feature;

use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MenuSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_every_filament_menu_without_changing_permission_menu_ids(): void
    {
        $seeder = new MenuSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('menus', 16);
        $this->assertDatabaseHas('menus', [
            'id' => 3,
            'route' => 'filament.admin.resources.adjustments.index',
        ]);
        $this->assertDatabaseHas('menus', [
            'id' => 11,
            'route' => 'filament.admin.resources.user-groups.index',
        ]);
        $this->assertDatabaseHas('menus', [
            'id' => 12,
            'route' => 'filament.admin.resources.users.index',
        ]);

        $this->assertSame(
            16,
            DB::table('menus')->whereNotNull('route')->distinct('route')->count('route'),
        );
    }
}
