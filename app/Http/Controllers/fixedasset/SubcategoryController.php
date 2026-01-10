<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\SubCategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = SubCategory::select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories,
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
            'name' => 'required|unique:fa_subcategories,name',
            'code' => 'required|integer|unique:fa_subcategories,code',
        ];

        $messages = [
            'name.required' => 'El nombre  es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',

            'code.required' => 'El código es obligatorio.',
            'code.integer'  => 'El código debe ser un número entero.',
            'code.unique'   => 'Ya existe un registro con este código.',
        ];

        $data = $request->validate($rules, $messages);

        $subcategory = new SubCategory();
        $subcategory->code = $request->code;
        $subcategory->name = $request->name;
        $subcategory->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $subcategory,
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
            'name' => 'required|unique:fa_subcategories,name,' . $id,
            'code' => 'required|integer|unique:fa_subcategories,code,' . $id,
        ];

        $messages = [
            'name.required' => 'El nombre  es obligatorio.',
            'name.unique'   => 'Ya existe un registro con este nombre.',

            'code.required' => 'El código es obligatorio.',
            'code.integer'  => 'El código debe ser un número entero.',
            'code.unique'   => 'Ya existe un registro con este código.',
        ];

        $data = $request->validate($rules, $messages);
        $subcategory = SubCategory::findOrFail($id);

        $subcategory->name = $request->name;
        $subcategory->code = $request->code;
        $subcategory->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data'    => $subcategory,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subcategory = SubCategory::findOrFail($id);
        $subcategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
        ], 200);
        //
    }
}
