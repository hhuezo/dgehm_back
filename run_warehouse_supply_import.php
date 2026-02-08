<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\WarehouseSupplyRequestImportService;

$service = new WarehouseSupplyRequestImportService();
$service->import(storage_path('app/almacen2.xlsx'));

echo "Solicitudes creadas: {$service->requestsCreated}\n";
echo "Omitidas: {$service->skipped}\n";
echo "Errores: " . count($service->errors) . "\n";
foreach (array_slice($service->errors, 0, 15) as $e) echo "  - {$e}\n";
if (count($service->errors) > 15) echo "  ... y " . (count($service->errors) - 15) . " más\n";
