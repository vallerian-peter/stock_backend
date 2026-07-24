<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('theme')->default('system');
            $table->string('locale', 2)->default('en');
            $table->boolean('compactTables')->default(false);
            $table->boolean('lowStockAlerts')->default(true);
            $table->boolean('salesDigest')->default(true);
            $table->boolean('debtReminders')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
