<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('category_user_group', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('usergroupid');
    $table->string('category_code');

    $table->foreign('usergroupid')->references('usergroupid')->on('user_groups')->onDelete('cascade');
    $table->foreign('category_code')->references('category_code')->on('categories')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_user');
    }
};
