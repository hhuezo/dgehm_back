<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PlanEntradasSalidasService;
use Illuminate\Support\Facades\DB;

$fresh = in_array('--fresh', $argv ?? [], true);
if ($fresh) {
    echo "Eliminando datos previos del plan (OC 0002+ y solicitudes del plan)...\n";
    $ocIds = DB::table('wh_purchase_order')->whereRaw('CAST(order_number AS UNSIGNED) >= 2')->pluck('id')->toArray();
    if (!empty($ocIds)) {
        DB::table('wh_kardex')->whereIn('purchase_order_id', $ocIds)->delete();
        DB::table('wh_purchase_order')->whereIn('id', $ocIds)->delete();
    }
    $requestIds = DB::table('wh_supply_request')->where('observation', 'like', 'Plan %')->pluck('id')->toArray();
    if (!empty($requestIds)) {
        DB::table('wh_kardex')->whereIn('supply_request_id', $requestIds)->delete();
        DB::table('wh_supply_request_detail')->whereIn('supply_request_id', $requestIds)->delete();
        DB::table('wh_supply_request')->whereIn('id', $requestIds)->delete();
    }
    echo "Datos eliminados.\n";
}

$service = new PlanEntradasSalidasService();
$service->run();

echo "--- Log ---\n";
foreach ($service->log as $line) {
    echo $line . "\n";
}

echo "\n--- Errores (" . count($service->errors) . ") ---\n";
foreach ($service->errors as $e) {
    echo "  - " . $e . "\n";
}

echo "\nListo.\n";
