<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::select('id', 'name', 'code')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
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
            'name' => 'required|unique:fa_categories,name',
            'code' => 'required|integer|unique:fa_categories,code',
        ];

        $messages = [
            'name.required' => 'El nombre de la categoria es obligatorio.',
            'name.unique'   => 'Ya existe una categoria contable con este nombre.',

            'code.required' => 'El código de la categoria es obligatorio.',
            'code.integer'  => 'El código de la categoria debe ser un número entero.',
            'code.unique'   => 'Ya existe una categoria contable con este código.',
        ];

        $data = $request->validate($rules, $messages);

        $category = new Category();
        $category->code = $request->code;
        $category->name = $request->name;
        $category->is_active = true;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Categoria creada correctamente.',
            'data' => $category,
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
            'name' => 'required|unique:fa_categories,name,' . $id,
            'code' => 'required|integer|unique:fa_categories,code,' . $id,
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una categoria con este nombre.',

            'code.required' => 'El código es obligatorio.',
            'code.integer'  => 'El código solo puede contener números.',
            'code.unique'   => 'Ya existe una categoria con este código.',
        ];

        $data = $request->validate($rules, $messages);
        $category = Category::findOrFail($id);

        $category->name = $request->name;
        $category->code = $request->code;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Categoria actualizada correctamente.',
            'data'    => $category,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = false;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Categoria deshabilitada correctamente',
        ], 200);
        //
    }
}
