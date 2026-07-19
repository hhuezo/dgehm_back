<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\fixedasset\CategoryController;
use App\Http\Controllers\fixedasset\InstitutionController;
use App\Http\Controllers\fixedasset\OrganizationalUnitController;
use App\Http\Controllers\fixedasset\OrganizationalUnitTypeController;
use App\Http\Controllers\fixedasset\OriginController;
use App\Http\Controllers\fixedasset\PhysicalConditionController;
use App\Http\Controllers\fixedasset\SpecificController;
use App\Http\Controllers\fixedasset\FixedAssetController;
use App\Http\Controllers\fixedasset\DepreciationReportController;
use App\Http\Controllers\fixedasset\AssignmentController;
use App\Http\Controllers\fixedasset\TransferController;
use App\Http\Controllers\fixedasset\MovementController;

/*
|--------------------------------------------------------------------------
| Activos fijos
|--------------------------------------------------------------------------
*/

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/responsibles-options', [CategoryController::class, 'responsiblesOptions']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::get('/institutions', [InstitutionController::class, 'index']);
Route::post('/institutions', [InstitutionController::class, 'store']);
Route::put('/institutions/{id}', [InstitutionController::class, 'update']);
Route::delete('/institutions/{id}', [InstitutionController::class, 'destroy']);

Route::get('/organizational_unit_types', [OrganizationalUnitTypeController::class, 'index']);
Route::post('/organizational_unit_types', [OrganizationalUnitTypeController::class, 'store']);
Route::put('/organizational_unit_types/{id}', [OrganizationalUnitTypeController::class, 'update']);
Route::delete('/organizational_unit_types/{id}', [OrganizationalUnitTypeController::class, 'destroy']);

Route::get('/organizational_units', [OrganizationalUnitController::class, 'index']);
Route::post('/organizational_units', [OrganizationalUnitController::class, 'store']);
Route::put('/organizational_units/{id}', [OrganizationalUnitController::class, 'update']);
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

Route::get('/fixed_assets', [FixedAssetController::class, 'index']);
Route::get('/fixed_assets/depreciation-statuses', [FixedAssetController::class, 'depreciationStatuses']);
Route::post('/fixed_assets', [FixedAssetController::class, 'store']);
Route::put('/fixed_assets/{id}', [FixedAssetController::class, 'update']);
Route::delete('/fixed_assets/{id}', [FixedAssetController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::get('/assignments/assignable-persons', [AssignmentController::class, 'assignablePersons']);
    Route::get('/assignments/reports/{id}', [AssignmentController::class, 'report']);
    Route::get('/assignments/{id}/reception-act-file', [AssignmentController::class, 'downloadReceptionActFile']);
    Route::get('/assignments/{id}', [AssignmentController::class, 'show']);
    Route::post('/assignments', [AssignmentController::class, 'store']);
    Route::post('/assignments/{id}/execute', [AssignmentController::class, 'execute']);
    Route::post('/assignments/{id}/approve', [AssignmentController::class, 'approve']);
    Route::post('/assignments/{id}/reject', [AssignmentController::class, 'reject']);
    Route::post('/assignments/{id}/annul', [AssignmentController::class, 'annul']);
    Route::put('/assignments/{id}', [AssignmentController::class, 'update']);
    Route::post('/assignments/{id}', [AssignmentController::class, 'update']);
});
Route::get('/transfers', [TransferController::class, 'index']);
Route::get('/transfers/assignable-persons', [TransferController::class, 'assignablePersons']);
Route::get('/transfers/persons/{personId}/assigned-assets', [TransferController::class, 'assignedAssets']);
Route::get('/transfers/{id}/file', [TransferController::class, 'downloadFile']);
Route::get('/transfers/{id}', [TransferController::class, 'show']);
Route::post('/transfers', [TransferController::class, 'store']);
Route::post('/transfers/{id}/execute', [TransferController::class, 'execute']);
Route::post('/transfers/{id}/approve', [TransferController::class, 'approve']);
Route::post('/transfers/{id}/reject', [TransferController::class, 'reject']);
Route::put('/transfers/{id}', [TransferController::class, 'update']);
Route::post('/transfers/{id}', [TransferController::class, 'update']);

Route::get('/fixed_assets/{id}/movements', [MovementController::class, 'indexForAsset']);


Route::post('/fixed_assets/import', [FixedAssetController::class, 'import']);
Route::post('/fixed_assets/import/start', [FixedAssetController::class, 'importStart']);
Route::post('/fixed_assets/import/process', [FixedAssetController::class, 'importProcess']);
Route::get('/fixed_assets/import/progress/{jobId}', [FixedAssetController::class, 'importProgress']);

// Reporte de depreciación (parámetro: date; opcional: pdf=true para descargar PDF)
Route::post('/fixed_assets/reports/depreciation', [DepreciationReportController::class, 'report']);
Route::get('/fixed_assets/reports/depreciation', [DepreciationReportController::class, 'report']);
