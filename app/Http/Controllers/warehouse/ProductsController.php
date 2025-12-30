<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Kardex;
use App\Models\warehouse\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function existencia(string $id)
    {
        $productId = (int) $id;

        $remainingQuantitySql = 'SUM(CASE WHEN k.movement_type = 1 THEN k.quantity WHEN k.movement_type = 2 THEN -k.quantity ELSE 0 END)';
        $remainingValueSql = 'SUM(CASE WHEN k.movement_type = 1 THEN k.subtotal WHEN k.movement_type = 2 THEN -k.subtotal ELSE 0 END)';
        $unitCostSql = 'MAX(CASE WHEN k.movement_type = 1 THEN k.unit_price ELSE NULL END)';

        $inventoryByOrder = DB::table('wh_kardex', 'k')
            ->join('wh_purchase_order AS po', 'k.purchase_order_id', '=', 'po.id')

            ->select(
                'k.product_id',
                'po.order_number',
                DB::raw("{$remainingQuantitySql} AS remaining_quantity"),
                DB::raw("{$remainingValueSql} AS remaining_value"),
                DB::raw("{$unitCostSql} AS unit_cost_of_entry")
            )

            ->where('k.product_id', $productId)

            ->groupBy('k.product_id', 'po.order_number')

            ->havingRaw("{$remainingQuantitySql} > 0")

            ->orderBy('po.order_number')

            ->get();

        return response()->json([
            'success' => true,
            'data' => $inventoryByOrder,
            'product_id' => $productId,
        ]);
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


    public function kardex(Request $request, string $id)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');


        if (!$startDate || !$endDate) {
            return response()->json([
                'success' => false,
                'message' => 'Las fechas de inicio y fin son obligatorias para generar el Kardex.'
            ], 400);
        }

        try {
            $kardexMovements = Kardex::with('product')->with('purchaseOrder')->with('supplierRequest.office')->where('product_id', $id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                //->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $kardexMovements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la solicitud del Kardex: ' . $e->getMessage()
            ], 500);
        }
    }
}
