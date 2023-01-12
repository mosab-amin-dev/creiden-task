<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\UserController;
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

Route::prefix('user')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('password-reset-request', [UserController::class, 'passwordResetRequest']);
        Route::post('check-password-reset-code', [UserController::class, 'checkPasswordResetCode']);
        Route::post('password-reset', [UserController::class, 'passwordReset']);
        Route::post('login', [UserController::class, 'login']);
        Route::post('register', [UserController::class, 'register']);
        Route::post('logout', [UserController::class, 'logout'])->middleware(['auth:user']);
    });
    Route::middleware(['auth:user'])->group(function () {
        Route::get('items/{item_id}',[ItemController::class,'user_show']);
        Route::get('items',[ItemController::class,'user_index']);
        Route::post('items',[ItemController::class,'user_store']);
        Route::put('items/{item_id}',[ItemController::class,'user_update']);
        Route::delete('items/{item_id}',[ItemController::class,'user_destroy']);

    });
});


Route::prefix('admin')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('password-reset-request', [AdminController::class, 'passwordResetRequest']);
        Route::post('check-password-reset-code', [AdminController::class, 'checkPasswordResetCode']);
        Route::post('password-reset', [AdminController::class, 'passwordReset']);
        Route::post('login', [AdminController::class, 'login']);
        Route::post('register', [AdminController::class, 'register']);
        Route::post('logout', [AdminController::class, 'logout'])->middleware(['auth:admin']);
    });
    Route::middleware(['auth:admin'])->group(function () {
        Route::apiResource('items',ItemController::class);
        Route::apiResource('storages',StorageController::class);
    });
});