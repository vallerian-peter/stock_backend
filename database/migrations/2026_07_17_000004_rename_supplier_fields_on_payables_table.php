<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->renameColumn('supplierName', 'creditorName');
            $table->renameColumn('supplierPhone', 'creditorPhone');
        });
    }

    public function down(): void
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->renameColumn('creditorName', 'supplierName');
            $table->renameColumn('creditorPhone', 'supplierPhone');
        });
    }
};
