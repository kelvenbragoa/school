<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\RequestWorkflowController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Authentication routes (public)
Route::post('auth/login', [AuthController::class, 'login']);

// Stock System API Routes (authenticated)
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes (authenticated)
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/alerts', [DashboardController::class, 'alerts']);
    Route::get('dashboard/recent-requests', [DashboardController::class, 'recentRequests']);
    Route::get('dashboard/top-items', [DashboardController::class, 'topItems']);

    // Items
    Route::apiResource('items', ItemController::class);
    Route::get('items/search/autocomplete', [ItemController::class, 'search']);
    Route::get('items/alerts/low-stock', [ItemController::class, 'lowStock']);
    Route::get('items/{item}/movements', [ItemController::class, 'movements']);

    // Requests (Solicitações)
    Route::apiResource('requests', RequestController::class);
    Route::get('requests/my/list', [RequestController::class, 'my']);
    Route::get('requests/{request}/history', [RequestController::class, 'history']);

    // Request Workflow
    Route::post('requests/{id}/prepare', [RequestWorkflowController::class, 'prepare']);
    Route::post('requests/{id}/send', [RequestWorkflowController::class, 'send']);
    Route::post('requests/{id}/receive', [RequestWorkflowController::class, 'receive']);
    Route::post('requests/{id}/deliver', [RequestWorkflowController::class, 'deliver']);
    Route::post('requests/{id}/cancel', [RequestWorkflowController::class, 'cancel']);

    // Stock Movements
    Route::apiResource('stock/movements', StockMovementController::class)->except(['update', 'destroy']);
    Route::post('stock/movements/entry', [StockMovementController::class, 'entry']);
    Route::post('stock/movements/exit', [StockMovementController::class, 'exit']);

    // Inventories
    Route::apiResource('inventories', InventoryController::class);

    // Users
    Route::apiResource('users', UserController::class);
    Route::get('users/data/roles', [UserController::class, 'roles']);
});
