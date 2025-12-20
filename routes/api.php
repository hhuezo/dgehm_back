<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\security\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login SIN token
Route::post('/login', [AuthController::class, 'login']);

// Rutas PROTEGIDAS
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/permission', [PermissionController::class, 'index']);
    Route::post('/permission', [PermissionController::class, 'store']);
    Route::put('/permission/{id}', [PermissionController::class, 'update']);
    Route::delete('/permission/{id}', [PermissionController::class, 'destroy']);
});
