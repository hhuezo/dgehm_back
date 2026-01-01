<?php

namespace App\Http\Controllers\warehouse;

use App\Exports\LiquidationReportExport;
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
            ->select(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code as account_code',
                'wh_accounting_accounts.name as account_name',
                DB::raw('ROUND(SUM(wh_kardex.subtotal), 2) as subtotal')
            )
            ->where('wh_kardex.movement_type', 2)
            ->whereBetween('wh_kardex.created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59'
            ])
            ->groupBy(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code',
                'wh_accounting_accounts.name'
            )
            ->get();

        $products = Kardex::join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
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
            ->whereBetween('wh_kardex.created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59'
            ])
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
        $fecha = $request->input('fecha');

        return response()->json(['message' => 'Stock report generated']);
    }
}
