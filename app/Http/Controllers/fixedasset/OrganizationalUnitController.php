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
     * Listado de unidades organizativas en estructura de árbol (padre → hijos).
     * GET /organizational_units/tree
     */
    public function indexTree()
    {
        $units = OrganizationalUnit::select(
            'id',
            'name',
            'abbreviation',
            'code',
            'fa_organizational_unit_type_id',
            'fa_organizational_unit_id'
        )
            ->with(['type:id,name', 'parent:id,name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $byParent = $units->groupBy('fa_organizational_unit_id');

        $buildTree = function ($parentId) use ($byParent, &$buildTree) {
            $children = $byParent->get($parentId, collect());
            return $children->map(function ($unit) use (&$buildTree) {
                $node = [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'abbreviation' => $unit->abbreviation,
                    'code' => $unit->code,
                    'fa_organizational_unit_id' => $unit->fa_organizational_unit_id,
                    'type' => $unit->type,
                    'parent' => $unit->parent,
                    'children' => $buildTree($unit->id),
                ];
                return $node;
            })->values()->all();
        };

        $tree = $buildTree(null);

        return response()->json([
            'success' => true,
            'data' => $tree,
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
     * Asigna o cambia la unidad padre (fa_organizational_unit_id).
     * PUT /organizational_units/{id}/parent
     * Body: { "fa_organizational_unit_id": 2 } o null para dejar sin padre.
     */
    public function assignParent(Request $request, string $id)
    {
        $request->validate([
            'fa_organizational_unit_id' => 'nullable|exists:fa_organizational_units,id',
        ], [
            'fa_organizational_unit_id.exists' => 'La unidad organizativa padre no existe.',
        ]);

        $unit = OrganizationalUnit::findOrFail($id);
        $parentId = $request->input('fa_organizational_unit_id') ? (int) $request->input('fa_organizational_unit_id') : null;

        if ($parentId !== null && (int) $id === $parentId) {
            return response()->json([
                'success' => false,
                'message' => 'La unidad no puede ser su propio padre.',
            ], 422);
        }

        if ($parentId !== null && $this->isDescendantOf($unit, $parentId)) {
            return response()->json([
                'success' => false,
                'message' => 'La unidad padre no puede ser una dependencia de la unidad actual (referencia circular).',
            ], 422);
        }

        $unit->fa_organizational_unit_id = $parentId;
        $unit->save();
        $unit->load(['type:id,name', 'parent:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Unidad padre asignada correctamente.',
            'data' => $unit,
        ], 200);
    }

    private function isDescendantOf(OrganizationalUnit $unit, int $possibleDescendantId): bool
    {
        if ($unit->id === $possibleDescendantId) {
            return true;
        }
        foreach ($unit->children as $child) {
            if ($this->isDescendantOf($child, $possibleDescendantId)) {
                return true;
            }
        }
        return false;
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
