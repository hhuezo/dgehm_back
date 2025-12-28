<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\fixedasset\AccountingCategoryController;
use App\Http\Controllers\fixedasset\AdministrativeUnitController;
use App\Http\Controllers\fixedasset\CategoryController;
use App\Http\Controllers\fixedasset\OriginController;
use App\Http\Controllers\fixedasset\PhysicalConditionController;
use App\Http\Controllers\fixedasset\SubcategoryController;
use App\Http\Controllers\fixedasset\VehicleBrandController;
use App\Http\Controllers\fixedasset\VehicleClassController;
use App\Http\Controllers\fixedasset\VehicleColorController;
use App\Http\Controllers\fixedasset\VehicleDriveTypeController;
use App\Http\Controllers\fixedasset\VehicleTypeController;
use App\Http\Controllers\general\ImageController;
use App\Http\Controllers\security\PermissionController;
use App\Http\Controllers\security\RoleController;
use App\Http\Controllers\security\UserController;
use App\Http\Controllers\warehouse\AccountingAccountController;
use App\Http\Controllers\warehouse\PurchaseOrderController;
use App\Http\Controllers\warehouse\PurchaseOrderDetailController;
use App\Http\Controllers\warehouse\SupplyRequestController;
use App\Http\Controllers\warehouse\SupplyRequestDetailController;
use App\Http\Controllers\warehouse\MeasuresController;
use App\Http\Controllers\warehouse\OfficeController;
use App\Http\Controllers\warehouse\ProductsController;
use App\Http\Controllers\warehouse\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::get('general/images/{imgName}', [ImageController::class, 'getGeneralImage']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {});  //temporalmente
Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signout',  [AuthController::class, 'signout']);

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


Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::post('/users/{id}/roles', [UserController::class, 'syncRoles']);
Route::post('/users/{id}/offices', [UserController::class, 'syncOffices']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);


Route::get('/accounting_categories', [AccountingCategoryController::class, 'index']);
Route::post('/accounting_categories', [AccountingCategoryController::class, 'store']);
Route::put('/accounting_categories/{id}', [AccountingCategoryController::class, 'update']);
Route::delete('/accounting_categories/{id}', [AccountingCategoryController::class, 'destroy']);

Route::get('/administrative_units', [AdministrativeUnitController::class, 'index']);
Route::post('/administrative_units', [AdministrativeUnitController::class, 'store']);
Route::put('/administrative_units/{id}', [AdministrativeUnitController::class, 'update']);
Route::delete('/administrative_units/{id}', [AdministrativeUnitController::class, 'destroy']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::get('/origins', [OriginController::class, 'index']);
Route::post('/origins', [OriginController::class, 'store']);
Route::put('/origins/{id}', [OriginController::class, 'update']);
Route::delete('/origins/{id}', [OriginController::class, 'destroy']);

Route::get('/physical_conditions', [PhysicalConditionController::class, 'index']);
Route::post('/physical_conditions', [PhysicalConditionController::class, 'store']);
Route::put('/physical_conditions/{id}', [PhysicalConditionController::class, 'update']);
Route::delete('/physical_conditions/{id}', [PhysicalConditionController::class, 'destroy']);

Route::get('/subcategories', [SubcategoryController::class, 'index']);
Route::post('/subcategories', [SubcategoryController::class, 'store']);
Route::put('/subcategories/{id}', [SubcategoryController::class, 'update']);
Route::delete('/subcategories/{id}', [SubcategoryController::class, 'destroy']);

Route::get('/vehicle_brands', [VehicleBrandController::class, 'index']);
Route::post('/vehicle_brands', [VehicleBrandController::class, 'store']);
Route::put('/vehicle_brands/{id}', [VehicleBrandController::class, 'update']);
Route::delete('/vehicle_brands/{id}', [VehicleBrandController::class, 'destroy']);

Route::get('/vehicle_classes', [VehicleClassController::class, 'index']);
Route::post('/vehicle_classes', [VehicleClassController::class, 'store']);
Route::put('/vehicle_classes/{id}', [VehicleClassController::class, 'update']);
Route::delete('/vehicle_classes/{id}', [VehicleClassController::class, 'destroy']);

Route::get('/vehicle_colors', [VehicleColorController::class, 'index']);
Route::post('/vehicle_colors', [VehicleColorController::class, 'store']);
Route::put('/vehicle_colors/{id}', [VehicleColorController::class, 'update']);
Route::delete('/vehicle_colors/{id}', [VehicleColorController::class, 'destroy']);

Route::get('/vehicle_drive_types', [VehicleDriveTypeController::class, 'index']);
Route::post('/vehicle_drive_types', [VehicleDriveTypeController::class, 'store']);
Route::put('/vehicle_drive_types/{id}', [VehicleDriveTypeController::class, 'update']);
Route::delete('/vehicle_drive_types/{id}', [VehicleDriveTypeController::class, 'destroy']);

Route::get('/vehicle_types', [VehicleTypeController::class, 'index']);
Route::post('/vehicle_types', [VehicleTypeController::class, 'store']);
Route::put('/vehicle_types/{id}', [VehicleTypeController::class, 'update']);
Route::delete('/vehicle_types/{id}', [VehicleTypeController::class, 'destroy']);



Route::get('/administrative_technicians', [UserController::class, 'getAdministrativeTechnicians']);
Route::get('/area-managers', [UserController::class, 'getAreaManagers']);


Route::get('/role', [RoleController::class, 'index']);
Route::post('/role', [RoleController::class, 'store']);
Route::put('/role/{id}', [RoleController::class, 'update']);
Route::get('/role/{id}', [RoleController::class, 'show']);
Route::post('/role/togglePermission', [RoleController::class, 'togglePermission']);


Route::get('supplier', [SupplierController::class, 'index']);



Route::get('product', [ProductsController::class, 'index']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::post('/users/{id}/roles', [UserController::class, 'syncRoles']);
Route::post('/users/{id}/offices', [UserController::class, 'syncOffices']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::get('/accounting_account', [ProductsController::class, 'index']);


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
