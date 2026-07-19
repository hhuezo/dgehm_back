<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\general\ImageController;
use App\Http\Controllers\configuration\MailSettingsController;
use App\Http\Controllers\security\EmployeeController;
use App\Http\Controllers\security\PermissionController;
use App\Http\Controllers\security\PermissionTypeController;
use App\Http\Controllers\security\RoleController;
use App\Http\Controllers\security\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signout', [AuthController::class, 'signout']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    $user->loadMissing(['employee:id,user_id']);

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'employee_id' => $user->employee?->id,
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'organizational_units' => $user->organizationalUnits ?? [],
    ];
});

Route::get('general/images/{imgName}', [ImageController::class, 'getGeneralImage']);

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {});  // temporalmente

/*
|--------------------------------------------------------------------------
| Permisos
|--------------------------------------------------------------------------
*/
Route::get('/permission', [PermissionController::class, 'index']);
Route::post('/permission', [PermissionController::class, 'store']);
Route::put('/permission/{id}', [PermissionController::class, 'update']);
Route::delete('/permission/{id}', [PermissionController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Tipos de permiso
|--------------------------------------------------------------------------
*/
Route::get('/permission_type', [PermissionTypeController::class, 'index']);
Route::post('/permission_type', [PermissionTypeController::class, 'store']);
Route::get('/permission_type/{id}', [PermissionTypeController::class, 'show']);
Route::put('/permission_type/{id}', [PermissionTypeController::class, 'update']);
Route::delete('/permission_type/{id}', [PermissionTypeController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/
Route::get('/role', [RoleController::class, 'index']);
Route::post('/role', [RoleController::class, 'store']);
Route::put('/role/{id}', [RoleController::class, 'update']);
Route::get('/role/{id}', [RoleController::class, 'show']);
Route::post('/role/togglePermission', [RoleController::class, 'togglePermission']);

/*
|--------------------------------------------------------------------------
| Usuarios
|--------------------------------------------------------------------------
*/
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::middleware('auth:sanctum')->get('/users/me/organizational_units', [UserController::class, 'myOrganizationalUnits']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::post('/users/{id}/roles', [UserController::class, 'syncRoles']);
Route::post('/users/{id}/organizational_units', [UserController::class, 'syncOrganizationalUnits']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Empleados (adm_employees)
|--------------------------------------------------------------------------
*/
Route::get('/employees/form-options', [EmployeeController::class, 'formOptions']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/warehouse-managers', [EmployeeController::class, 'getWarehouseManagers']);
Route::get('/administrative_technicians', [EmployeeController::class, 'getWarehouseManagers']);
Route::post('/employees', [EmployeeController::class, 'store']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::put('/employees/{id}', [EmployeeController::class, 'update']);
Route::patch('/employees/{id}/warehouse-manager', [EmployeeController::class, 'updateWarehouseManager']);
Route::patch('/employees/{id}/fixed-asset-manager', [EmployeeController::class, 'updateFixedAssetManager']);
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

Route::get('/area-managers', [UserController::class, 'getAreaManagers']);

/*
|--------------------------------------------------------------------------
| Configuración de correo (adm_mail_settings)
|--------------------------------------------------------------------------
*/
Route::get('/mail_settings', [MailSettingsController::class, 'show']);
Route::put('/mail_settings', [MailSettingsController::class, 'update']);
Route::post('/mail_settings/test', [MailSettingsController::class, 'sendTest']);
