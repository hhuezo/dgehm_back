<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\VehicleColor;
use Illuminate\Http\Request;

class VehicleColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicleColors = VehicleColor::select('id', 'name')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vehicleColors,
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
            'name' => 'required|unique:fa_vehicle_colors,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $vehicleColor = new VehicleColor();
        $vehicleColor->name = $request->name;
        $vehicleColor->is_active = true;
        $vehicleColor->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $vehicleColor,
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
            'name' => 'required|unique:fa_vehicle_colors,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $vehicleColor = VehicleColor::findOrFail($id);
        $vehicleColor->name = $data['name'];
        $vehicleColor->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $vehicleColor,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicleColor = VehicleColor::findOrFail($id);
        $vehicleColor->is_active = false;
        $vehicleColor->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
        //
    }
}
