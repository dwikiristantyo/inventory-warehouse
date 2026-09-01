<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Item;
use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles / Groups
        $adminGroup = UserGroup::create(['description' => 'Administrator']);
        $headGroup = UserGroup::create(['description' => 'Head Warehouse']);
        $adminWhGroup = UserGroup::create(['description' => 'Admin Warehouse']);

        // 2. Demo Administrator Account (admin / admin123)
        $adminUser = User::create([
            'nik' => 'ADM-001',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'usergroupid' => $adminGroup->usergroupid,
            'status' => 'active',
        ]);

        // 3. Generate 100 Companies
        for ($i = 1; $i <= 100; $i++) {
            Company::create([
                'company_name' => "PT. Perusahaan Nusantara $i",
                'business_line' => "Industri Logistics $i",
                'address' => "Jl. Raya Logistik No. $i, Jakarta",
                'status' => 'active',
            ]);
        }

        // 4. Generate 100 Warehouses
        for ($i = 1; $i <= 100; $i++) {
            Warehouse::create([
                'companyid' => rand(1, 100),
                'warehouse_name' => "Gudang Utama $i",
                'address' => "Kawasan Industri Block B No. $i",
                'phone' => "021-555" . sprintf('%04d', $i),
                'status' => 'active',
            ]);
        }

        // Attach company & warehouse to admin
        $adminUser::first()->companies()->attach(Company::pluck('companyid'));
        $adminUser::first()->warehouses()->attach(Warehouse::pluck('warehouseid'));

        // 5. Generate 100 Categories
        for ($i = 1; $i <= 100; $i++) {
            Category::create([
                'category_code' => "CAT-" . sprintf('%03d', $i),
                'category_name' => "Kategori Produk $i",
                'status' => 'active',
            ]);
        }

        // 6. Generate 100 Items
        for ($i = 1; $i <= 100; $i++) {
            Item::create([
                'item_code' => "SKU-" . sprintf('%05d', $i),
                'category_code' => "CAT-" . sprintf('%03d', rand(1, 100)),
                'description' => "Barang Material Sparepart Grade $i",
                'uprice' => rand(10000, 500000),
                'uom1' => 'BOX',
                'uom2' => 'PCS',
                'conv_qty' => 12,
                'status' => 'active',
            ]);
        }

        // 7. Generate 100 Transactions & Details
        $types = ['IN', 'OUT', 'ADJ'];
        for ($i = 1; $i <= 100; $i++) {
            $type = $types[array_rand($types)];
            $header = TransactionHeader::create([
                'trans_no' => "$type-" . date('Ymd') . "-" . sprintf('%04d', $i),
                'trans_type' => $type,
                'trans_date' => now()->subDays(rand(1, 30)),
                'warehouseid' => rand(1, 100),
                'remark' => "Catatan otomatis transaksi dummy $i",
                'status' => rand(0, 1) ? 'A' : 'P',
                'created_by' => $adminUser->id,
            ]);

            // Add Details
            TransactionDetail::create([
                'trans_id' => $header->trans_id,
                'item_code' => "SKU-" . sprintf('%05d', rand(1, 100)),
                'qty_uom1' => rand(1, 50),
                'qty_uom2' => rand(1, 500),
            ]);
        }
    }
}