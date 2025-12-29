<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\general\ImageController;
use App\Http\Controllers\security\PermissionController;
use App\Http\Controllers\security\RoleController;
use App\Http\Controllers\warehouse\AccountingAccountController;
use App\Http\Controllers\warehouse\MeasuresController;
use App\Http\Controllers\warehouse\OfficeController;
use App\Http\Controllers\warehouse\ProductsController;
use App\Http\Controllers\warehouse\PurchaseOrderController;
use App\Http\Controllers\warehouse\PurchaseOrderDetailController;
use App\Http\Controllers\warehouse\SupplierController;
use App\Http\Controllers\warehouse\SupplyRequestController;
use App\Http\Controllers\warehouse\SupplyRequestDetailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/signout', [AuthController::class, 'signout']);

    // Permissions
    Route::get('/permission', [PermissionController::class, 'index']);
    Route::post('/permission', [PermissionController::class, 'store']);
    Route::put('/permission/{id}', [PermissionController::class, 'update']);
    Route::delete('/permission/{id}', [PermissionController::class, 'destroy']);

    // Roles
    Route::get('/role', [RoleController::class, 'index']);
    Route::post('/role', [RoleController::class, 'store']);
    Route::put('/role/{id}', [RoleController::class, 'update']);
    Route::get('/role/{id}', [RoleController::class, 'show']);
    Route::post('/role/togglePermission', [RoleController::class, 'togglePermission']);
});

/*
|--------------------------------------------------------------------------
| General
|--------------------------------------------------------------------------
*/
Route::get('general/images/{imgName}', [ImageController::class, 'getGeneralImage']);

/*
|--------------------------------------------------------------------------
| Purchase Orders
|--------------------------------------------------------------------------
*/
Route::get('purchase_order', [PurchaseOrderController::class, 'index']);
Route::post('purchase_order', [PurchaseOrderController::class, 'store']);
Route::get('purchase_order/{id}', [PurchaseOrderController::class, 'show']);
Route::put('purchase_order/{id}', [PurchaseOrderController::class, 'update']);
Route::get('purchase_order/acta/{id}', [PurchaseOrderController::class, 'reportActa']);

Route::get('purchase_order_detail/{id}', [PurchaseOrderDetailController::class, 'show']);
Route::post('purchase_order_detail', [PurchaseOrderDetailController::class, 'store']);
Route::put('purchase_order_detail/{id}', [PurchaseOrderDetailController::class, 'update']);
Route::delete('purchase_order_detail/{id}', [PurchaseOrderDetailController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Supply Requests
|--------------------------------------------------------------------------
*/
Route::get('supply_request', [SupplyRequestController::class, 'index']);
Route::post('supply_request', [SupplyRequestController::class, 'store']);
Route::get('supply_request/{id}', [SupplyRequestController::class, 'show']);
Route::post('supply_request/approve/{id}', [SupplyRequestController::class, 'approve']);
Route::post('supply_request/finalize/{id}', [SupplyRequestController::class, 'finalize']);

Route::get('product/{id}/{quantity}', [SupplyRequestController::class, 'resolveKardexStock']);

Route::get('supply_request_detail/{id}', [SupplyRequestDetailController::class, 'show']);
Route::post('supply_request_detail', [SupplyRequestDetailController::class, 'store']);
Route::put('supply_request_detail/{id}', [SupplyRequestDetailController::class, 'update']);
Route::delete('supply_request_detail/{id}', [SupplyRequestDetailController::class, 'destroy']);

Route::get('offices/{officeId}/bosses', [SupplyRequestController::class, 'getBoss']);

/*
|--------------------------------------------------------------------------
| Catalogs
|--------------------------------------------------------------------------
*/
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
