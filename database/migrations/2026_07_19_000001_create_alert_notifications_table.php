<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete();
            $table->string('sourceKey');
            $table->string('type');
            $table->string('severity');
            $table->string('redirectTo');
            $table->json('details');
            $table->timestamp('readAt')->nullable();
            $table->timestamp('dismissedAt')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['userId', 'sourceKey']);
            $table->index(['userId', 'active', 'dismissedAt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_notifications');
    }
};
