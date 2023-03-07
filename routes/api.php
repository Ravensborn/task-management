<?php

use App\Http\Controllers\API\AuthController;

use App\Http\Controllers\API\UserController;
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

Route::prefix('v1')->group(function () {


    Route::post('/token/create', [AuthController::class, 'issue_token']);

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::apiResource('users', UserController::class);


    });
});
