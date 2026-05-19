<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Cálculo de depreciación de activos fijos.
 * Valor residual 10%; se deprecia el 90% del valor del bien sobre la vida útil.
 * Si ya pasó el período de depreciación, el valor en libros es el 10% residual.
 */
class DepreciationService
{
    public const RESIDUAL_PERCENT = 10;

    public const MIN_PURCHASE_VALUE = 900.00;

    /**
     * Valor residual (10% del valor del bien).
     */
    public static function residualValue(float $purchaseValue): float
    {
        return round($purchaseValue * (self::RESIDUAL_PERCENT / 100), 2);
    }

    /**
     * Valor depreciable (90% del valor del bien).
     */
    public static function depreciableValue(float $purchaseValue): float
    {
        return round($purchaseValue * ((100 - self::RESIDUAL_PERCENT) / 100), 2);
    }

    /**
     * Depreciación anual = Valor depreciable / Vida útil (años).
     */
    public static function annualDepreciation(float $purchaseValue, int $usefulLifeYears): float
    {
        if ($usefulLifeYears <= 0) {
            return 0.0;
        }
        $depreciable = self::depreciableValue($purchaseValue);
        return round($depreciable / $usefulLifeYears, 2);
    }

    /**
     * Depreciación mensual = Depreciación anual / 12.
     */
    public static function monthlyDepreciation(float $purchaseValue, int $usefulLifeYears): float
    {
        $annual = self::annualDepreciation($purchaseValue, $usefulLifeYears);
        return round($annual / 12, 2);
    }

    /**
     * Meses transcurridos desde la fecha de adquisición hasta la fecha del reporte.
     * Carbon diffInMonths considera correctamente meses de distinta longitud (años bisiestos).
     */
    public static function monthsElapsed(string $acquisitionDate, string $reportDate): int
    {
        $start = Carbon::parse($acquisitionDate);
        $end = Carbon::parse($reportDate);
        if ($end->lt($start)) {
            return 0;
        }
        return (int) $start->diffInMonths($end);
    }

    /**
     * Total de meses del período de depreciación (vida útil en meses).
     */
    public static function depreciationPeriodMonths(int $usefulLifeYears): int
    {
        return $usefulLifeYears * 12;
    }

    /**
     * Depreciación acumulada hasta la fecha del reporte.
     * No supera el valor depreciable (90%).
     */
    public static function accumulatedDepreciation(
        float $purchaseValue,
        int $usefulLifeYears,
        string $acquisitionDate,
        string $reportDate
    ): float {
        $monthsElapsed = self::monthsElapsed($acquisitionDate, $reportDate);
        $totalMonths = self::depreciationPeriodMonths($usefulLifeYears);
        if ($monthsElapsed >= $totalMonths) {
            return self::depreciableValue($purchaseValue);
        }
        $monthly = self::monthlyDepreciation($purchaseValue, $usefulLifeYears);
        return round($monthly * $monthsElapsed, 2);
    }

    /**
     * Valor en libros a la fecha del reporte.
     * Si ya pasó la vida útil, es el valor residual (10%).
     */
    public static function bookValueAt(
        float $purchaseValue,
        int $usefulLifeYears,
        string $acquisitionDate,
        string $reportDate
    ): float {
        $accumulated = self::accumulatedDepreciation(
            $purchaseValue,
            $usefulLifeYears,
            $acquisitionDate,
            $reportDate
        );
        return round($purchaseValue - $accumulated, 2);
    }

    /**
     * Indica si el activo ya terminó su período de depreciación a la fecha del reporte.
     */
    public static function isFullyDepreciated(
        int $usefulLifeYears,
        string $acquisitionDate,
        string $reportDate
    ): bool {
        $monthsElapsed = self::monthsElapsed($acquisitionDate, $reportDate);
        $totalMonths = self::depreciationPeriodMonths($usefulLifeYears);
        return $monthsElapsed >= $totalMonths;
    }
}
