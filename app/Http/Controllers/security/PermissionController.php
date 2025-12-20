<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission as ModelsPermission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::get();

        return response()->json([
            'success' => true,
            'data'    => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255|unique:permissions,name',
            ],
            [
                'name.required' => 'El nombre del permiso es obligatorio.',
                'name.unique'   => 'Este permiso ya existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $permission = ModelsPermission::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $permission,
            'message' => 'Permiso creado correctamente.',
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $permission = ModelsPermission::find($id);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado.',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            ],
            [
                'name.required' => 'El nombre del permiso es obligatorio.',
                'name.unique'   => 'Este permiso ya existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $permission->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $permission,
            'message' => 'Permiso actualizado correctamente.',
        ]);
    }

    public function destroy(string $id)
    {
        $permission = ModelsPermission::find($id);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado.',
            ], 404);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permiso eliminado correctamente.',
        ]);
    }
}
