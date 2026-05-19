<?php

namespace App\Http\Controllers\warehouse;

use App\Exports\DeliveryReportExport;
use App\Exports\LiquidationReportExport;
use App\Exports\StockReportExport;
use App\Http\Controllers\Controller;
use App\Models\warehouse\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function liquidationReport(Request $request)
    {
        $startDate   = $request->input('startDate');
        $endDate     = $request->input('endDate');
        $exportExcel = $request->boolean('exportExcel', false);

        $accounts = Kardex::join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_accounting_accounts', 'wh_products.accounting_account_id', '=', 'wh_accounting_accounts.id')
            ->join('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->select(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code as account_code',
                'wh_accounting_accounts.name as account_name',
                DB::raw('ROUND(SUM(wh_kardex.subtotal), 2) as subtotal')
            )
            ->where('wh_kardex.movement_type', 2)
            ->whereBetween('wh_supply_request.delivery_date', [$startDate, $endDate])
            ->groupBy(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code',
                'wh_accounting_accounts.name'
            )
            ->get();

        $products = Kardex::join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->join('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->select(
                'wh_products.accounting_account_id',
                'wh_products.id as product_id',
                'wh_products.name as product_name',
                'wh_measures.name as measure_name',
                DB::raw('SUM(wh_kardex.quantity) as quantity'),
                DB::raw('ROUND(wh_kardex.unit_price, 2) as unit_price'),
                DB::raw('ROUND(SUM(wh_kardex.subtotal), 2) as product_total')
            )
            ->where('wh_kardex.movement_type', 2)
            ->whereBetween('wh_supply_request.delivery_date', [$startDate, $endDate])
            ->groupBy(
                'wh_products.accounting_account_id',
                'wh_products.id',
                'wh_products.name',
                'wh_measures.name',
                'wh_kardex.unit_price'
            )
            ->orderBy('wh_products.id')
            ->get();



        if ($exportExcel) {
            return Excel::download(
                new LiquidationReportExport(
                    $accounts,
                    $products,
                    $startDate,
                    $endDate
                ),
                'Liquidacion_Inventario_' . $startDate . '_' . $endDate . '.xlsx'
            );
        }


        $pdf = Pdf::loadView('reports.liquidation', [
            'accounts'  => $accounts,
            'products'  => $products,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ])
            ->setPaper('A4', 'landscape');

        return $pdf->download(
            'Liquidacion_Inventario_' . $startDate . '_' . $endDate . '.pdf'
        );

        return view('reports.liquidation', compact(
            'accounts',
            'products',
            'startDate',
            'endDate'
        ));
    }


    public function stockReport(Request $request)
    {
        $date        = $request->input('date');
        $exportExcel = $request->boolean('exportExcel', false);

        // Existencias a la fecha: solo entradas (reception_date <= fecha) y salidas (delivery_date <= fecha)
        $stock = DB::table('wh_kardex')
            ->join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_accounting_accounts', 'wh_products.accounting_account_id', '=', 'wh_accounting_accounts.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->leftJoin('wh_purchase_order', 'wh_kardex.purchase_order_id', '=', 'wh_purchase_order.id')
            ->leftJoin('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->select(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code as account_code',
                'wh_accounting_accounts.name as account_name',
                'wh_products.id as product_id',
                'wh_products.name as product_name',
                'wh_measures.name as measure_name',
                DB::raw('ROUND(wh_kardex.unit_price, 2) as unit_price')
            )
            ->selectRaw("
                SUM(
                    CASE
                        WHEN wh_kardex.movement_type = 1
                             AND wh_purchase_order.reception_date IS NOT NULL
                             AND DATE(wh_purchase_order.reception_date) <= ?
                        THEN wh_kardex.quantity
                        ELSE 0
                    END
                )
                -
                SUM(
                    CASE
                        WHEN wh_kardex.movement_type = 2
                             AND wh_supply_request.delivery_date IS NOT NULL
                             AND DATE(wh_supply_request.delivery_date) <= ?
                        THEN wh_kardex.quantity
                        ELSE 0
                    END
                ) AS stock_quantity
            ", [$date, $date])
            ->groupBy(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code',
                'wh_accounting_accounts.name',
                'wh_products.id',
                'wh_products.name',
                'wh_measures.name',
                'wh_kardex.unit_price'
            )
            ->having('stock_quantity', '>', 0)
            ->orderBy('wh_accounting_accounts.code')
            ->orderBy('wh_products.name')
            ->orderBy('unit_price')
            ->get();

        /* =========================
            EXPORTAR EXCEL
            ========================== */
        if ($exportExcel) {
            return Excel::download(
                new StockReportExport($stock, $date),
                'Existencias_' . $date . '.xlsx'
            );
        }

        /* =========================
            EXPORTAR PDF (DEFAULT)
            ========================== */
        $pdf = Pdf::loadView('reports.stock', [
            'stock' => $stock,
            'date'  => $date,
        ])
            ->setPaper('A4', 'landscape');

        return $pdf->download(
            'Existencias_' . $date . '.pdf'
        );
    }

    public function deliveryReport(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date',
        ]);

        $startDate   = $request->startDate;
        $endDate     = $request->endDate;
        $exportExcel = $request->boolean('exportExcel', false);

        // 1. Oficinas (columnas)
        $offices = DB::table('wh_offices')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        // 2. Datos base (producto + oficina + cantidad)
        $data = DB::table('wh_kardex')
            ->join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->select(
                'wh_products.id as product_id',
                'wh_products.name as product_name',
                'wh_measures.name as measure_name',
                'wh_supply_request.office_id',
                DB::raw('SUM(wh_kardex.quantity) as quantity')
            )
            ->where('wh_kardex.movement_type', 2)
            ->whereBetween('wh_supply_request.delivery_date', [$startDate, $endDate])
            ->groupBy(
                'wh_products.id',
                'wh_products.name',
                'wh_measures.name',
                'wh_supply_request.office_id'
            )
            ->orderBy('wh_products.name')
            ->get();

        // 3. Agrupar por producto (para la matriz)
        $products = $data->groupBy('product_id');

        /* =========================
            EXPORTAR EXCEL
            ========================== */
        if ($exportExcel) {
            return Excel::download(
                new DeliveryReportExport($products, $offices, $startDate, $endDate),
                "Entrega_Productos_{$startDate}_{$endDate}.xlsx"
            );
        }

        $pdf = Pdf::loadView('reports.delivery', [
            'products'  => $products,
            'offices'   => $offices,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ])
            ->setPaper('A4', 'landscape');

        return $pdf->download(
            "Entrega_Productos_{$startDate}_{$endDate}.pdf"
        );
    }
}
