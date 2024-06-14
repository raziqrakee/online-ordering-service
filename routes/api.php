<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\CateringServiceController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\SalesReportController;

// User
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::put('user/{id}', [UserController::class, 'update']);
Route::delete('user/{id}/remove-image', [UserController::class, 'removeImage']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});

// Product
Route::get('products', [ProductController::class, 'p_index']);
Route::post('products', [ProductController::class, 'p_store']);
Route::post('product', [ProductController::class, 'p_insert']);
Route::get('products/{id}', [ProductController::class, 'p_show']);
Route::get('products/{id}/edit', [ProductController::class, 'p_edit']);
Route::post('product/{id}', [ProductController::class, 'product_update']);
// Route::put('products/{id}/edit', [ProductController::class, 'p_update']);
Route::delete('products/{id}/delete', [ProductController::class, 'p_destroy']);
Route::post('products/{id}/purchase', [ProductController::class, 'p_purchase']);

// Catering Service
// Route::middleware('auth:sanctum')->post('/catering-service', [CateringServiceController::class, 'submitForm']);
Route::post('/catering-service', [CateringServiceController::class, 'submitForm']);

// Reservation
Route::apiResource('reservations', ReservationController::class);
Route::get('available-slots/{date}', [ReservationController::class, 'availableSlots']);

// Order
Route::middleware('auth:sanctum')->group(function () {
    Route::post('orders', [OrderController::class, 'placeOrder']);
    Route::put('orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
    Route::get('orders/{id}', [OrderController::class, 'viewOrder']);
    Route::get('orders', [OrderController::class, 'listOrders']);
    Route::get('orders/{id}/details', [OrderController::class, 'viewOrderDetails']);
    Route::get('orders/latest', [OrderController::class, 'getLatestOrderId']);
});

// Sales Report
Route::get('sales-reports', [SalesReportController::class, 'index']);

