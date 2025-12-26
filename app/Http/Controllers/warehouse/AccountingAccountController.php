<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\AccountingAccount;
use Illuminate\Http\Request;

class AccountingAccountController extends Controller
{

    public function index()
    {
        $acountings = AccountingAccount::select('id', 'code', 'name')->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data'    => $acountings,
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
        $validated = $request->validate([
            'code'        => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ]);
        try {
            $accounting = new AccountingAccount();
            $accounting->code = $validated['code'];
            $accounting->name = $validated['name'];
            $accounting->save();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta contable creada correctamente.',
                'data'    => $accounting,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cuenta contable',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
        $validated = $request->validate([
            'code'        => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ]);

        try {
            $accounting = AccountingAccount::find($id);

            if (!$accounting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cuenta contable no encontrada.',
                ], 404);
            }

            $accounting->code        = $validated['code'];
            $accounting->name = $validated['name'];
            $accounting->save();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta contable actualizada correctamente.',
                'data'    => $accounting,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cuenta contable',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
