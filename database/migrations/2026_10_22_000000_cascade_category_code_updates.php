<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_code']);
            $table->foreign('category_code')
                ->references('category_code')
                ->on('categories')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('category_user_group', function (Blueprint $table) {
            $table->dropForeign(['category_code']);
            $table->foreign('category_code')
                ->references('category_code')
                ->on('categories')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('category_user_group', function (Blueprint $table) {
            $table->dropForeign(['category_code']);
            $table->foreign('category_code')
                ->references('category_code')
                ->on('categories')
                ->cascadeOnDelete();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_code']);
            $table->foreign('category_code')
                ->references('category_code')
                ->on('categories')
                ->restrictOnDelete();
        });
    }
};
