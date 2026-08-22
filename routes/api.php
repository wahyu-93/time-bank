<?php

use App\Http\Controllers\Api\ActivityApprovalController;
use App\Http\Controllers\Api\ActivityClaimController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildDashboardController;
use App\Http\Controllers\Api\PlaySessionController;
use App\Http\Controllers\Api\RfidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'family.child'])->group(function () {
    Route::get('/children/{child}/dashboard', [ChildDashboardController::class, 'show',]);
});

Route::post('/rfid/identify', [RfidController::class, 'identify',]);

Route::post('/children/{child}/activities/{activity}/claim', [ActivityClaimController::class, 'store']);

Route::middleware(['auth:sanctum', 'family.claim'])->group(function () {
    Route::post('/activity-claims/{claim}/approve', [ActivityApprovalController::class, 'approve']);
    Route::post('/activity-claims/{claim}/reject', [ActivityApprovalController::class, 'reject']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/play-sessions/{session}/start', [PlaySessionController::class, 'start']);
    Route::post('/play-sessions/{session}/finish', [PlaySessionController::class, 'finish']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});