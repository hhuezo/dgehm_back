<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\security\PermissionController;
use App\Http\Controllers\warehouse\AccountingAccountController;
use App\Http\Controllers\warehouse\MeasuresController;
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


Route::get('/accounting_account', [AccountingAccountController::class, 'index']);
Route::post('/accounting_account', [PermissionController::class, 'store']);
Route::put('/permission/{id}', [PermissionController::class, 'update']);
Route::delete('/permission/{id}', [PermissionController::class, 'destroy']);

Route::get('/measures', [MeasuresController::class, 'index']);
Route::post('/measures', [MeasuresController::class, 'store']);
Route::put('/measures/{id}', [MeasuresController::class, 'update']);
Route::delete('/measures/{id}', [MeasuresController::class, 'destroy']);
