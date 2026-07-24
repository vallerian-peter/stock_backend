<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->date('debtDate')->nullable()->after('status');
        });

        Schema::table('receivables', function (Blueprint $table) {
            $table->date('debtDate')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->dropColumn('debtDate');
        });

        Schema::table('payables', function (Blueprint $table) {
            $table->dropColumn('debtDate');
        });
    }
};
