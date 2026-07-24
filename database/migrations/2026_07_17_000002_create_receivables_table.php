<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saleId')->nullable()->unique()->constrained('sales')->cascadeOnDelete();
            $table->string('customerName');
            $table->string('customerPhone')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->decimal('totalAmount', 12, 2);
            $table->decimal('amountPaid', 12, 2)->default(0);
            $table->decimal('balanceAmount', 12, 2);
            $table->string('status');
            $table->date('dueDate')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('createdBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
