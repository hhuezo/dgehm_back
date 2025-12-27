<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\security\PermissionController;
use App\Http\Controllers\warehouse\AccountingAccountController;
use App\Http\Controllers\warehouse\MeasuresController;
use App\Http\Controllers\warehouse\OfficeController;
use App\Http\Controllers\warehouse\ProductsController;
use App\Http\Controllers\warehouse\SupplierController;
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
Route::post('/accounting_account', [AccountingAccountController::class, 'store']);
Route::put('/accounting_account/{id}', [AccountingAccountController::class, 'update']);
Route::delete('/accounting_account/{id}', [AccountingAccountController::class, 'destroy']);

Route::get('/measures', [MeasuresController::class, 'index']);
Route::post('/measures', [MeasuresController::class, 'store']);
Route::put('/measures/{id}', [MeasuresController::class, 'update']);
Route::delete('/measures/{id}', [MeasuresController::class, 'destroy']);

Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::put('/products/{id}', [ProductsController::class, 'update']);
Route::delete('/products/{id}', [ProductsController::class, 'destroy']);

Route::get('/offices', [OfficeController::class, 'index']);
Route::post('/offices', [OfficeController::class, 'store']);
Route::put('/offices/{id}', [OfficeController::class, 'update']);
Route::delete('/offices/{id}', [OfficeController::class, 'destroy']);

Route::get('/suppliers', [SupplierController::class, 'index']);
Route::post('/suppliers', [SupplierController::class, 'store']);
Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);
