<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\general\ImageController;
use App\Http\Controllers\security\PermissionController;
use App\Http\Controllers\security\RoleController;
use App\Http\Controllers\warehouse\AccountingAccountController;
use App\Http\Controllers\warehouse\ProductController;
use App\Http\Controllers\warehouse\PurchaseOrderController;
use App\Http\Controllers\warehouse\PurchaseOrderDetailController;
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



    Route::post('/signout',  [AuthController::class, 'signout']);

    Route::get('/permission', [PermissionController::class, 'index']);
    Route::post('/permission', [PermissionController::class, 'store']);
    Route::put('/permission/{id}', [PermissionController::class, 'update']);
    Route::delete('/permission/{id}', [PermissionController::class, 'destroy']);

    Route::get('/role', [RoleController::class, 'index']);
    Route::post('/role', [RoleController::class, 'store']);
    Route::put('/role/{id}', [RoleController::class, 'update']);
    Route::get('/role/{id}', [RoleController::class, 'show']);
    Route::post('/role/togglePermission', [RoleController::class, 'togglePermission']);


    Route::get('supplier', [SupplierController::class, 'index']);



    Route::get('product', [ProductController::class, 'index']);
});


Route::get('/accounting_account', [ProductController::class, 'index']);


Route::get('general/images/{imgName}', [ImageController::class, 'getGeneralImage']);


Route::get('purchase_order', [PurchaseOrderController::class, 'index']);
Route::post('purchase_order', [PurchaseOrderController::class, 'store']);
Route::put('purchase_order/{id}', [PurchaseOrderController::class, 'update']);
Route::get('purchase_order/{id}', [PurchaseOrderController::class, 'show']);
Route::get('purchase_order/acta/{id}', [PurchaseOrderController::class, 'reportActa']);

Route::get('purchase_order_detail/{id}', [PurchaseOrderDetailController::class, 'show']);
Route::post('purchase_order_detail', [PurchaseOrderDetailController::class, 'store']);
Route::delete('purchase_order_detail/{id}', [PurchaseOrderDetailController::class, 'destroy']);
Route::put('purchase_order_detail/{id}', [PurchaseOrderDetailController::class, 'update']);

