<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PartController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {
    // require base_path('routes/api/v1.php');

    // Auth
    Route::post('/login', [AuthController::class, 'login'])->name('user.login');

    // protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');

        // users
        Route::get('/users', [UserController::class, 'index'])->name('users.get.all');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.get.all');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // parts
        Route::get('/parts', [PartController::class, 'index'])->name('parts.get.all');
        Route::post('/parts', [PartController::class, 'store'])->name('parts.store');
        Route::patch('/parts/{part}', [PartController::class, 'update'])->name('parts.update');
        Route::delete('/parts/{part}', [PartController::class, 'destroy'])->name('parts.destroy');
    });
});
