<?php

use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OrderController;


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

// Order
Route::get('orders', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::get('orders/{id}', [OrderController::class, 'show']);
Route::put('orders/{id}', [OrderController::class, 'update']);
Route::delete('orders/{id}', [OrderController::class, 'destroy']);

