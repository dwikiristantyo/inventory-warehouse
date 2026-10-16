<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('uom2', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('items')->whereNull('uom2')->update(['uom2' => '']);

        Schema::table('items', function (Blueprint $table) {
            $table->string('uom2', 20)->nullable(false)->change();
        });
    }
};