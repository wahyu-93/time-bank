<?php

use App\Http\Controllers\Api\ActivityApprovalController;
use App\Http\Controllers\Api\ActivityClaimController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildDashboardController;
use App\Http\Controllers\Api\PlaySessionController;
use App\Http\Controllers\Api\PrivilegeRequestController;
use App\Http\Controllers\Api\RfidController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

// Route::middleware(['auth:sanctum', 'family.child'])->group(function () {
//     Route::get('/children/{child}/dashboard', [ChildDashboardController::class, 'show',]);
// });

Route::get('/children/{child}/dashboard', [ChildDashboardController::class, 'show',]);

Route::post('/rfid/identify', [RfidController::class, 'identify',]);

Route::post('/children/{child}/activities/{activity}/claim', [ActivityClaimController::class, 'store']);

Route::get('/children/{child}/play-session', [PlaySessionController::class,'current']);

Route::get('/play-sessions/{session}', [PlaySessionController::class, 'show']);
Route::post('/play-sessions/{session}/start', [PlaySessionController::class, 'start']);
Route::post('/play-sessions/{session}/finish', [PlaySessionController::class, 'finish']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/activity-claims/pending', [ActivityClaimController::class, 'pending']);
});

Route::middleware(['auth:sanctum', 'family.claim'])->group(function () {
    Route::post('/activity-claims/{claim}/approve', [ActivityApprovalController::class, 'approve']);
    Route::post('/activity-claims/{claim}/reject', [ActivityApprovalController::class, 'reject']);
    Route::post('/activity-claims/{claim}/penalty', [ActivityApprovalController::class, 'penalty']);
});

Route::post('/children/{child}/privileges/{privilege}/request', [PrivilegeRequestController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/privilege-requests/pending', [PrivilegeRequestController::class, 'pending']);
    Route::post('/privilege-requests/{privilegeRequest}/approve', [PrivilegeRequestController::class, 'approve'])->middleware('family.privilege');
    Route::post('/privilege-requests/{privilegeRequest}/reject', [PrivilegeRequestController::class, 'reject'])->middleware('family.privilege');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});