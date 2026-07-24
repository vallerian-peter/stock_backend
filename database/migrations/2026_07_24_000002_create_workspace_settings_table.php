<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('default');
            $table->unsignedInteger('lowStockThreshold')->default(10);
            $table->boolean('allowNegativeStock')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_settings');
    }
};
