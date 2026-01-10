<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\AdministrativeUnit;
use Illuminate\Http\Request;

class AdministrativeUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = AdministrativeUnit::select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $units,
        ], 200);
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:fa_administrative_units,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una unidad administrativa con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $unit = new AdministrativeUnit();
        $unit->name = $request->name;
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $unit,
        ], 201);
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => 'required|unique:fa_administrative_units,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una unidad administrativa con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $unit = AdministrativeUnit::findOrFail($id);
        $unit->name = $data['name'];
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $unit,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = AdministrativeUnit::findOrFail($id);
        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
        ], 200);
        //
    }
}
