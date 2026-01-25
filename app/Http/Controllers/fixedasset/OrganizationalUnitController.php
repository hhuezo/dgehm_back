<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\OrganizationalUnit;
use Illuminate\Http\Request;

class OrganizationalUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = OrganizationalUnit::select(
            'id',
            'name',
            'abbreviation' , 'code',
            'fa_organizational_unit_type_id',
            'fa_organizational_unit_id'
        )
            ->with(['type:id,name', 'parent:id,name'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $units,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:fa_organizational_units,name',
            'abbreviation' => 'nullable|unique:fa_organizational_units,abbreviation',
            'code' => 'nullable|string|max:32',
            'fa_organizational_unit_type_id' => 'required|exists:fa_organizational_unit_types,id',
            'fa_organizational_unit_id' => 'nullable|exists:fa_organizational_units,id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe una unidad organizativa con este nombre.',
            'abbreviation.unique' => 'Ya existe una unidad organizativa con esta abreviación.',
            'code.max' => 'El código no puede superar 32 caracteres.',
            'fa_organizational_unit_type_id.required' => 'El tipo de unidad organizativa es obligatorio.',
            'fa_organizational_unit_type_id.exists' => 'El tipo de unidad organizativa no existe.',
            'fa_organizational_unit_id.exists' => 'La unidad organizativa padre no existe.',
        ];

        $data = $request->validate($rules, $messages);

        $unit = new OrganizationalUnit();
        $unit->name = $data['name'];
        $unit->abbreviation = $data['abbreviation'] ?? null;
        $unit->code = $data['code'] ?? null;
        $unit->fa_organizational_unit_type_id = $data['fa_organizational_unit_type_id'];
        $unit->fa_organizational_unit_id = $data['fa_organizational_unit_id'] ?? null;
        $unit->is_active = true;
        $unit->save();

        $unit->load(['type:id,name', 'parent:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $unit,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => 'required|unique:fa_organizational_units,name,' . $id . ',id',
            'abbreviation' => 'nullable|unique:fa_organizational_units,abbreviation,' . $id . ',id',
            'code' => 'nullable|string|max:32',
            'fa_organizational_unit_type_id' => 'required|exists:fa_organizational_unit_types,id',
            'fa_organizational_unit_id' => 'nullable|exists:fa_organizational_units,id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe una unidad organizativa con este nombre.',
            'abbreviation.unique' => 'Ya existe una unidad organizativa con esta abreviación.',
            'code.max' => 'El código no puede superar 32 caracteres.',
            'fa_organizational_unit_type_id.required' => 'El tipo de unidad organizativa es obligatorio.',
            'fa_organizational_unit_type_id.exists' => 'El tipo de unidad organizativa no existe.',
            'fa_organizational_unit_id.exists' => 'La unidad organizativa padre no existe.',
        ];

        $data = $request->validate($rules, $messages);

        $unit = OrganizationalUnit::findOrFail($id);
        $unit->name = $data['name'];
        $unit->abbreviation = $data['abbreviation'] ?? null;
        $unit->code = $data['code'] ?? null;
        $unit->fa_organizational_unit_type_id = $data['fa_organizational_unit_type_id'];
        $unit->fa_organizational_unit_id = $data['fa_organizational_unit_id'] ?? null;
        $unit->save();

        $unit->load(['type:id,name', 'parent:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $unit,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = OrganizationalUnit::findOrFail($id);
        $unit->is_active = false;
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
    }
}
