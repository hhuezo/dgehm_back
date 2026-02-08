<?php

namespace App\Services;

use App\Models\User;
use App\Models\warehouse\AccountingAccount;
use App\Models\warehouse\Kardex;
use App\Models\warehouse\Measure;
use App\Models\warehouse\Office;
use App\Models\warehouse\Product;
use App\Models\warehouse\PurchaseOrder;
use App\Models\warehouse\Supplier;
use App\Models\warehouse\SupplyRequest;
use App\Models\warehouse\SupplyRequestDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ejecuta el plan de entradas (OCs) y salidas (solicitudes entregadas).
 * Feb–Jun 2025 y Jul 2025–Ene 2026. Respeta PEPS en las salidas.
 */
class PlanEntradasSalidasService
{
    public array $errors = [];
    public array $log = [];

    protected ?int $nextOrderNum = null;
    /** @var array<string, Product> */
    protected array $productCache = [];
    /** @var array<string, Office> */
    protected array $officeCache = [];
    /** @var array<string, Supplier> */
    protected array $supplierCache = [];

    protected string $observationPlan1 = 'Plan Feb–Jun 2025';
    protected string $observationPlan2 = 'Plan Jul 2025–Ene 2026';

    public function run(): self
    {
        $this->log[] = 'Iniciando plan (Feb–Jun 2025 y Jul 2025–Ene 2026).';

        $this->ensureSuppliers();
        $this->ensureExtraProducts();

        if (!PurchaseOrder::where('order_number', '0002')->exists()) {
            $this->log[] = 'Ejecutando bloque Feb–Jun 2025.';
            foreach (['2025-02', '2025-03', '2025-04', '2025-05', '2025-06'] as $month) {
                $this->runMonth($month, $this->observationPlan1);
            }
        } else {
            $this->log[] = 'Bloque Feb–Jun 2025 ya ejecutado (existe OC 0002).';
        }

        if (!PurchaseOrder::where('order_number', '0022')->exists()) {
            $this->log[] = 'Ejecutando bloque Jul 2025–Ene 2026.';
            foreach (['2025-07', '2025-08', '2025-09', '2025-10', '2025-11', '2025-12', '2026-01'] as $month) {
                $this->runMonth($month, $this->observationPlan2);
            }
        } else {
            $this->log[] = 'Bloque Jul 2025–Ene 2026 ya ejecutado (existe OC 0022).';
        }

        $this->log[] = 'Plan finalizado.';
        return $this;
    }

    protected function ensureSuppliers(): void
    {
        $allSuppliers = [
            ['name' => 'PROVEEDORA DE ALIMENTOS EL SALVADOR S.A. DE C.V.', 'contact' => 'Juan Pérez', 'phone' => '2255-0000', 'email' => 'ventas@proveedora.com', 'address' => 'Avenida Principal, San Salvador'],
            ['name' => 'SUMINISTROS DE OFICINA CENTRAL S.A. DE C.V.', 'contact' => 'Maria López', 'phone' => '2266-1111', 'email' => 'info@suministros.com', 'address' => 'Calle Secundaria, Santa Ana'],
            ['name' => 'DISTRIBUIDORA DE PAPEL Y LIMPIEZA S.A.', 'contact' => 'Ana García', 'phone' => '2270-1111', 'email' => 'ventas@papelylimpieza.com', 'address' => 'Zona Industrial, San Salvador'],
            ['name' => 'INSUMOS QUÍMICOS Y LABORATORIO S.A.', 'contact' => 'Carlos Méndez', 'phone' => '2280-2222', 'email' => 'info@insumoslab.com', 'address' => 'Colonia Escalón, San Salvador'],
            ['name' => 'TECNOLOGÍA Y SUMINISTROS C.A.', 'contact' => 'Luis Rodríguez', 'phone' => '2290-3333', 'email' => 'ventas@tecnosum.com', 'address' => 'Santa Ana'],
            ['name' => 'PROVEEDORA NACIONAL DE CAFÉ Y ALIMENTOS', 'contact' => 'Rosa Martínez', 'phone' => '2260-4444', 'email' => 'pedidos@cafealimentos.com', 'address' => 'Sonsonate'],
        ];

        foreach ($allSuppliers as $s) {
            $sup = Supplier::firstOrCreate(
                ['name' => $s['name']],
                [
                    'contact_person' => $s['contact'],
                    'phone' => $s['phone'],
                    'email' => $s['email'],
                    'address' => $s['address'],
                    'is_active' => true,
                ]
            );
            $this->supplierCache[$s['name']] = $sup;
        }

        Supplier::all()->each(function (Supplier $s) {
            $this->supplierCache[$s->name] = $s;
        });
        $this->log[] = 'Proveedores listos.';
    }

    protected function ensureExtraProducts(): void
    {
        $measureId = Measure::firstOrCreate(['name' => 'Unidad'], ['is_active' => true])->id;
        $resmaId = Measure::firstOrCreate(['name' => 'Resma'], ['is_active' => true])->id;
        $cuentaAlimentos = AccountingAccount::firstOrCreate(['code' => '54101'], ['name' => 'PRODUCTOS ALIMENTICIOS PARA PERSONAS', 'is_active' => true])->id;
        $accounts = [
            '54105' => AccountingAccount::firstOrCreate(['code' => '54105'], ['name' => 'PRODUCTOS DE PAPEL Y CARTON', 'is_active' => true]),
            '54115' => AccountingAccount::firstOrCreate(['code' => '54115'], ['name' => 'MATERIALES INFORMATICOS', 'is_active' => true]),
            '54107' => AccountingAccount::firstOrCreate(['code' => '54107'], ['name' => 'PRODUCTOS QUIMICOS', 'is_active' => true]),
        ];

        $products = [
            ['name' => 'RESMA DE PAPEL CARTA', 'account' => $accounts['54105'], 'measure_id' => $resmaId],
            ['name' => 'MATERIAL INFORMATICO', 'account' => $accounts['54115'], 'measure_id' => $measureId],
            ['name' => 'INSUMOS LABORATORIO', 'account' => $accounts['54107'], 'measure_id' => $measureId],
            ['name' => 'CAFE MOLIDO MAJADA ORO, BOLSA DE 1 LIBRA CON EMPAQUE METALIZADO CON VALVULA', 'account_id' => $cuentaAlimentos, 'measure_id' => $measureId],
            ['name' => 'AZUCAR DE 1 LIBRA DEL CAÑAL', 'account_id' => $cuentaAlimentos, 'measure_id' => $measureId],
            ['name' => 'BOLSAS DE CAFÉ TOSTADO Y MOLIDO', 'account_id' => $cuentaAlimentos, 'measure_id' => $measureId],
            ['name' => 'SOBRE DE AZUCAR SPLENDA (CAJA DE 100 UNIDADES)', 'account_id' => $cuentaAlimentos, 'measure_id' => $measureId],
            ['name' => 'TE DE MANZANILLA CANELA. CAJA DE 20 UNIDADES MARCA MC CORMICK', 'account_id' => $cuentaAlimentos, 'measure_id' => $measureId],
        ];

        foreach ($products as $p) {
            $accountId = isset($p['account']) ? $p['account']->id : $p['account_id'];
            Product::firstOrCreate(
                ['name' => $p['name']],
                [
                    'accounting_account_id' => $accountId,
                    'measure_id' => $p['measure_id'],
                    'description' => $p['name'],
                    'is_active' => true,
                ]
            );
        }
        $this->log[] = 'Productos extra listos.';
    }

    protected function resolveProduct(string $key): ?Product
    {
        if (isset($this->productCache[$key])) {
            return $this->productCache[$key];
        }

        $patterns = [
            'cafe' => ['CAFE MOLIDO MAJADA', 'CAFE MOLIDO', 'MAJADA ORO', 'CAFE'],
            'cremora' => ['CREMORA EN SOBRE', 'CREMORA'],
            'azucar_1lb' => ['AZUCAR DE 1 LIBRA', 'AZUCAR DE 1 LIBRA DEL', '1 LIBRA DEL CANAL', '1 LIBRA DEL CAÑAL'],
            'te_canela' => ['TE DE CANELA', 'CANELA CAJA', 'CANELA CAJA DE 25'],
            'te_negro' => ['TE NEGRO', 'NEGRO CAJA'],
            'sal' => ['SAL EN SOBRE', 'SAL EN SOBRE BOLSA'],
            'papel_aluminio' => ['PAPEL DE ALUMINIO', 'PAPEL DE ALUMINIO ROLLO', 'ALUMINIO ROLLO'],
            'bolsas_cafe' => ['BOLSAS DE CAFE', 'BOLSAS DE CAFÉ', 'CAFÉ TOSTADO Y MOLIDO', 'CAFE TOSTADO'],
            'te_menta' => ['TE DE MENTA', 'MENTA CAJA'],
            'te_verde' => ['TE VERDE', 'VERDE CAJA'],
            'azucar_splenda' => ['SOBRE DE AZUCAR SPLENDA', 'AZUCAR SPLENDA', 'SPLENDA'],
            'te_manzanilla' => ['TE DE MANZANILLA', 'MANZANILLA CANELA', 'MANZANILLA'],
            'resma' => ['RESMA DE PAPEL', 'RESMA DE PAPEL CARTA'],
            'material_informatico' => ['MATERIAL INFORMATICO', 'MATERIAL INFORMÁTICO'],
            'insumos_lab' => ['INSUMOS LABORATORIO', 'INSUMOS LAB'],
        ];

        $tryPatterns = $patterns[$key] ?? [$key];
        if (!is_array($tryPatterns)) {
            $tryPatterns = [$tryPatterns];
        }
        $product = null;
        foreach ($tryPatterns as $pattern) {
            $product = Product::where('name', 'LIKE', '%' . $pattern . '%')->first();
            if ($product) {
                break;
            }
        }
        if ($product) {
            $this->productCache[$key] = $product;
        }
        return $product;
    }

    protected function getOffice(string $namePart): ?Office
    {
        $key = $namePart;
        if (isset($this->officeCache[$key])) {
            return $this->officeCache[$key];
        }
        $office = Office::where('name', 'LIKE', '%' . $namePart . '%')->first();
        if ($office) {
            $this->officeCache[$key] = $office;
        }
        return $office;
    }

    protected function getSupplier(string $namePart): ?Supplier
    {
        foreach ($this->supplierCache as $name => $s) {
            if (stripos($name, $namePart) !== false) {
                return $s;
            }
        }
        return Supplier::where('name', 'LIKE', '%' . $namePart . '%')->first();
    }

    protected function getNextOrderNumber(): string
    {
        if ($this->nextOrderNum === null) {
            $last = PurchaseOrder::orderBy('id', 'desc')->first();
            $this->nextOrderNum = $last ? (int) preg_replace('/\D/', '', $last->order_number) + 1 : 2;
        }
        $num = str_pad((string) $this->nextOrderNum, 4, '0', STR_PAD_LEFT);
        $this->nextOrderNum++;
        return $num;
    }

    protected function createPurchaseOrder(string $supplierNamePart, string $receptionDate, array $lines): ?PurchaseOrder
    {
        $supplier = $this->getSupplier($supplierNamePart);
        if (!$supplier) {
            $this->errors[] = "Proveedor no encontrado: {$supplierNamePart}";
            return null;
        }

        $orderNum = $this->getNextOrderNumber();
        $techId = User::first()?->id;

        $total = 0;
        $kardexRows = [];
        foreach ($lines as $line) {
            $product = $this->resolveProduct($line['product']);
            if (!$product) {
                $this->errors[] = "Producto no encontrado: {$line['product']}";
                continue;
            }
            $subtotal = $line['qty'] * $line['price'];
            $total += $subtotal;
            $kardexRows[] = [
                'product_id' => $product->id,
                'quantity' => (int) $line['qty'],
                'unit_price' => $line['price'],
                'subtotal' => round($subtotal, 4),
            ];
        }

        if (empty($kardexRows)) {
            return null;
        }

        $date = Carbon::parse($receptionDate)->format('Y-m-d');
        $order = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'order_number' => $orderNum,
            'budget_commitment_number' => 'OC-' . $orderNum,
            'acta_date' => $date . ' 10:00:00',
            'reception_date' => $date . ' 10:00:00',
            'supplier_representative' => 'Rep. ' . $supplier->name,
            'invoice_number' => 'FAC-' . $orderNum . '-' . time(),
            'invoice_date' => $date . ' 10:00:00',
            'total_amount' => round($total, 2),
            'administrative_manager' => 'Sistema',
            'administrative_technician_id' => $techId,
        ]);

        foreach ($kardexRows as $row) {
            Kardex::create([
                'purchase_order_id' => $order->id,
                'product_id' => $row['product_id'],
                'movement_type' => 1,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'subtotal' => $row['subtotal'],
                'supply_request_id' => null,
            ]);
        }

        $this->log[] = "OC {$orderNum} creada (recepción {$receptionDate}).";
        return $order;
    }

    /**
     * PEPS: consume primero el lote más antiguo (menor purchase_order_id).
     */
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
                'subtotal' => round($take * (float) $order->unit_price, 4),
            ];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \Exception("Existencia insuficiente para el producto ID {$productId}. Faltan {$remaining} unidades.");
        }

        return $result;
    }

    protected function deliverRequest(int $requestId, string $deliveryDate): void
    {
        $request = SupplyRequest::with('details')->findOrFail($requestId);
        if ($request->status_id !== 3) {
            $this->errors[] = "Solicitud {$requestId} no está Aprobada (status_id=3).";
            return;
        }

        $details = SupplyRequestDetail::where('supply_request_id', $requestId)->where('delivered_quantity', '>', 0)->get();
        if ($details->isEmpty()) {
            $this->errors[] = "Solicitud {$requestId} no tiene delivered_quantity > 0.";
            return;
        }

        $kardexToInsert = [];
        foreach ($details as $detail) {
            try {
                $distribution = $this->resolveKardexStock($detail->product_id, (int) $detail->delivered_quantity);
                foreach ($distribution as $item) {
                    $kardexToInsert[] = [
                        'purchase_order_id' => $item['purchase_order_id'],
                        'product_id' => $item['product_id'],
                        'movement_type' => 2,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                        'supply_request_id' => $requestId,
                        'supply_return_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } catch (\Exception $e) {
                $this->errors[] = "Solicitud {$requestId} producto {$detail->product_id}: " . $e->getMessage();
                return;
            }
        }

        DB::table('wh_kardex')->insert($kardexToInsert);
        $request->delivery_date = $deliveryDate;
        $request->delivered_by_id = User::first()?->id;
        $request->status_id = 4;
        $request->save();
        $this->log[] = "Solicitud {$requestId} entregada (PEPS aplicado).";
    }

    protected function runMonth(string $month, string $observation = 'Plan Feb–Jun 2025'): void
    {
        $baseDate = Carbon::parse($month . '-01');
        $year = $baseDate->format('Y');
        $monthNum = (int) $baseDate->format('n');
        $y = (int) $year;

        if ($monthNum === 2) {
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-02-05', [
                ['product' => 'cafe', 'qty' => 20, 'price' => 3.50],
                ['product' => 'cremora', 'qty' => 50, 'price' => 0.25],
                ['product' => 'azucar_1lb', 'qty' => 30, 'price' => 1.20],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-02-10', [
                ['product' => 'resma', 'qty' => 10, 'price' => 4.00],
                ['product' => 'papel_aluminio', 'qty' => 5, 'price' => 8.00],
            ]);
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-02-15', [
                ['product' => 'te_canela', 'qty' => 15, 'price' => 2.00],
                ['product' => 'te_negro', 'qty' => 15, 'price' => 2.00],
                ['product' => 'sal', 'qty' => 100, 'price' => 0.15],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2025-02-25', [
                ['product' => 'papel_aluminio', 'qty' => 3, 'price' => 7.50],
                ['product' => 'bolsas_cafe', 'qty' => 10, 'price' => 2.80],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-02-20', '2025-02-21', [
                ['product' => 'cremora', 'qty' => 20],
                ['product' => 'cafe', 'qty' => 5],
                ['product' => 'te_canela', 'qty' => 5],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-02-25', '2025-02-26', [
                ['product' => 'te_negro', 'qty' => 5],
                ['product' => 'sal', 'qty' => 30],
            ], $observation);
        }

        if ($monthNum === 3) {
            $this->createPurchaseOrder('INSUMOS QUÍMICOS', '2025-03-05', [
                ['product' => 'insumos_lab', 'qty' => 10, 'price' => 5.00],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-03-12', [
                ['product' => 'azucar_1lb', 'qty' => 40, 'price' => 1.25],
                ['product' => 'cremora', 'qty' => 30, 'price' => 0.28],
            ]);
            $this->createPurchaseOrder('TECNOLOGÍA Y SUMINISTROS', '2025-03-18', [
                ['product' => 'material_informatico', 'qty' => 5, 'price' => 15.00],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-03-25', [
                ['product' => 'resma', 'qty' => 8, 'price' => 4.20],
                ['product' => 'te_menta', 'qty' => 10, 'price' => 2.10],
            ]);

            $this->createSupplyRequestAndDeliver('UNIDAD DE TECNOLOGIA', '2025-03-10', '2025-03-11', [
                ['product' => 'resma', 'qty' => 2],
            ], $observation);
            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-03-20', '2025-03-21', [
                ['product' => 'cafe', 'qty' => 8],
                ['product' => 'cremora', 'qty' => 15],
                ['product' => 'azucar_1lb', 'qty' => 10],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-03-28', '2025-03-29', [
                ['product' => 'sal', 'qty' => 20],
                ['product' => 'te_negro', 'qty' => 5],
            ], $observation);
        }

        if ($monthNum === 4) {
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-04-05', [
                ['product' => 'cafe', 'qty' => 25, 'price' => 3.60],
                ['product' => 'te_verde', 'qty' => 20, 'price' => 2.05],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2025-04-10', [
                ['product' => 'papel_aluminio', 'qty' => 4, 'price' => 8.50],
            ]);
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-04-18', [
                ['product' => 'te_canela', 'qty' => 20, 'price' => 2.10],
                ['product' => 'azucar_splenda', 'qty' => 5, 'price' => 4.00],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-04-25', [
                ['product' => 'resma', 'qty' => 12, 'price' => 4.00],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA FINANCIERA', '2025-04-08', '2025-04-09', [
                ['product' => 'resma', 'qty' => 3],
                ['product' => 'te_canela', 'qty' => 5],
            ], $observation);
            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-04-15', '2025-04-16', [
                ['product' => 'cremora', 'qty' => 25],
                ['product' => 'papel_aluminio', 'qty' => 2],
            ], $observation);
        }

        if ($monthNum === 5) {
            $this->createPurchaseOrder('INSUMOS QUÍMICOS', '2025-05-05', [
                ['product' => 'insumos_lab', 'qty' => 8, 'price' => 6.00],
            ]);
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-05-12', [
                ['product' => 'cafe', 'qty' => 30, 'price' => 3.55],
                ['product' => 'te_manzanilla', 'qty' => 15, 'price' => 2.20],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-05-20', [
                ['product' => 'resma', 'qty' => 10, 'price' => 4.10],
                ['product' => 'te_negro', 'qty' => 15, 'price' => 2.00],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-05-28', [
                ['product' => 'azucar_1lb', 'qty' => 25, 'price' => 1.22],
                ['product' => 'sal', 'qty' => 80, 'price' => 0.16],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA LEGAL', '2025-05-10', '2025-05-11', [
                ['product' => 'resma', 'qty' => 2],
                ['product' => 'te_negro', 'qty' => 3],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-05-22', '2025-05-23', [
                ['product' => 'sal', 'qty' => 25],
                ['product' => 'insumos_lab', 'qty' => 2],
            ], $observation);
            $this->createSupplyRequestAndDeliver('UNIDAD DE TECNOLOGIA', '2025-05-28', '2025-05-29', [
                ['product' => 'material_informatico', 'qty' => 2],
            ], $observation);
        }

        if ($monthNum === 6) {
            $this->createPurchaseOrder('TECNOLOGÍA Y SUMINISTROS', '2025-06-05', [
                ['product' => 'material_informatico', 'qty' => 6, 'price' => 14.00],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-06-12', [
                ['product' => 'cafe', 'qty' => 20, 'price' => 3.65],
                ['product' => 'cremora', 'qty' => 40, 'price' => 0.26],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2025-06-18', [
                ['product' => 'papel_aluminio', 'qty' => 5, 'price' => 8.00],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-06-25', [
                ['product' => 'resma', 'qty' => 15, 'price' => 4.15],
                ['product' => 'te_verde', 'qty' => 10, 'price' => 2.10],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-06-10', '2025-06-11', [
                ['product' => 'cafe', 'qty' => 10],
                ['product' => 'cremora', 'qty' => 20],
                ['product' => 'resma', 'qty' => 2],
            ], $observation);
            $this->createSupplyRequestAndDeliver('GERENCIA FINANCIERA', '2025-06-20', '2025-06-21', [
                ['product' => 'te_canela', 'qty' => 5],
                ['product' => 'azucar_1lb', 'qty' => 5],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-06-25', '2025-06-26', [
                ['product' => 'sal', 'qty' => 15],
                ['product' => 'insumos_lab', 'qty' => 1],
            ], $observation);
        }

        // Jul 2025
        if ($monthNum === 7 && $y === 2025) {
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-07-05', [
                ['product' => 'cafe', 'qty' => 25, 'price' => 3.70],
                ['product' => 'cremora', 'qty' => 45, 'price' => 0.27],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-07-10', [
                ['product' => 'resma', 'qty' => 12, 'price' => 4.20],
                ['product' => 'te_negro', 'qty' => 12, 'price' => 2.05],
            ]);
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-07-18', [
                ['product' => 'te_canela', 'qty' => 15, 'price' => 2.15],
                ['product' => 'sal', 'qty' => 80, 'price' => 0.16],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2025-07-25', [
                ['product' => 'papel_aluminio', 'qty' => 4, 'price' => 8.20],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-07-15', '2025-07-16', [
                ['product' => 'cafe', 'qty' => 8],
                ['product' => 'cremora', 'qty' => 15],
                ['product' => 'resma', 'qty' => 2],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-07-22', '2025-07-23', [
                ['product' => 'sal', 'qty' => 20],
                ['product' => 'te_canela', 'qty' => 5],
            ], $observation);
        }

        // Ago 2025
        if ($monthNum === 8 && $y === 2025) {
            $this->createPurchaseOrder('INSUMOS QUÍMICOS', '2025-08-05', [
                ['product' => 'insumos_lab', 'qty' => 10, 'price' => 5.50],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-08-12', [
                ['product' => 'azucar_1lb', 'qty' => 35, 'price' => 1.28],
                ['product' => 'te_verde', 'qty' => 15, 'price' => 2.10],
            ]);
            $this->createPurchaseOrder('TECNOLOGÍA Y SUMINISTROS', '2025-08-18', [
                ['product' => 'material_informatico', 'qty' => 4, 'price' => 15.50],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-08-25', [
                ['product' => 'resma', 'qty' => 10, 'price' => 4.25],
                ['product' => 'te_menta', 'qty' => 12, 'price' => 2.12],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA FINANCIERA', '2025-08-08', '2025-08-09', [
                ['product' => 'resma', 'qty' => 3],
                ['product' => 'te_negro', 'qty' => 4],
            ], $observation);
            $this->createSupplyRequestAndDeliver('UNIDAD DE TECNOLOGIA', '2025-08-20', '2025-08-21', [
                ['product' => 'material_informatico', 'qty' => 2],
            ], $observation);
        }

        // Sep 2025
        if ($monthNum === 9 && $y === 2025) {
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-09-05', [
                ['product' => 'cafe', 'qty' => 22, 'price' => 3.68],
                ['product' => 'te_manzanilla', 'qty' => 12, 'price' => 2.25],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2025-09-12', [
                ['product' => 'papel_aluminio', 'qty' => 5, 'price' => 8.30],
                ['product' => 'bolsas_cafe', 'qty' => 8, 'price' => 2.85],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-09-20', [
                ['product' => 'cremora', 'qty' => 40, 'price' => 0.26],
                ['product' => 'azucar_splenda', 'qty' => 4, 'price' => 4.10],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-09-28', [
                ['product' => 'resma', 'qty' => 14, 'price' => 4.30],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-09-10', '2025-09-11', [
                ['product' => 'cafe', 'qty' => 10],
                ['product' => 'cremora', 'qty' => 20],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-09-25', '2025-09-26', [
                ['product' => 'sal', 'qty' => 25],
                ['product' => 'insumos_lab', 'qty' => 2],
            ], $observation);
        }

        // Oct 2025
        if ($monthNum === 10 && $y === 2025) {
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-10-05', [
                ['product' => 'azucar_1lb', 'qty' => 30, 'price' => 1.24],
                ['product' => 'sal', 'qty' => 90, 'price' => 0.17],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-10-12', [
                ['product' => 'resma', 'qty' => 15, 'price' => 4.35],
                ['product' => 'te_canela', 'qty' => 18, 'price' => 2.18],
            ]);
            $this->createPurchaseOrder('INSUMOS QUÍMICOS', '2025-10-18', [
                ['product' => 'insumos_lab', 'qty' => 8, 'price' => 6.20],
            ]);
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-10-25', [
                ['product' => 'cafe', 'qty' => 20, 'price' => 3.72],
                ['product' => 'te_negro', 'qty' => 15, 'price' => 2.08],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA LEGAL', '2025-10-08', '2025-10-09', [
                ['product' => 'resma', 'qty' => 2],
                ['product' => 'te_canela', 'qty' => 5],
            ], $observation);
            $this->createSupplyRequestAndDeliver('GERENCIA FINANCIERA', '2025-10-20', '2025-10-21', [
                ['product' => 'azucar_1lb', 'qty' => 8],
                ['product' => 'te_verde', 'qty' => 5],
            ], $observation);
        }

        // Nov 2025
        if ($monthNum === 11 && $y === 2025) {
            $this->createPurchaseOrder('TECNOLOGÍA Y SUMINISTROS', '2025-11-05', [
                ['product' => 'material_informatico', 'qty' => 5, 'price' => 14.80],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-11-12', [
                ['product' => 'cremora', 'qty' => 50, 'price' => 0.27],
                ['product' => 'te_menta', 'qty' => 14, 'price' => 2.15],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2025-11-18', [
                ['product' => 'papel_aluminio', 'qty' => 6, 'price' => 8.40],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-11-25', [
                ['product' => 'resma', 'qty' => 12, 'price' => 4.40],
                ['product' => 'te_verde', 'qty' => 12, 'price' => 2.12],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2025-11-10', '2025-11-11', [
                ['product' => 'cafe', 'qty' => 8],
                ['product' => 'cremora', 'qty' => 25],
                ['product' => 'papel_aluminio', 'qty' => 2],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2025-11-22', '2025-11-23', [
                ['product' => 'sal', 'qty' => 20],
                ['product' => 'insumos_lab', 'qty' => 2],
            ], $observation);
        }

        // Dic 2025
        if ($monthNum === 12 && $y === 2025) {
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2025-12-05', [
                ['product' => 'cafe', 'qty' => 28, 'price' => 3.75],
                ['product' => 'te_canela', 'qty' => 20, 'price' => 2.20],
            ]);
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2025-12-12', [
                ['product' => 'azucar_1lb', 'qty' => 40, 'price' => 1.26],
                ['product' => 'azucar_splenda', 'qty' => 5, 'price' => 4.15],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2025-12-18', [
                ['product' => 'resma', 'qty' => 18, 'price' => 4.45],
            ]);
            $this->createPurchaseOrder('INSUMOS QUÍMICOS', '2025-12-28', [
                ['product' => 'insumos_lab', 'qty' => 10, 'price' => 6.00],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA FINANCIERA', '2025-12-10', '2025-12-11', [
                ['product' => 'resma', 'qty' => 4],
                ['product' => 'te_canela', 'qty' => 6],
            ], $observation);
            $this->createSupplyRequestAndDeliver('UNIDAD DE TECNOLOGIA', '2025-12-18', '2025-12-19', [
                ['product' => 'material_informatico', 'qty' => 2],
            ], $observation);
        }

        // Ene 2026
        if ($monthNum === 1 && $y === 2026) {
            $this->createPurchaseOrder('PROVEEDORA DE ALIMENTOS', '2026-01-05', [
                ['product' => 'cafe', 'qty' => 24, 'price' => 3.78],
                ['product' => 'cremora', 'qty' => 45, 'price' => 0.28],
                ['product' => 'sal', 'qty' => 100, 'price' => 0.18],
            ]);
            $this->createPurchaseOrder('SUMINISTROS DE OFICINA', '2026-01-12', [
                ['product' => 'resma', 'qty' => 15, 'price' => 4.50],
                ['product' => 'te_negro', 'qty' => 15, 'price' => 2.22],
            ]);
            $this->createPurchaseOrder('DISTRIBUIDORA DE PAPEL', '2026-01-18', [
                ['product' => 'papel_aluminio', 'qty' => 5, 'price' => 8.50],
            ]);
            $this->createPurchaseOrder('PROVEEDORA NACIONAL DE CAFÉ', '2026-01-25', [
                ['product' => 'te_verde', 'qty' => 18, 'price' => 2.15],
                ['product' => 'te_manzanilla', 'qty' => 12, 'price' => 2.28],
            ]);

            $this->createSupplyRequestAndDeliver('GERENCIA ADMINISTRATIVA', '2026-01-15', '2026-01-16', [
                ['product' => 'cafe', 'qty' => 10],
                ['product' => 'cremora', 'qty' => 20],
                ['product' => 'resma', 'qty' => 3],
            ], $observation);
            $this->createSupplyRequestAndDeliver('LABORATORIO', '2026-01-22', '2026-01-23', [
                ['product' => 'sal', 'qty' => 30],
                ['product' => 'insumos_lab', 'qty' => 2],
            ], $observation);
        }
    }

    protected function createSupplyRequestAndDeliver(
        string $officeNamePart,
        string $requestDate,
        string $deliveryDate,
        array $details,
        string $observation = 'Plan Feb–Jun 2025'
    ): void {
        $office = $this->getOffice($officeNamePart);
        if (!$office) {
            $this->errors[] = "Oficina no encontrada: {$officeNamePart}";
            return;
        }

        $userId = User::first()?->id;
        if (!$userId) {
            $this->errors[] = 'No hay usuarios en el sistema.';
            return;
        }

        $request = SupplyRequest::create([
            'date' => $requestDate,
            'delivery_date' => null,
            'observation' => $observation,
            'requester_id' => $userId,
            'office_id' => $office->id,
            'immediate_boss_id' => $userId,
            'status_id' => 1,
        ]);

        $detailCount = 0;
        foreach ($details as $d) {
            $product = $this->resolveProduct($d['product']);
            if (!$product) {
                $this->errors[] = "Producto no encontrado para solicitud: {$d['product']}";
                continue;
            }
            SupplyRequestDetail::create([
                'supply_request_id' => $request->id,
                'product_id' => $product->id,
                'quantity' => $d['qty'],
                'delivered_quantity' => $d['qty'],
            ]);
            $detailCount++;
        }

        if ($detailCount === 0) {
            $this->errors[] = "Solicitud {$officeNamePart} ({$requestDate}): no se agregó ningún detalle (productos no encontrados).";
            return;
        }

        $request->status_id = 2;
        $request->save();
        $request->approved_by_id = $userId;
        $request->status_id = 3;
        $request->save();

        $this->deliverRequest($request->id, $deliveryDate . ' 14:00:00');
    }
}
