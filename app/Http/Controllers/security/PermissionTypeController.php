<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionTypeController extends Controller
{
    public function index()
    {
        $types = PermissionType::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $types,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100|unique:permission_types,name',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'El nombre del tipo de permiso es obligatorio.',
            'name.unique'    => 'Ya existe un tipo de permiso con este nombre.',
        ]);

        $type = PermissionType::create([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $type,
            'message' => 'Tipo de permiso creado correctamente.',
        ], 201);
    }

    public function show(string $id)
    {
        $type = PermissionType::with('permissions')->find($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de permiso no encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $type,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $type = PermissionType::find($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de permiso no encontrado.',
            ], 404);
        }

        $request->validate([
            'name'      => ['required', 'string', 'max:100', Rule::unique('permission_types', 'name')->ignore($type->id)],
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'El nombre del tipo de permiso es obligatorio.',
            'name.unique'    => 'Ya existe un tipo de permiso con este nombre.',
        ]);

        $type->update([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $type,
            'message' => 'Tipo de permiso actualizado correctamente.',
        ]);
    }

    public function destroy(string $id)
    {
        $type = PermissionType::find($id);

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de permiso no encontrado.',
            ], 404);
        }

        if ($type->permissions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: hay permisos asociados a este tipo. Asigne otro tipo a esos permisos primero.',
            ], 422);
        }

        $type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de permiso eliminado correctamente.',
        ]);
    }
}
