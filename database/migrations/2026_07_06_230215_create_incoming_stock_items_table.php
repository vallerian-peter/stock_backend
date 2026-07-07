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
        Schema::create('incoming_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incomingStockId')->constrained('incoming_stocks')->cascadeOnDelete();
            $table->foreignId('partId')->constrained('parts');
            $table->unsignedInteger('quantity');
            $table->decimal('unitCost', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_stock_items');
    }
};
