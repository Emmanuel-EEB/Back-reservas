<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\ReservationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('courts', CourtController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('reservations', ReservationController::class);
    Route::put('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
});