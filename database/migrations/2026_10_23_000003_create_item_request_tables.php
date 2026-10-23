<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_no')->unique();
            $table->string('requester_email');
            $table->string('requester_name');
            $table->string('department');
            $table->foreignId('warehouseid')->constrained('warehouses', 'warehouseid')->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('transaction_id')->nullable()->unique()->constrained('transaction_headers', 'trans_id')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('item_request_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_request_id')->constrained('item_requests')->cascadeOnDelete();
            $table->string('item_code');
            $table->foreign('item_code')->references('item_code')->on('items')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('uom', 20);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_request_details');
        Schema::dropIfExists('item_requests');
    }
};
