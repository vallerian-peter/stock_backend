<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->string('referenceNumber')->unique();
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('category');
            $table->string('subject');
            $table->text('message');
            $table->string('priority')->default('normal');
            $table->string('contactPreference')->default('email');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('status')->default('submitted');
            $table->string('locale', 5)->default('en');
            $table->string('sourcePath')->nullable();
            $table->string('sheetSyncStatus')->default('pending');
            $table->timestamp('sheetSyncedAt')->nullable();
            $table->text('sheetSyncError')->nullable();
            $table->timestamps();

            $table->index(['userId', 'created_at']);
            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
