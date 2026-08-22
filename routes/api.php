<?php

use App\Http\Controllers\Api\ActivityClaimController;
use App\Http\Controllers\Api\ChildDashboardController;
use App\Http\Controllers\Api\RfidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/rfid/identify', [RfidController::class, 'identify',]);

Route::get('/children/{child}/dashboard', [ChildDashboardController::class, 'show',]);

Route::post('/children/{child}/activities/{activity}/claim', [ActivityClaimController::class, 'store']);