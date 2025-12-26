<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Measure;
use Illuminate\Http\Request;

class MeasuresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $measures = Measure::select('id', 'name')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $measures,
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
            'name' => 'required|unique:wh_measures,name',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una medida con este nombre.',
        ]);
        $measure = new Measure();
        $measure->name = $request->name;
        $measure->is_active = 1;
        $measure->save();

        return response()->json([
            'success' => true,
            'message' => 'Medida creada correctamente.',
            'data' => $measure,
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
            'name' => 'required|unique:wh_measures,name,' . $id,
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una medida con este nombre.',
        ]);

        $measure = Measure::findOrFail($id);

        $measure->name = $request->name;
        $measure->save();

        return response()->json([
            'success' => true,
            'message' => 'Medida actualizada correctamente.',
            'data'    => $measure,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $measure = Measure::findOrFail($id);
        $measure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medida eliminada correctamente',
        ], 200);
        //
    }
}
