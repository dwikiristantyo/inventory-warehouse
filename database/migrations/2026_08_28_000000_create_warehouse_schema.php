<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Companies
        Schema::create('companies', function (Blueprint $table) {
            $table->id('companyid');
            $table->string('company_name');
            $table->string('business_line')->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Warehouses
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id('warehouseid');
            $table->foreignId('companyid')->constrained('companies', 'companyid')->cascadeOnDelete();
            $table->string('warehouse_name');
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 3. User Groups (Header & Detail)
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id('usergroupid');
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('user_group_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usergroupid')->constrained('user_groups', 'usergroupid')->cascadeOnDelete();
            $table->string('menu_id'); // E.g., 'master_user', 'transaction_goods_in', etc.
            $table->boolean('can_view')->default(false);
            $table->boolean('can_add')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
        });

        // 4. Users
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->foreignId('usergroupid')->constrained('user_groups', 'usergroupid');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        // User Pivot Tables (Multi Company & Warehouse)
        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('companyid')->constrained('companies', 'companyid')->cascadeOnDelete();
        });

        // Pivot user_companies (disesuaikan untuk pemanggilan Seeder / Relasi Kustom)
        Schema::create('user_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('companyid')->constrained('companies', 'companyid')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('user_warehouse', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouseid')->constrained('warehouses', 'warehouseid')->cascadeOnDelete();
        });

        // 5. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->string('category_code')->primary();
            $table->string('category_name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 6. Master Items
        Schema::create('items', function (Blueprint $table) {
            $table->string('item_code')->primary();
            $table->string('category_code');
            $table->foreign('category_code')->references('category_code')->on('categories')->restrictOnDelete();
            $table->text('description');
            $table->decimal('uprice', 15, 2)->default(0);
            $table->string('uom1', 20); // Box
            $table->string('uom2', 20); // Pcs
            $table->decimal('conv_qty', 10, 2)->default(1); // 1 UOM1 = X UOM2
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 7. Transaction Headers & Details (Barang Masuk, Barang Keluar, Adjustment)
        Schema::create('transaction_headers', function (Blueprint $table) {
            $table->id('trans_id');
            $table->string('trans_no')->unique();
            $table->enum('trans_type', ['IN', 'OUT', 'ADJ']);
            $table->date('trans_date');
            $table->foreignId('warehouseid')->constrained('warehouses', 'warehouseid');
            $table->text('remark')->nullable();
            $table->enum('status', ['A', 'P', 'X'])->default('A'); // A: Active, P: Posted, X: Inactive/Deleted
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trans_id')->constrained('transaction_headers', 'trans_id')->cascadeOnDelete();
            $table->string('item_code');
            $table->foreign('item_code')->references('item_code')->on('items');
            $table->decimal('qty_uom1', 12, 2)->default(0); // Qty Box
            $table->decimal('qty_uom2', 12, 2)->default(0); // Qty Pcs
            $table->timestamps();
        });

        // 8. Lock Transaction Periods
        Schema::create('lock_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->foreignId('warehouseid')->constrained('warehouses', 'warehouseid');
            $table->boolean('is_locked')->default(true);
            $table->foreignId('locked_by')->constrained('users');
            $table->timestamps();
            $table->unique(['year', 'month', 'warehouseid']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('lock_periods');
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transaction_headers');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('user_warehouse');
        Schema::dropIfExists('user_companies');
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_group_details');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('companies');
    }
};