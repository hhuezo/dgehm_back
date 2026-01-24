<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\Origin;
use Illuminate\Http\Request;

class OriginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $origins = Origin::select('id', 'name')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $origins,
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
            'name' => 'required|unique:fa_origins,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $origin = new Origin();
        $origin->name = $request->name;
        $origin->is_active = true;
        $origin->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $origin,
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
            'name' => 'required|unique:fa_origins,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $origin = Origin::findOrFail($id);
        $origin->name = $data['name'];
        $origin->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $origin,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $origin = Origin::findOrFail($id);
        $origin->is_active = false;
        $origin->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
        //
    }
}
