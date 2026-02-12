<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\general\ImageController;
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
Route::get('/user', function (Request $request) {
    return $request->user();
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
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::post('/users/{id}/roles', [UserController::class, 'syncRoles']);
Route::post('/users/{id}/offices', [UserController::class, 'syncOffices']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

Route::get('/administrative_technicians', [UserController::class, 'getAdministrativeTechnicians']);
Route::get('/area-managers', [UserController::class, 'getAreaManagers']);
