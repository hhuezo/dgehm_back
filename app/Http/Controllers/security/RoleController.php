<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role as ModelsRole;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::get();

        return response()->json([
            'success' => true,
            'data'    => $roles,
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
                    Rule::unique('roles')->where(function ($query) use ($guardName) {
                        return $query->where('guard_name', $guardName);
                    })
                ],
                'guard_name' => 'nullable|string|max:255',
            ],
            [
                'name.required' => 'El nombre del rol es obligatorio.',
                'name.unique'   => 'Este rol ya existe para este guard.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $role = ModelsRole::create([
            'name' => $request->name,
            'guard_name' => $guardName,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $role,
            'message' => 'Rol creado correctamente.',
        ], 201);
    }



    public function update(Request $request, string $id)
    {
        $role = ModelsRole::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado.',
            ], 404);
        }

        $guardName = $request->guard_name ?? $role->guard_name ?? 'web';

        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles')->where(function ($query) use ($guardName) {
                        return $query->where('guard_name', $guardName);
                    })->ignore($role->id)
                ],
                'guard_name' => 'nullable|string|max:255',
            ],
            [
                'name.required' => 'El nombre del rol es obligatorio.',
                'name.unique'   => 'Este rol ya existe para este guard.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $role->update([
            'name' => $request->name,
            'guard_name' => $guardName,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $role,
            'message' => 'Rol actualizado correctamente.',
        ]);
    }

    public function show(string $id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado.',
            ], 404);
        }

        // Un solo bloque: todos los permisos agrupados por tipo (para asignar al rol)
        $allPermissions = Permission::query()
            ->with('permissionType')
            ->orderBy('permission_type_id')
            ->orderBy('name')
            ->get();
        $grouped = $allPermissions->groupBy(fn ($p) => $p->permissionType?->name ?? 'Sin tipo');
        $role->all_permissions_by_section = $grouped->map(fn ($items) => $items->values()->all())->sortKeys()->all();

        // El otro bloque: solo los permisos asignados a este rol (lista plana)
        $role->setRelation('permissions', $role->permissions->makeHidden('pivot'));

        return response()->json([
            'success' => true,
            'data'    => $role,
            'message' => 'Rol encontrado correctamente.',
        ]);
    }

    public function togglePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'permission_id' => 'required|integer|exists:permissions,id',
        ]);

        $roleId = $request->input('role_id');
        $permissionId = $request->input('permission_id');

        try {
            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            $action = '';
            $message = '';

            // Nota: Se asume que en el modelo Role, has renombrado rolePermissions a permissions()
            if ($role->permissions->contains($permissionId)) {

                // Quitar (Detach)
                $role->permissions()->detach($permissionId);
                $action = 'revoked';
                $message = "Permiso '{$permission->name}' revocado del rol '{$role->name}'.";
            } else {

                // Agregar (Attach)
                $role->permissions()->attach($permissionId);
                $action = 'attached';
                $message = "Permiso '{$permission->name}' asignado al rol '{$role->name}'.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'role' => $role->name,
                'permission' => $permission->name,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El Rol o el Permiso especificado no existe.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al alternar el permiso: ' . $e->getMessage()
            ], 500);
        }
    }
}
