<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::select('id', 'name', 'phone')
            ->where('is_active', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offices,
        ]);
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
        $request->validate([
            'name'  => 'required|unique:wh_offices,name',
            'phone' => 'required|regex:/^[0-9]{4}-[0-9]{4}$/|unique:wh_offices,phone',
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.unique'    => 'Ya existe una oficina con este nombre.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex'    => 'El teléfono debe tener el formato 0000-0000.',
            'phone.unique'   => 'Ya existe una oficina con este teléfono.',
        ]);


        $office = new Office();
        $office->name = $request->name;
        $office->phone = $request->phone;
        $office->is_active = 1;
        $office->save();

        return response()->json([
            'success' => true,
            'message' => 'Oficina creada correctamente.',
            'data' => $office,
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
        $request->validate([
            'name' => 'required|unique:wh_offices,name,' . $id,
            'phone' => 'required|regex:/^[0-9]{4}-[0-9]{4}$/|unique:wh_offices,phone,' . $id,
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.unique'    => 'Ya existe una oficina con este nombre.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.regex'    => 'El teléfono debe tener el formato 0000-0000.',
            'phone.unique'   => 'Ya existe una oficina con este teléfono.',
        ]);

        $office = Office::findOrFail($id);

        $office->name = $request->name;
        $office->phone = $request->phone;
        $office->save();

        return response()->json([
            'success' => true,
            'message' => 'Oficina actualizada correctamente.',
            'data'    => $office,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $office = Office::findOrFail($id);
        $office->delete();

        return response()->json([
            'success' => true,
            'message' => 'Oficina eliminada correctamente',
        ], 200);
        //
    }
}
