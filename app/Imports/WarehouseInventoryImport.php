<?php

namespace App\Imports;

use App\Models\warehouse\AccountingAccount;
use App\Models\warehouse\Kardex;
use App\Models\warehouse\Measure;
use App\Models\warehouse\Product;
use App\Models\warehouse\PurchaseOrder;
use App\Models\warehouse\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class WarehouseInventoryImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;
    public ?int $purchaseOrderId = null;

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        // Crear proveedor genérico
        $supplier = Supplier::firstOrCreate(
            ['name' => 'Proveedor Genérico'],
            ['is_active' => true]
        );

        // Obtener primer usuario para administrative_technician_id (puede ser null)
        $technicianId = \App\Models\User::first()?->id;

        // Obtener o crear orden de compra 0001
        $orderDate = Carbon::parse('2025-01-01')->startOfDay();
        $order = PurchaseOrder::firstOrCreate(
            ['order_number' => '0001'],
            [
                'supplier_id' => $supplier->id,
                'budget_commitment_number' => 'IMP-0001',
                'acta_date' => $orderDate,
                'reception_date' => $orderDate,
                'supplier_representative' => 'Importación Excel',
                'invoice_number' => 'FAC-IMP-0001',
                'invoice_date' => $orderDate->format('Y-m-d'),
                'total_amount' => 0,
                'administrative_manager' => 'Sistema',
                'administrative_technician_id' => $technicianId ?? null,
            ]
        );
        $this->purchaseOrderId = $order->id;

        $lastCode = null;
        $lastConcepto = null;
        $totalAmount = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $data = $this->normalizeRow($row->toArray());

                $producto = $this->getValue($data, ['producto', 'product', 'nombre']);
                $codigo = $this->getValue($data, ['codigo', 'code']) ?? $lastCode;
                $concepto = $this->getValue($data, ['concepto', 'concept', 'categoria']) ?? $lastConcepto;
                $cantidad = $this->parseDecimal($data, ['cantidad', 'quantity']) ?? 0;
                $unidad = $this->getValue($data, ['unidad', 'unit', 'medida']);
                $precioUnitario = $this->parseDecimal($data, ['precio_unitario', 'precio', 'unit_price']) ?? 0;
                $valor = $this->parseDecimal($data, ['valor', 'value', 'total']);

                // Saltar filas sin producto o subtotales
                if (!$producto || trim($producto) === '') {
                    continue;
                }
                if (stripos($producto, 'sub total') !== false || stripos($producto, 'subtotal') !== false) {
                    $lastCode = null;
                    $lastConcepto = null;
                    continue;
                }
                if (stripos($concepto ?? '', 'sub total') !== false) {
                    continue;
                }

                // Saltar filas con cantidad 0
                if ($cantidad <= 0) {
                    $this->skipped++;
                    continue;
                }

                if ($codigo) {
                    $lastCode = $codigo;
                }
                if ($concepto) {
                    $lastConcepto = $concepto;
                }

                if (!$codigo || !$concepto) {
                    $this->errors[] = "Fila {$rowNumber}: Falta código o concepto.";
                    $this->skipped++;
                    continue;
                }

                if (!$unidad) {
                    $this->errors[] = "Fila {$rowNumber}: Falta unidad de medida.";
                    $this->skipped++;
                    continue;
                }

                // Obtener o crear cuenta contable (por código)
                $accountingAccount = AccountingAccount::firstOrCreate(
                    ['code' => $codigo],
                    [
                        'name' => $concepto,
                        'is_active' => true,
                    ]
                );

                // Obtener o crear unidad de medida
                $measure = Measure::firstOrCreate(
                    ['name' => trim($unidad)],
                    ['is_active' => true]
                );

                // Obtener o crear producto (por nombre + medida + cuenta)
                $product = Product::firstOrCreate(
                    [
                        'name' => trim($producto),
                        'measure_id' => $measure->id,
                        'accounting_account_id' => $accountingAccount->id,
                    ],
                    [
                        'description' => $producto,
                        'is_active' => true,
                    ]
                );

                // Calcular subtotal
                $subtotal = $valor ?? ($cantidad * $precioUnitario);

                // Crear entrada en kardex
                Kardex::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'movement_type' => 1, // ENTRADA
                    'quantity' => (int) $cantidad,
                    'unit_price' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                $this->skipped++;
            }
        }

        // Actualizar monto total de la orden
        if ($this->imported > 0) {
            $order->total_amount = $totalAmount;
            $order->save();
        }
    }

    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeKey($key);
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }

    protected function normalizeKey($key): string
    {
        if (!is_string($key)) {
            return (string) $key;
        }
        $key = mb_strtolower($key);
        $key = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        return trim($key, '_');
    }

    protected function getValue(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = $this->normalizeKey($key);
            if (isset($data[$normalizedKey]) && $data[$normalizedKey] !== null && $data[$normalizedKey] !== '') {
                $val = $data[$normalizedKey];
                return is_numeric($val) ? (string) $val : trim((string) $val);
            }
        }
        return null;
    }

    protected function parseDecimal(array $data, array $possibleKeys): ?float
    {
        $value = $this->getValue($data, $possibleKeys);
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $cleaned = preg_replace('/[^0-9.,\-]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}
