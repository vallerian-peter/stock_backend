<?php

use App\Enums\PartStatus;
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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('imageUrl')->nullable();
            $table->unsignedBigInteger('imageLastModifiedAt')->nullable();
            $table->string('partName');
            $table->string('partNumber')->unique();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->foreignId('categoryId')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status')->default(PartStatus::IN_STOCK->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
