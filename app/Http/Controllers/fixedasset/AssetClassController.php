<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\AssetClass;
use Illuminate\Http\Request;

class AssetClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = AssetClass::select('id', 'name')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:fa_classes,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una clase con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $assetClass = new AssetClass();
        $assetClass->name = $data['name'];
        $assetClass->is_active = true;
        $assetClass->save();

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
            'name' => 'required|unique:fa_classes,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una clase con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $assetClass = AssetClass::findOrFail($id);
        $assetClass->name = $data['name'];
        $assetClass->save();

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
