<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\FixedAsset;
use App\Services\DepreciationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DepreciationReportController extends Controller
{
    /**
     * Reporte de depreciación a una fecha dada.
     * Lista activos con valor de compra >= 900, agrupados por específico,
     * con total por específico y total general.
     * Valor residual 10%; depreciación sobre 90% del valor en vida útil.
     */
    public function report(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $reportDate = $request->input('date');

        $assets = FixedAsset::with(['assetClass:id,fa_specific_id,code,name,useful_life', 'assetClass.specific:id,code,name'])
            ->where('purchase_value', '>=', DepreciationService::MIN_PURCHASE_VALUE)
            ->whereHas('assetClass', function ($q) {
                $q->whereNotNull('useful_life')->where('useful_life', '>', 0);
            })
            ->orderBy('fa_class_id')
            ->get();

        $rowsBySpecific = [];
        foreach ($assets as $asset) {
            $class = $asset->assetClass;
            if (!$class || !$class->specific) {
                continue;
            }
            $specific = $class->specific;
            $key = $specific->id;
            $purchaseValue = (float) $asset->purchase_value;
            $usefulLife = (int) $class->useful_life;
            $acquisitionDate = $asset->acquisition_date->format('Y-m-d');

            $residual = DepreciationService::residualValue($purchaseValue);
            $annualDepreciation = DepreciationService::annualDepreciation($purchaseValue, $usefulLife);
            $monthlyDepreciation = DepreciationService::monthlyDepreciation($purchaseValue, $usefulLife);
            $accumulated = DepreciationService::accumulatedDepreciation(
                $purchaseValue,
                $usefulLife,
                $acquisitionDate,
                $reportDate
            );
            $bookValue = DepreciationService::bookValueAt(
                $purchaseValue,
                $usefulLife,
                $acquisitionDate,
                $reportDate
            );
            $isFullyDepreciated = DepreciationService::isFullyDepreciated($usefulLife, $acquisitionDate, $reportDate);

            $row = [
                'asset' => $asset,
                'specific_code' => $specific->code,
                'specific_name' => $specific->name,
                'class_code' => $class->code,
                'class_name' => $class->name,
                'useful_life_years' => $usefulLife,
                'purchase_value' => $purchaseValue,
                'residual_value' => $residual,
                'annual_depreciation' => $annualDepreciation,
                'monthly_depreciation' => $monthlyDepreciation,
                'accumulated_depreciation' => $accumulated,
                'book_value' => $bookValue,
                'is_fully_depreciated' => $isFullyDepreciated,
            ];

            if (!isset($rowsBySpecific[$key])) {
                $rowsBySpecific[$key] = [
                    'specific' => $specific,
                    'rows' => [],
                    'subtotal_purchase' => 0,
                    'subtotal_book_value' => 0,
                ];
            }
            $rowsBySpecific[$key]['rows'][] = $row;
            $rowsBySpecific[$key]['subtotal_purchase'] += $purchaseValue;
            $rowsBySpecific[$key]['subtotal_book_value'] += $bookValue;
        }

        // Ordenar por código del específico y redondear subtotales
        foreach (array_keys($rowsBySpecific) as $key) {
            $rowsBySpecific[$key]['subtotal_purchase'] = round($rowsBySpecific[$key]['subtotal_purchase'], 2);
            $rowsBySpecific[$key]['subtotal_book_value'] = round($rowsBySpecific[$key]['subtotal_book_value'], 2);
        }
        uasort($rowsBySpecific, function ($a, $b) {
            return strcmp($a['specific']->code ?? '', $b['specific']->code ?? '');
        });

        $grandTotalPurchase = 0;
        $grandTotalBookValue = 0;
        foreach ($rowsBySpecific as $group) {
            $grandTotalPurchase += $group['subtotal_purchase'];
            $grandTotalBookValue += $group['subtotal_book_value'];
        }
        $grandTotalPurchase = round($grandTotalPurchase, 2);
        $grandTotalBookValue = round($grandTotalBookValue, 2);

        $pdf = Pdf::loadView('reports.depreciation', [
            'reportDate' => $reportDate,
            'rowsBySpecific' => $rowsBySpecific,
            'grandTotalPurchase' => $grandTotalPurchase,
            'grandTotalBookValue' => $grandTotalBookValue,
        ])
            ->setPaper('A4', 'landscape');

        return $pdf->download('Reporte_Depreciacion_' . $reportDate . '.pdf');
    }
}
