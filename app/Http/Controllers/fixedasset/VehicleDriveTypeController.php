<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\VehicleDriveType;
use Illuminate\Http\Request;

class VehicleDriveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $driveTypes = VehicleDriveType::select('id', 'name')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $driveTypes,
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
            'name' => 'required|unique:fa_vehicle_drive_types,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $driveType = new VehicleDriveType();
        $driveType->name = $request->name;
        $driveType->is_active = true;
        $driveType->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $driveType,
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
            'name' => 'required|unique:fa_vehicle_drive_types,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $driveType = VehicleDriveType::findOrFail($id);
        $driveType->name = $data['name'];
        $driveType->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $driveType,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $driveType = VehicleDriveType::findOrFail($id);
        $driveType->is_active = false;
        $driveType->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro deshabilitado correctamente',
        ], 200);
        //
    }
}
