<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\AssetClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = AssetClass::select('id', 'name', 'code', 'useful_life', 'fa_specific_id')
            ->with('specific:id,code,name')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assetClass = AssetClass::select('id', 'name', 'code', 'useful_life', 'fa_specific_id')
            ->with('specific:id,code,name')
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $assetClass,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                Rule::unique('fa_classes')->where('fa_specific_id', $request->fa_specific_id),
            ],
            'code' => [
                'required',
                Rule::unique('fa_classes')->where('fa_specific_id', $request->fa_specific_id),
            ],
            'useful_life' => 'nullable|integer|min:0',
            'fa_specific_id' => 'required|exists:fa_specifics,id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una clase con este nombre en el específico seleccionado.',
            'code.required' => 'El código es obligatorio.',
            'code.unique'   => 'Ya existe una clase con este código en el específico seleccionado.',
            'useful_life.integer' => 'La vida útil debe ser un número entero.',
            'useful_life.min' => 'La vida útil no puede ser negativa.',
            'fa_specific_id.required' => 'El específico es obligatorio.',
            'fa_specific_id.exists'   => 'El específico no existe.',
        ];

        $data = $request->validate($rules, $messages);

        $assetClass = new AssetClass();
        $assetClass->name = $data['name'];
        $assetClass->code = $data['code'];
        $assetClass->useful_life = $data['useful_life'] ?? null;
        $assetClass->fa_specific_id = $data['fa_specific_id'];
        $assetClass->is_active = true;
        $assetClass->save();

        $assetClass->load('specific:id,code,name');

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $assetClass,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => [
                'required',
                Rule::unique('fa_classes')->where('fa_specific_id', $request->fa_specific_id)->ignore($id),
            ],
            'code' => [
                'required',
                Rule::unique('fa_classes')->where('fa_specific_id', $request->fa_specific_id)->ignore($id),
            ],
            'useful_life' => 'nullable|integer|min:0',
            'fa_specific_id' => 'required|exists:fa_specifics,id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una clase con este nombre en el específico seleccionado.',
            'code.required' => 'El código es obligatorio.',
            'code.unique'   => 'Ya existe una clase con este código en el específico seleccionado.',
            'useful_life.integer' => 'La vida útil debe ser un número entero.',
            'useful_life.min' => 'La vida útil no puede ser negativa.',
            'fa_specific_id.required' => 'El específico es obligatorio.',
            'fa_specific_id.exists'   => 'El específico no existe.',
        ];

        $data = $request->validate($rules, $messages);

        $assetClass = AssetClass::findOrFail($id);
        $assetClass->name = $data['name'];
        $assetClass->code = $data['code'];
        $assetClass->useful_life = $data['useful_life'] ?? null;
        $assetClass->fa_specific_id = $data['fa_specific_id'];
        $assetClass->save();

        $assetClass->load('specific:id,code,name');

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $assetClass,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $assetClass = AssetClass::findOrFail($id);
        $assetClass->is_active = false;
        $assetClass->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
    }
}
