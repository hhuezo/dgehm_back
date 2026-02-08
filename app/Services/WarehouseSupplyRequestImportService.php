<?php

namespace App\Services;

use App\Models\warehouse\Office;
use App\Models\warehouse\Product;
use App\Models\warehouse\SupplyRequest;
use App\Models\warehouse\SupplyRequestDetail;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WarehouseSupplyRequestImportService
{
    public array $errors = [];
    public int $requestsCreated = 0;
    public int $skipped = 0;

    public function import(string $filePath): self
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheetByName('ENTREGA NOVI');

        if (!$sheet) {
            $this->errors[] = 'No se encontró la hoja ENTREGA NOVI.';
            return $this;
        }

        $highestRow = min($sheet->getHighestRow(), 200);
        $userId = \App\Models\User::first()?->id;

        if (!$userId) {
            $this->errors[] = 'No hay usuarios en el sistema.';
            return $this;
        }

        // Oficinas: fila 7, columnas 4 a 41 (excluir 42 TOTALES)
        // Agrupar por nombre de oficina (puede repetirse en varias columnas)
        $officeColumns = [];
        for ($col = 4; $col <= 41; $col++) {
            $name = trim((string) $sheet->getCellByColumnAndRow($col, 7)->getValue());
            if ($name && stripos($name, 'TOTAL') === false) {
                if (!isset($officeColumns[$name])) {
                    $officeColumns[$name] = [];
                }
                $officeColumns[$name][] = $col;
            }
        }

        // Productos: filas 9 a $highestRow, col 2 = nombre
        $productRows = [];
        for ($row = 9; $row <= $highestRow; $row++) {
            $name = trim((string) $sheet->getCellByColumnAndRow(2, $row)->getValue());
            if (!$name || stripos($name, 'TOTAL') !== false) {
                continue;
            }
            $productRows[$row] = $name;
        }

        foreach ($officeColumns as $officeName => $columns) {
            try {
                $office = Office::firstOrCreate(
                    ['name' => $officeName],
                    ['is_active' => true]
                );

                $requestDate = $this->randomDateInJanuary2025();

                $supplyRequest = SupplyRequest::create([
                    'date' => $requestDate->format('Y-m-d H:i:s'),
                    'observation' => 'Importación desde Excel - ' . $officeName,
                    'requester_id' => $userId,
                    'office_id' => $office->id,
                    'immediate_boss_id' => $userId,
                    'status_id' => 1,
                ]);

                $detailsByProduct = [];

                foreach ($productRows as $row => $productName) {
                    $totalQty = 0;
                    foreach ($columns as $col) {
                        $qtyVal = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                        $totalQty += is_numeric($qtyVal) ? (float) $qtyVal : 0;
                    }

                    if ($totalQty <= 0) {
                        continue;
                    }

                    $product = $this->findProduct($productName);
                    if (!$product) {
                        $this->errors[] = "Producto no encontrado: {$productName} (oficina: {$officeName})";
                        $this->skipped++;
                        continue;
                    }

                    $detailsByProduct[$product->id] = ($detailsByProduct[$product->id] ?? 0) + $totalQty;
                }

                foreach ($detailsByProduct as $productId => $qty) {
                    SupplyRequestDetail::create([
                        'supply_request_id' => $supplyRequest->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'delivered_quantity' => $qty,
                    ]);
                }

                if (empty($detailsByProduct)) {
                    $supplyRequest->delete();
                    $this->skipped++;
                    continue;
                }

                $this->finalizeSupplyRequest($supplyRequest->id, $requestDate);
                $this->requestsCreated++;
            } catch (\Exception $e) {
                $this->errors[] = "Oficina {$officeName}: " . $e->getMessage();
                $this->skipped++;
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this;
    }

    protected function randomDateInJanuary2025(): \DateTime
    {
        $min = strtotime('2025-01-02 08:00:00');
        $max = strtotime('2025-01-31 18:00:00');
        $timestamp = rand($min, $max);
        return new \DateTime(date('Y-m-d H:i:s', $timestamp));
    }

    protected function findProduct(string $name): ?Product
    {
        $name = trim($name);
        if (!$name) {
            return null;
        }

        // Búsqueda exacta
        $product = Product::where('name', $name)->first();
        if ($product) {
            return $product;
        }

        // Búsqueda por comienzo (nombre truncado en Excel)
        $product = Product::where('name', 'like', $name . '%')->first();
        if ($product) {
            return $product;
        }

        // Búsqueda por contención
        $product = Product::where('name', 'like', '%' . substr($name, 0, 30) . '%')->first();
        if ($product) {
            return $product;
        }

        return null;
    }

    protected function finalizeSupplyRequest(int $supplyRequestId, \DateTime $deliveryDate): void
    {
        $details = SupplyRequestDetail::where('supply_request_id', $supplyRequestId)
            ->where('delivered_quantity', '>', 0)
            ->get();

        $kardexToInsert = [];

        foreach ($details as $detail) {
            try {
                $distribution = $this->resolveKardexStock(
                    $detail->product_id,
                    (int) $detail->delivered_quantity
                );

                foreach ($distribution as $item) {
                    $kardexToInsert[] = [
                        'purchase_order_id' => $item['purchase_order_id'],
                        'product_id' => $item['product_id'],
                        'movement_type' => 2,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                        'supply_request_id' => $supplyRequestId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } catch (\Exception $e) {
                throw new \Exception("Producto ID {$detail->product_id}: " . $e->getMessage());
            }
        }

        if (!empty($kardexToInsert)) {
            DB::table('wh_kardex')->insert($kardexToInsert);
        }

        SupplyRequest::where('id', $supplyRequestId)->update([
            'status_id' => 4,
            'delivery_date' => $deliveryDate->format('Y-m-d'),
        ]);
    }

    protected function resolveKardexStock(int $productId, int $deliveredQuantity): array
    {
        $result = [];
        $remaining = $deliveredQuantity;

        $orders = DB::table('wh_kardex')
            ->select(
                'purchase_order_id',
                'product_id',
                'unit_price',
                DB::raw("
                SUM(
                    CASE
                        WHEN movement_type = 1 THEN quantity
                        WHEN movement_type = 2 THEN -quantity
                        ELSE 0
                    END
                ) AS stock
            ")
            )
            ->where('product_id', $productId)
            ->groupBy('purchase_order_id', 'product_id', 'unit_price')
            ->havingRaw('stock > 0')
            ->orderBy('purchase_order_id')
            ->get();

        foreach ($orders as $order) {
            if ($remaining <= 0) {
                break;
            }

            $take = min((int) $order->stock, $remaining);

            $result[] = [
                'purchase_order_id' => $order->purchase_order_id,
                'product_id' => $order->product_id,
                'quantity' => $take,
                'unit_price' => (float) $order->unit_price,
                'subtotal' => round($take * $order->unit_price, 4),
            ];

            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \Exception('Existencia insuficiente');
        }

        return $result;
    }
}
