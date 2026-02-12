<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('permissionType')->get();

        return response()->json([
            'success' => true,
            'data'    => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $guardName = $request->guard_name ?? 'web';

        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('permissions')->where(function ($query) use ($guardName) {
                        return $query->where('guard_name', $guardName);
                    })
                ],
                'guard_name'         => 'nullable|string|max:255',
                'permission_type_id' => 'nullable|exists:permission_types,id',
            ],
            [
                'name.required' => 'El nombre del permiso es obligatorio.',
                'name.unique'   => 'Este permiso ya existe para este guard.',
                'permission_type_id.exists' => 'El tipo de permiso no existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $permission = Permission::create([
            'name'                => $request->name,
            'guard_name'          => $guardName,
            'permission_type_id'  => $request->permission_type_id,
        ]);

        $permission->load('permissionType');

        return response()->json([
            'success' => true,
            'data'    => $permission,
            'message' => 'Permiso creado correctamente.',
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado.',
            ], 404);
        }

        $guardName = $request->guard_name ?? $permission->guard_name ?? 'web';

        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('permissions')->where(function ($query) use ($guardName) {
                        return $query->where('guard_name', $guardName);
                    })->ignore($permission->id)
                ],
                'guard_name'         => 'nullable|string|max:255',
                'permission_type_id' => 'nullable|exists:permission_types,id',
            ],
            [
                'name.required' => 'El nombre del permiso es obligatorio.',
                'name.unique'   => 'Este permiso ya existe para este guard.',
                'permission_type_id.exists' => 'El tipo de permiso no existe.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $permission->update([
            'name'                => $request->name,
            'guard_name'          => $guardName,
            'permission_type_id'  => $request->permission_type_id,
        ]);

        $permission->load('permissionType');

        return response()->json([
            'success' => true,
            'data'    => $permission,
            'message' => 'Permiso actualizado correctamente.',
        ]);
    }

    public function destroy(string $id)
    {

        $permission = Permission::find($id);

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
