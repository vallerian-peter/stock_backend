<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     public function up(): void
//     {
//         Schema::table('users', function (Blueprint $table): void {
//             $table->string('first_name')->after('id');
//             $table->string('last_name')->after('first_name');
//             $table->string('phone', 30)->nullable()->unique()->after('email');
//             $table->string('role')->default('staff')->after('phone');
//             $table->string('status')->default('active')->after('role');
//         });
//     }

//     public function down(): void
//     {
//         Schema::table('users', function (Blueprint $table): void {
//             $table->dropColumn([
//                 'first_name',
//                 'last_name',
//                 'phone',
//                 'role',
//                 'status',
//             ]);
//         });
//     }
// };
