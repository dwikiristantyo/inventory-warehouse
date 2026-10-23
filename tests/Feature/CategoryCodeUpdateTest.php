<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryCodeUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_category_code_updates_related_items_and_user_groups(): void
    {
        DB::table('categories')->insert([
            'category_code' => 'BUKU',
            'category_name' => 'Buku',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('items')->insert([
            'item_code' => 'BUKU0001',
            'category_code' => 'BUKU',
            'description' => 'Buku tulis',
            'uprice' => 0,
            'uom1' => 'PCS',
            'uom2' => 'PCS',
            'conv_qty' => 1,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userGroupId = DB::table('user_groups')->insertGetId([
            'description' => 'Pengguna gudang',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'usergroupid');

        DB::table('category_user_group')->insert([
            'usergroupid' => $userGroupId,
            'category_code' => 'BUKU',
        ]);

        DB::table('categories')
            ->where('category_code', 'BUKU')
            ->update(['category_code' => 'BUK']);

        $this->assertDatabaseHas('items', [
            'item_code' => 'BUKU0001',
            'category_code' => 'BUK',
        ]);
        $this->assertDatabaseHas('category_user_group', [
            'usergroupid' => $userGroupId,
            'category_code' => 'BUK',
        ]);
    }
}
