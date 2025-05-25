<?php
use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('login', 'login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', 'logout');
            Route::post('logout-current', 'logoutCurrentToken');
            Route::get('me', 'me');
        });
    });
