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
        Schema::create('outgoing_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outgoingStockId')->constrained('outgoing_stocks')->cascadeOnDelete();
            $table->foreignId('partId')->constrained('parts');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_stock_items');
    }
};
