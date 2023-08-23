<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserAuthController;
use App\Http\Middleware\UserVerifiedMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/user/register', [UserAuthController::class, 'register']); //
Route::post('/user/login', [UserAuthController::class, 'login']); //
Route::post('/user/forgot_password', [UserAuthController::class, 'forgot_password']); //
Route::post('/user/reset_password/{email}/{token}', [UserAuthController::class, 'reset_password']); //

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/user/logout', [UserAuthController::class, 'logout']); //
    Route::get('/user', [UserAuthController::class, 'getSelf']); //
    Route::put('/user', [UserAuthController::class, 'update']);
    Route::delete('/user', [UserAuthController::class, 'destroy']);

    Route::group(['middleware' => ['auth:sanctum', UserVerifiedMiddleware::class]], function () {
        Route::post('/user/profile', [ProfileController::class, 'store']);
        Route::get('/user/profile', [ProfileController::class, 'getSelf']);
        Route::get('/user/profile/{id}', [ProfileController::class, 'show']); // TODO: make so only can see if is a match
        Route::put('/user/profile', [ProfileController::class, 'update']);
    });
});
