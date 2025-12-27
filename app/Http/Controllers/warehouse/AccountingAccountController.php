<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\AccountingAccount;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;

class AccountingAccountController extends Controller
{

    public function index()
    {
        $accounts = AccountingAccount::select('id', 'code', 'name')
            ->where('is_active', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
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
            'name' => 'required|unique:wh_accounting_accounts,name',
            'code' => 'required|integer|unique:wh_accounting_accounts,code',
        ]);

        $accounting = new AccountingAccount();
        $accounting->code = $request->code;
        $accounting->name = $request->name;
        $accounting->is_active = 1;
        $accounting->save();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta contable creada correctamente.',
            'data' => $accounting,
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
            'name' => 'required|unique:wh_accounting_accounts,name,' . $id,
            'code' => 'required|integer',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una cuenta contable con este nombre.',
            'code.required' => 'El código es obligatorio.',
            'code.integer'  => 'El código solo puede contener números.',
        ]);

        $accounting = AccountingAccount::findOrFail($id);

        $accounting->name = $request->name;
        $accounting->code = $request->code;
        $accounting->save();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta contable actualizada correctamente.',
            'data'    => $accounting,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accounting = AccountingAccount::findOrFail($id);
        $accounting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta contable eliminada correctamente',
        ], 200);
    }
}
