<?php

namespace App\Services;

use App\Models\warehouse\AccountingAccount;
use App\Models\warehouse\Kardex;
use App\Models\warehouse\Measure;
use App\Models\warehouse\Product;
use App\Models\warehouse\PurchaseOrder;
use App\Models\warehouse\Supplier;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WarehouseInventoryImportService
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;
    public ?int $purchaseOrderId = null;

    public function import(string $filePath): self
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $reader = IOFactory::createReader($ext === 'csv' ? 'Csv' : ($ext === 'xls' ? 'Xls' : 'Xlsx'));
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $supplier = Supplier::firstOrCreate(
            ['name' => 'Proveedor Genérico'],
            ['is_active' => true]
        );

        $technicianId = \App\Models\User::first()?->id;
        $orderDate = Carbon::parse('2025-01-01')->startOfDay();

        $lastOrder = PurchaseOrder::orderBy('id', 'desc')->first();
        $lastNum = $lastOrder ? (int) preg_replace('/\D/', '', $lastOrder->order_number) : 0;
        $orderNumber = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        $order = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'order_number' => $orderNumber,
            'budget_commitment_number' => 'IMP-' . $orderNumber,
            'acta_date' => $orderDate,
            'reception_date' => $orderDate,
            'supplier_representative' => 'Importación Excel',
            'invoice_number' => 'FAC-IMP-' . $orderNumber . '-' . time(),
            'invoice_date' => $orderDate->format('Y-m-d'),
            'total_amount' => 0,
            'administrative_manager' => 'Sistema',
            'administrative_technician_id' => $technicianId,
        ]);

        $this->purchaseOrderId = $order->id;
        $lastCode = null;
        $lastConcepto = null;
        $totalAmount = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $codigo = trim((string) $sheet->getCell('A' . $row)->getValue());
            $concepto = trim((string) $sheet->getCell('B' . $row)->getValue());
            $producto = trim((string) $sheet->getCell('C' . $row)->getValue());
            $cantidadVal = $sheet->getCell('D' . $row)->getValue();
            $unidad = trim((string) $sheet->getCell('E' . $row)->getValue());
            $precioVal = $sheet->getCell('F' . $row)->getValue();
            $valorVal = $sheet->getCell('G' . $row)->getValue();

            $cantidad = is_numeric($cantidadVal) ? (float) $cantidadVal : 0;
            $precioUnitario = is_numeric($precioVal) ? (float) $precioVal : 0;
            $valor = is_numeric($valorVal) ? (float) $valorVal : null;

            if (!$producto) continue;
            if (stripos($producto, 'sub total') !== false || stripos($producto, 'subtotal') !== false) {
                $lastCode = null;
                $lastConcepto = null;
                continue;
            }
            if (stripos($concepto ?? '', 'sub total') !== false) continue;
            if ($cantidad <= 0) { $this->skipped++; continue; }

            if ($codigo) $lastCode = $codigo;
            if ($concepto) $lastConcepto = $concepto;

            $codigo = $codigo ?: $lastCode;
            $concepto = $concepto ?: $lastConcepto;

            if (!$codigo || !$concepto) {
                $this->errors[] = "Fila {$row}: Falta código o concepto.";
                $this->skipped++;
                continue;
            }
            if (!$unidad) {
                $this->errors[] = "Fila {$row}: Falta unidad.";
                $this->skipped++;
                continue;
            }

            try {
                $accountingAccount = AccountingAccount::firstOrCreate(
                    ['code' => $codigo],
                    ['name' => $concepto, 'is_active' => true]
                );

                $measure = Measure::firstOrCreate(
                    ['name' => $unidad],
                    ['is_active' => true]
                );

                $product = Product::firstOrCreate(
                    [
                        'name' => $producto,
                        'measure_id' => $measure->id,
                        'accounting_account_id' => $accountingAccount->id,
                    ],
                    ['description' => $producto, 'is_active' => true]
                );

                $subtotal = $valor ?? ($cantidad * $precioUnitario);

                Kardex::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'movement_type' => 1,
                    'quantity' => (int) $cantidad,
                    'unit_price' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Fila {$row}: " . $e->getMessage();
                $this->skipped++;
            }
        }

        $order->total_amount = $totalAmount;
        $order->save();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this;
    }
}
