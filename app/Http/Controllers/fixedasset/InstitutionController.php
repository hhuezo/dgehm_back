<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\Institution;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institutions = Institution::select('id', 'name', 'name_en')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $institutions,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:fa_institutions,name',
            'name_en' => 'required|unique:fa_institutions,name_en',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una institución con este nombre.',
            'name_en.required' => 'El nombre en inglés es obligatorio.',
            'name_en.unique'   => 'Ya existe una institución con este nombre en inglés.',
        ];

        $data = $request->validate($rules, $messages);

        $institution = new Institution();
        $institution->name = $data['name'];
        $institution->name_en = $data['name_en'];
        $institution->is_active = true;
        $institution->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $institution,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => 'required|unique:fa_institutions,name,' . $id . ',id',
            'name_en' => 'required|unique:fa_institutions,name_en,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una institución con este nombre.',
            'name_en.required' => 'El nombre en inglés es obligatorio.',
            'name_en.unique'   => 'Ya existe una institución con este nombre en inglés.',
        ];

        $data = $request->validate($rules, $messages);

        $institution = Institution::findOrFail($id);
        $institution->name = $data['name'];
        $institution->name_en = $data['name_en'];
        $institution->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $institution,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $institution = Institution::findOrFail($id);
        $institution->is_active = false;
        $institution->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
    }
}
