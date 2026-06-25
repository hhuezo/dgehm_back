<?php

namespace App\Http\Controllers\warehouse;

use App\Exports\DeliveryReportExport;
use App\Exports\EnvironmentalSuppliesReportExport;
use App\Exports\LiquidationReportExport;
use App\Exports\StockReportExport;
use App\Http\Controllers\Controller;
use App\Models\warehouse\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    private function resolveFundingSourceId(Request $request): ?int
    {
        if (!$request->filled('wh_funding_sources_id')) {
            return null;
        }

        $request->validate([
            'wh_funding_sources_id' => 'integer|exists:wh_funding_sources,id',
        ]);

        return (int) $request->input('wh_funding_sources_id');
    }

    public function liquidationReport(Request $request)
    {
        $startDate   = $request->input('startDate');
        $endDate     = $request->input('endDate');
        $exportExcel = $request->boolean('exportExcel', false);
        $fundingSourceId = $this->resolveFundingSourceId($request);

        $accounts = Kardex::join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_accounting_accounts', 'wh_products.accounting_account_id', '=', 'wh_accounting_accounts.id')
            ->join('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->join('wh_purchase_order', 'wh_kardex.purchase_order_id', '=', 'wh_purchase_order.id')
            ->select(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code as account_code',
                'wh_accounting_accounts.name as account_name',
                DB::raw('ROUND(SUM(wh_kardex.subtotal), 2) as subtotal')
            )
            ->where('wh_kardex.movement_type', 2)
            ->whereBetween('wh_supply_request.delivery_date', [$startDate, $endDate])
            ->when($fundingSourceId, fn ($query) => $query->where('wh_purchase_order.wh_funding_sources_id', $fundingSourceId))
            ->groupBy(
                'wh_products.accounting_account_id',
                'wh_accounting_accounts.code',
                'wh_accounting_accounts.name'
            )
            ->get();

        $products = Kardex::join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->join('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->join('wh_purchase_order', 'wh_kardex.purchase_order_id', '=', 'wh_purchase_order.id')
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
            ->when($fundingSourceId, fn ($query) => $query->where('wh_purchase_order.wh_funding_sources_id', $fundingSourceId))
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
        $fundingSourceId = $this->resolveFundingSourceId($request);

        // Existencias a la fecha: solo entradas (reception_date <= fecha) y salidas (delivery_date <= fecha)
        $stock = DB::table('wh_kardex')
            ->join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_accounting_accounts', 'wh_products.accounting_account_id', '=', 'wh_accounting_accounts.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->leftJoin('wh_purchase_order', 'wh_kardex.purchase_order_id', '=', 'wh_purchase_order.id')
            ->leftJoin('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->when($fundingSourceId, fn ($query) => $query->where('wh_purchase_order.wh_funding_sources_id', $fundingSourceId))
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
        $fundingSourceId = $this->resolveFundingSourceId($request);

        // 1. Datos base (producto + oficina + cantidad)
        $data = DB::table('wh_kardex')
            ->join('wh_products', 'wh_kardex.product_id', '=', 'wh_products.id')
            ->join('wh_supply_request', 'wh_kardex.supply_request_id', '=', 'wh_supply_request.id')
            ->join('wh_purchase_order', 'wh_kardex.purchase_order_id', '=', 'wh_purchase_order.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->select(
                'wh_products.id as product_id',
                'wh_products.name as product_name',
                'wh_measures.name as measure_name',
                'wh_supply_request.fa_organizational_unit_id',
                DB::raw('SUM(wh_kardex.quantity) as quantity')
            )
            ->where('wh_kardex.movement_type', 2)
            ->whereBetween('wh_supply_request.delivery_date', [$startDate, $endDate])
            ->when($fundingSourceId, fn ($query) => $query->where('wh_purchase_order.wh_funding_sources_id', $fundingSourceId))
            ->groupBy(
                'wh_products.id',
                'wh_products.name',
                'wh_measures.name',
                'wh_supply_request.fa_organizational_unit_id'
            )
            ->orderBy('wh_products.name')
            ->get();

        // 2. Solo oficinas con entregas en el periodo (columnas)
        $deliveredUnitIds = $data->pluck('fa_organizational_unit_id')->unique()->filter()->values();

        $organizationalUnits = DB::table('fa_organizational_units')
            ->whereIn('id', $deliveredUnitIds)
            ->orderBy('name')
            ->get();

        // 3. Agrupar por producto (para la matriz)
        $products = $data->groupBy('product_id');

        /* =========================
            EXPORTAR EXCEL
            ========================== */
        if ($exportExcel) {
            return Excel::download(
                new DeliveryReportExport($products, $organizationalUnits, $startDate, $endDate),
                "Entrega_Productos_{$startDate}_{$endDate}.xlsx"
            );
        }

        $pdf = Pdf::loadView('reports.delivery', [
            'products'  => $products,
            'organizationalUnits' => $organizationalUnits,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ])
            ->setPaper('A4', 'landscape');

        return $pdf->download(
            "Entrega_Productos_{$startDate}_{$endDate}.pdf"
        );
    }

    public function environmentalSuppliesReport(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
        ]);

        $startDate   = $request->startDate;
        $endDate     = $request->endDate;
        $exportExcel = $request->boolean('exportExcel', false);
        $fundingSourceId = $this->resolveFundingSourceId($request);

        Carbon::setLocale('es');

        $rows = DB::table('wh_kardex as k')
            ->join('wh_products as p', 'k.product_id', '=', 'p.id')
            ->join('wh_supply_request as sr', 'k.supply_request_id', '=', 'sr.id')
            ->join('wh_supply_request_detail as srd', function ($join) {
                $join->on('srd.supply_request_id', '=', 'sr.id')
                    ->on('srd.product_id', '=', 'k.product_id');
            })
            ->join('fa_organizational_units as ou', 'sr.fa_organizational_unit_id', '=', 'ou.id')
            ->join('wh_accounting_accounts as aa', 'p.accounting_account_id', '=', 'aa.id')
            ->leftJoin('wh_measures as m', 'p.measure_id', '=', 'm.id')
            ->leftJoin('wh_purchase_order as po', 'k.purchase_order_id', '=', 'po.id')
            ->leftJoin('wh_funding_sources as fs', 'po.wh_funding_sources_id', '=', 'fs.id')
            ->select(
                'sr.delivery_date',
                'sr.id as request_number',
                'ou.name as organizational_unit_name',
                'aa.code as account_code',
                'aa.name as account_name',
                DB::raw("COALESCE(NULLIF(TRIM(p.description), ''), p.name) as product_detail"),
                'm.name as measure_name',
                'srd.quantity as requested_quantity',
                'srd.delivered_quantity',
                DB::raw('ROUND(SUM(k.subtotal) / NULLIF(SUM(k.quantity), 0), 2) as unit_price'),
                DB::raw('ROUND(SUM(k.subtotal), 2) as total'),
                DB::raw('MAX(fs.name) as funding_source_name')
            )
            ->where('k.movement_type', 2)
            ->where('p.environmental_report', true)
            ->whereNotNull('k.supply_request_id')
            ->whereNotNull('sr.delivery_date')
            ->whereBetween(DB::raw('DATE(sr.delivery_date)'), [$startDate, $endDate])
            ->when($fundingSourceId, fn ($query) => $query->where('po.wh_funding_sources_id', $fundingSourceId))
            ->groupBy(
                'sr.delivery_date',
                'sr.id',
                'ou.name',
                'aa.code',
                'aa.name',
                'p.id',
                'p.name',
                'p.description',
                'm.name',
                'srd.quantity',
                'srd.delivered_quantity'
            )
            ->orderBy('sr.delivery_date')
            ->orderBy('sr.id')
            ->orderBy('product_detail')
            ->get()
            ->map(function ($row) {
                $deliveryDate = Carbon::parse($row->delivery_date);

                $row->month_name = ucfirst($deliveryDate->translatedFormat('F'));
                $row->delivery_date_formatted = $deliveryDate->format('d/m/Y');
                $row->funding_source_name = $row->funding_source_name ?: '—';

                return $row;
            });

        $fileName = "Medio_Ambiente_Insumos_{$startDate}_{$endDate}";

        if ($exportExcel) {
            return Excel::download(
                new EnvironmentalSuppliesReportExport($rows, $startDate, $endDate),
                "{$fileName}.xlsx"
            );
        }

        $pdf = Pdf::loadView('reports.environmental_supplies', [
            'rows'      => $rows,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ])->setPaper('A4', 'landscape');

        return $pdf->download("{$fileName}.pdf");
    }
}
