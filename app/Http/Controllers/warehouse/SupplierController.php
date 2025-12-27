<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
         $suppliers = Supplier::get();
=======
        $suppliers = Supplier::select('id', 'name', 'contact_person', 'phone', 'email', 'address')
            ->where('is_active', true)
            ->get();
>>>>>>> c684374f505eaa6fb213ac3cf0c5e75073870cf1

        return response()->json([
            'success' => true,
            'data'    => $suppliers,
        ]);
<<<<<<< HEAD
=======
        //
>>>>>>> c684374f505eaa6fb213ac3cf0c5e75073870cf1
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
<<<<<<< HEAD
=======
        $request->validate([
            'name'           => 'required|unique:wh_suppliers,name',
            'contact_person' => 'required',
            'phone'          => 'required',
            'email'          => 'nullable|email',
            'address'        => 'nullable',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un proveedor con este nombre.',
            'phone.required' => 'El teléfono es obligatorio.',
            'email.email'   => 'El correo no es válido.',
        ]);

        $supplier = new Supplier();
        $supplier->name           = $request->name;
        $supplier->contact_person = $request->contact_person;
        $supplier->phone          = $request->phone;
        $supplier->email          = $request->email;
        $supplier->address        = $request->address;
        $supplier->is_active      = 1;
        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado correctamente.',
            'data'    => $supplier,
        ], 201);
>>>>>>> c684374f505eaa6fb213ac3cf0c5e75073870cf1
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
<<<<<<< HEAD
        //
=======
        $request->validate([
            'name'           => 'required|unique:wh_suppliers,name,' . $id,
            'contact_person' => 'required',
            'phone'          => 'required',
            'email'          => 'nullable|email',
            'address'        => 'nullable',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un proveedor con este nombre.',
            'phone.required' => 'El teléfono es obligatorio.',
            'email.email'   => 'El correo no es válido.',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->name           = $request->name;
        $supplier->contact_person = $request->contact_person;
        $supplier->phone          = $request->phone;
        $supplier->email          = $request->email;
        $supplier->address        = $request->address;
        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado correctamente.',
            'data'    => $supplier,
        ], 200);
>>>>>>> c684374f505eaa6fb213ac3cf0c5e75073870cf1
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
<<<<<<< HEAD
=======
        $supplier = Supplier::findOrFail($id);
        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente.',
        ], 200);
>>>>>>> c684374f505eaa6fb213ac3cf0c5e75073870cf1
        //
    }
}
