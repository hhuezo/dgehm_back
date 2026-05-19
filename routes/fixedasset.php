<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\fixedasset\AssetClassController;
use App\Http\Controllers\fixedasset\CategoryController;
use App\Http\Controllers\fixedasset\InstitutionController;
use App\Http\Controllers\fixedasset\OrganizationalUnitController;
use App\Http\Controllers\fixedasset\OrganizationalUnitTypeController;
use App\Http\Controllers\fixedasset\OriginController;
use App\Http\Controllers\fixedasset\PhysicalConditionController;
use App\Http\Controllers\fixedasset\SpecificController;
use App\Http\Controllers\fixedasset\VehicleBrandController;
use App\Http\Controllers\fixedasset\VehicleColorController;
use App\Http\Controllers\fixedasset\VehicleDriveTypeController;
use App\Http\Controllers\fixedasset\VehicleTypeController;
use App\Http\Controllers\fixedasset\FixedAssetController;
use App\Http\Controllers\fixedasset\DepreciationReportController;

/*
|--------------------------------------------------------------------------
| Activos fijos
|--------------------------------------------------------------------------
*/

Route::get('/classes', [AssetClassController::class, 'index']);
Route::get('/classes/{id}', [AssetClassController::class, 'show']);
Route::post('/classes', [AssetClassController::class, 'store']);
Route::put('/classes/{id}', [AssetClassController::class, 'update']);
Route::delete('/classes/{id}', [AssetClassController::class, 'destroy']);

Route::get('/institutions', [InstitutionController::class, 'index']);
Route::post('/institutions', [InstitutionController::class, 'store']);
Route::put('/institutions/{id}', [InstitutionController::class, 'update']);
Route::delete('/institutions/{id}', [InstitutionController::class, 'destroy']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::get('/organizational_unit_types', [OrganizationalUnitTypeController::class, 'index']);
Route::post('/organizational_unit_types', [OrganizationalUnitTypeController::class, 'store']);
Route::put('/organizational_unit_types/{id}', [OrganizationalUnitTypeController::class, 'update']);
Route::delete('/organizational_unit_types/{id}', [OrganizationalUnitTypeController::class, 'destroy']);

Route::get('/organizational_units', [OrganizationalUnitController::class, 'index']);
Route::get('/organizational_units/tree', [OrganizationalUnitController::class, 'indexTree']);
Route::post('/organizational_units', [OrganizationalUnitController::class, 'store']);
Route::put('/organizational_units/{id}', [OrganizationalUnitController::class, 'update']);
Route::put('/organizational_units/{id}/parent', [OrganizationalUnitController::class, 'assignParent']);
Route::delete('/organizational_units/{id}', [OrganizationalUnitController::class, 'destroy']);

Route::get('/origins', [OriginController::class, 'index']);
Route::post('/origins', [OriginController::class, 'store']);
Route::put('/origins/{id}', [OriginController::class, 'update']);
Route::delete('/origins/{id}', [OriginController::class, 'destroy']);

Route::get('/physical_conditions', [PhysicalConditionController::class, 'index']);
Route::post('/physical_conditions', [PhysicalConditionController::class, 'store']);
Route::put('/physical_conditions/{id}', [PhysicalConditionController::class, 'update']);
Route::delete('/physical_conditions/{id}', [PhysicalConditionController::class, 'destroy']);

Route::get('/specifics', [SpecificController::class, 'index']);
Route::post('/specifics', [SpecificController::class, 'store']);
Route::put('/specifics/{id}', [SpecificController::class, 'update']);
Route::delete('/specifics/{id}', [SpecificController::class, 'destroy']);

Route::get('/vehicle_brands', [VehicleBrandController::class, 'index']);
Route::post('/vehicle_brands', [VehicleBrandController::class, 'store']);
Route::put('/vehicle_brands/{id}', [VehicleBrandController::class, 'update']);
Route::delete('/vehicle_brands/{id}', [VehicleBrandController::class, 'destroy']);

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

Route::get('/fixed_assets', [FixedAssetController::class, 'index']);
Route::post('/fixed_assets', [FixedAssetController::class, 'store']);
Route::put('/fixed_assets/{id}', [FixedAssetController::class, 'update']);
Route::delete('/fixed_assets/{id}', [FixedAssetController::class, 'destroy']);


Route::post('/fixed_assets/import', [FixedAssetController::class, 'import']);

// Reporte de depreciación (parámetro: date; opcional: pdf=true para descargar PDF)
Route::post('/fixed_assets/reports/depreciation', [DepreciationReportController::class, 'report']);
Route::get('/fixed_assets/reports/depreciation', [DepreciationReportController::class, 'report']);
