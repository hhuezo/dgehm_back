<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Kardex;
use App\Models\warehouse\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index()
    {
        $data = Product::query()
            ->select('id', 'name', 'description', 'measure_id', 'accounting_account_id')
            ->with([
                'measure:id,name',
                'accountingAccount:id,name',
            ])
            ->orderBy('id')
            ->get();

        return response()
            ->json([
                'success' => true,
                'data'    => $data,
            ])
            ->header('X-Products-Index', 'wh-with-v1');
    }

    public function store(Request $request)
    {

        $rules = [
            'name'                  => 'required|unique:wh_products,name',
            'accounting_account_id' => 'required|exists:wh_accounting_accounts,id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un producto con este nombre.',

            'accounting_account_id.required' => 'Debe seleccionar una cuenta contable.',
            'accounting_account_id.exists'   => 'La cuenta contable seleccionada no existe.',
        ];

        $data = $request->validate($rules, $messages);

        $product = new Product();
        $product->name = $request->name;
        $product->accounting_account_id = $request->accounting_account_id;
        $product->measure_id = $request->measure_id;
        $product->description = $request->description;
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

    public function update(Request $request, string $id)
    {
        $rules = [
            'name'                  => 'required|unique:wh_products,name,' . $id,
            'accounting_account_id' => 'required|exists:wh_accounting_accounts,id',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe un producto con este nombre.',

            'accounting_account_id.required' => 'Debe seleccionar una cuenta contable.',
            'accounting_account_id.exists'   => 'La cuenta contable seleccionada no existe.',
        ];

        $data = $request->validate($rules, $messages);


        $product = Product::findOrFail($id);
        $product->name = $request->name;
        $product->accounting_account_id = $request->accounting_account_id;
        $product->measure_id = $request->measure_id;
        $product->description = $request->description;
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
                'message' => 'Las fechas de inicio y fin son obligatorias para generar el Kardex.',
            ], 400);
        }

        $tz = config('app.timezone', 'UTC');

        try {
            $start = Carbon::parse($startDate, $tz)->startOfDay();
            $end = Carbon::parse($endDate, $tz)->endOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD.',
            ], 422);
        }

        if ($start->greaterThan($end)) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha de inicio no puede ser posterior a la fecha fin.',
            ], 422);
        }

        try {
            // Fecha del movimiento: devolución → fecha solicitud → recepción OC → registro en kardex.
            // `date` entrecomillado: palabra reservada en MySQL.
            $movementAt = 'COALESCE(sret.return_date, sreq.`date`, po.reception_date, wh_kardex.created_at)';

            $kardexMovements = Kardex::query()
                ->select('wh_kardex.*')
                ->leftJoin('wh_purchase_order as po', 'wh_kardex.purchase_order_id', '=', 'po.id')
                ->leftJoin('wh_supply_request as sreq', 'wh_kardex.supply_request_id', '=', 'sreq.id')
                ->leftJoin('wh_supply_returns as sret', 'wh_kardex.supply_return_id', '=', 'sret.id')
                ->with(['product', 'purchaseOrder', 'supplierRequest.office', 'supplierReturn.office'])
                ->where('wh_kardex.product_id', (int) $id)
                ->whereRaw("{$movementAt} >= ?", [$start->format('Y-m-d H:i:s')])
                ->whereRaw("{$movementAt} <= ?", [$end->format('Y-m-d H:i:s')])
                ->orderByDesc('wh_kardex.id')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $kardexMovements,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la solicitud del Kardex: ' . $e->getMessage(),
            ], 500);
        }
    }
}
