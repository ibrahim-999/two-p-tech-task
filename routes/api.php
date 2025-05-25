<?php
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CartController;
use App\Http\Controllers\Api\v1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('login', 'login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', 'logout');
            Route::post('logout-current', 'logoutCurrentToken');
            Route::get('me', 'me');

            Route::controller(ProductController::class)->group(function () {
                Route::get('products',  'index');
                Route::get('products/{id}', 'show');
            });

            Route::apiResource('carts', CartController::class);

        });
    });
