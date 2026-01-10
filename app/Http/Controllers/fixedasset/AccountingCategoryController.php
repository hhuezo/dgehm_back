<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\AccountingCategory;
use Illuminate\Http\Request;

class AccountingCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accountings = AccountingCategory::select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accountings,
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
            'name' => 'required|unique:fa_accounting_categories,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una categoría con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $accounting = new AccountingCategory();
        $accounting->name = $request->name;
        $accounting->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro creado correctamente.',
            'data' => $accounting,
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
            'name' => 'required|unique:fa_accounting_categories,name,' . $id . ',id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una categoría contable con este nombre.',
        ];

        $data = $request->validate($rules, $messages);

        $accounting = AccountingCategory::findOrFail($id);
        $accounting->name = $data['name'];
        $accounting->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
            'data' => $accounting,
        ], 200);


        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accounting = AccountingCategory::findOrFail($id);
        $accounting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente',
        ], 200);
        //
    }
}
