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
        Schema::create('outgoing_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('dispatchNumber')->nullable();
            $table->string('recipientName')->nullable();
            $table->string('purpose'); // 'SALE', 'DAMAGED', 'RETURN'
            $table->foreignId('dispatchedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('dispatchedAt');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_stocks');
    }
};
