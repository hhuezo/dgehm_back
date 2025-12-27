<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::select('id', 'name', 'accounting_account_id')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $products,
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
            'name' => 'required|unique:wh_products,name',
            'accounting_account_id' => 'required|exists:wh_accounting_accounts,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe un producto con este nombre.',
            'accounting_account_id.required' => 'Debe seleccionar una cuenta contable.',
            'accounting_account_id.exists' => 'La cuenta contable seleccionada no existe.',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->accounting_account_id = $request->accounting_account_id;
        $product->is_active = 1;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente.',
            'data' => $product,
        ], 201);
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
            'name' => 'required|unique:wh_products,name,' . $id,
            'accounting_account_id' => 'required|exists:wh_accounting_accounts,id',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe un producto con este nombre.',
            'accounting_account_id.required' => 'Debe seleccionar una cuenta contable.',
            'accounting_account_id.exists' => 'La cuenta contable seleccionada no existe.',
        ]);

        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->accounting_account_id = $request->accounting_account_id;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente.',
            'data' => $product,
        ], 200);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente',
        ], 200);
        //
    }
}
