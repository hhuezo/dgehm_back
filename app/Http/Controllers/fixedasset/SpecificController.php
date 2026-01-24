<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\Specific;
use Illuminate\Http\Request;

class SpecificController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specifics = Specific::select('id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $specifics,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:fa_specifics,name',
            'code' => 'required|unique:fa_specifics,code',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
            'code.required' => 'El código es obligatorio.',
            'code.unique'   => 'Ya existe un registro con este código.',
        ];

        $data = $request->validate($rules, $messages);

        $specific = new Specific();
        $specific->name = $data['name'];
        $specific->code = $data['code'];
        $specific->is_active = true;
        $specific->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $specific,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => 'required|unique:fa_specifics,name,' . $id . ',id',
            'code' => 'required|unique:fa_specifics,code,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
            'code.required' => 'El código es obligatorio.',
            'code.unique'   => 'Ya existe un registro con este código.',
        ];

        $data = $request->validate($rules, $messages);

        $specific = Specific::findOrFail($id);
        $specific->name = $data['name'];
        $specific->code = $data['code'];
        $specific->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $specific,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $specific = Specific::findOrFail($id);
        $specific->is_active = false;
        $specific->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
    }
}
