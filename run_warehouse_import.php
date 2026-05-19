<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\WarehouseInventoryImportService;
use Illuminate\Support\Facades\DB;

// Truncar tablas
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DB::table('wh_kardex')->truncate();
DB::table('wh_purchase_order')->truncate();
DB::table('wh_supply_request_detail')->truncate();
DB::table('wh_supply_returns_detail')->truncate();
DB::table('wh_products')->truncate();
DB::table('wh_measures')->truncate();
DB::table('wh_suppliers')->truncate();
DB::table('wh_accounting_accounts')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "Tablas truncadas.\n";

$service = new WarehouseInventoryImportService();
$service->import(storage_path('app/almacen.xlsx'));

echo "Importados: {$service->imported}\n";
echo "Omitidos: {$service->skipped}\n";
echo "Orden de compra ID: {$service->purchaseOrderId}\n";
echo "Errores: " . count($service->errors) . "\n";
foreach (array_slice($service->errors, 0, 10) as $e) echo "  - {$e}\n";
