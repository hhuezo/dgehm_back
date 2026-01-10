<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\PhysicalCondition;
use Illuminate\Http\Request;

class PhysicalConditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conditions = PhysicalCondition::select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conditions,
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
            'name' => 'required|unique:fa_physical_conditions,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $condition = new PhysicalCondition();
        $condition->name = $request->name;
        $condition->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $condition,
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
            'name' => 'required|unique:fa_physical_conditions,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $condition = PhysicalCondition::findOrFail($id);
        $condition->name = $data['name'];
        $condition->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $condition,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $condition = PhysicalCondition::findOrFail($id);
        $condition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
        ], 200);
        //
    }
}
