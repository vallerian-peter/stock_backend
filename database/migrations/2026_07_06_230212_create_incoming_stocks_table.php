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
        Schema::create('incoming_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('invoiceNumber')->nullable();
            $table->string('supplierName')->nullable();
            $table->foreignId('receivedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('receivedAt');
            $table->decimal('totalAmount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_stocks');
    }
};
