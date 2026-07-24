<?php

use App\Http\Controllers\Api\V1\AlertNotificationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardSummaryController;
use App\Http\Controllers\Api\V1\IncomingStockController;
use App\Http\Controllers\Api\V1\OutgoingStockController;
use App\Http\Controllers\Api\V1\PartController;
use App\Http\Controllers\Api\V1\PayableController;
use App\Http\Controllers\Api\V1\ReceivableController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // require base_path('routes/api/v1.php');

    // Auth
    Route::post('/login', [AuthController::class, 'login'])->name('user.login');

    // protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');
        Route::get('/dashboard/summary', DashboardSummaryController::class);

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

        // incoming stock
        Route::apiResource('incoming-stocks', IncomingStockController::class)->only(['index', 'store', 'destroy']);

        // outgoing stock
        Route::apiResource('outgoing-stocks', OutgoingStockController::class)->only(['index', 'store', 'destroy']);

        // sales
        Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'destroy']);

        // debts
        Route::apiResource('payables', PayableController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('receivables', ReceivableController::class)->only(['index', 'store', 'destroy']);

        // notifications
        Route::get('/notifications', [AlertNotificationController::class, 'index']);
        Route::post('/notifications/read-all', [AlertNotificationController::class, 'readAll']);
        Route::delete('/notifications', [AlertNotificationController::class, 'destroyAll']);
        Route::post('/notifications/{notification}/read', [AlertNotificationController::class, 'read']);
        Route::delete('/notifications/{notification}', [AlertNotificationController::class, 'destroy']);
    });
});
