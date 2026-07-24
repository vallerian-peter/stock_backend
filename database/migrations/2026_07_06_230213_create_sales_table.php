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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('saleNumber')->nullable();
            $table->string('customerName')->nullable();
            $table->string('paymentStatus'); // 'PAID', 'PENDING', 'PARTIAL'
            $table->string('paymentMethod')->nullable(); // 'CASH', 'MOBILE_MONEY', 'BANK_TRANSFER'
            $table->decimal('totalAmount', 12, 2)->default(0);
            $table->decimal('amountPaid', 12, 2)->default(0);
            $table->foreignId('soldBy')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('soldAt');
            $table->foreignId('outgoingStockId')->nullable()->constrained('outgoing_stocks')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
