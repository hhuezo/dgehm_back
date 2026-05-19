<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KardexStockService
{
    private const REMAINING_QUANTITY_SQL = 'SUM(CASE WHEN k.movement_type = 1 THEN k.quantity WHEN k.movement_type = 2 THEN -k.quantity ELSE 0 END)';

    /**
     * Existencia total disponible de un producto en kardex.
     */
    public function getAvailableQuantity(int $productId): float
    {
        $value = DB::table('wh_kardex as k')
            ->where('k.product_id', $productId)
            ->selectRaw(self::REMAINING_QUANTITY_SQL . ' as available_quantity')
            ->value('available_quantity');

        return max(0, (float) ($value ?? 0));
    }

    /**
     * @param  array<int>  $productIds
     * @return array<int, float> product_id => available_quantity
     */
    public function getAvailableQuantitiesForProducts(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        if ($productIds === []) {
            return [];
        }

        $rows = DB::table('wh_kardex as k')
            ->whereIn('k.product_id', $productIds)
            ->groupBy('k.product_id')
            ->select('k.product_id', DB::raw(self::REMAINING_QUANTITY_SQL . ' as available_quantity'))
            ->get();

        $map = [];
        foreach ($productIds as $id) {
            $map[$id] = 0.0;
        }
        foreach ($rows as $row) {
            $map[(int) $row->product_id] = max(0, (float) $row->available_quantity);
        }

        return $map;
    }
}
