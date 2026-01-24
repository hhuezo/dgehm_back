<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\OrganizationalUnitType;
use Illuminate\Http\Request;

class OrganizationalUnitTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = OrganizationalUnitType::select('id', 'name', 'staff')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:fa_organizational_unit_types,name',
            'staff' => 'boolean',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un tipo de unidad organizativa con este nombre.',
            'staff.boolean' => 'El campo staff debe ser verdadero o falso.',
        ];

        $data = $request->validate($rules, $messages);

        $type = new OrganizationalUnitType();
        $type->name = $data['name'];
        $type->staff = $request->boolean('staff', false);
        $type->is_active = true;
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $type,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => 'required|unique:fa_organizational_unit_types,name,' . $id . ',id',
            'staff' => 'boolean',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un tipo de unidad organizativa con este nombre.',
            'staff.boolean' => 'El campo staff debe ser verdadero o falso.',
        ];

        $data = $request->validate($rules, $messages);

        $type = OrganizationalUnitType::findOrFail($id);
        $type->name = $data['name'];
        $type->staff = $request->boolean('staff', false);
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $type,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $type = OrganizationalUnitType::findOrFail($id);
        $type->is_active = false;
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
    }
}
