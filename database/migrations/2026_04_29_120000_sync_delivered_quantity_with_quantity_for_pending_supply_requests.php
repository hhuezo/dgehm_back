<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historial: las líneas en solicitudes pendientes o enviadas podían tener delivered_quantity en 0.
 * La regla de negocio es: al crear, entregado = solicitado; hasta aprobar no divergen.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wh_supply_request_detail') || ! Schema::hasTable('wh_supply_request')) {
            return;
        }

        DB::statement('
            UPDATE wh_supply_request_detail d
            INNER JOIN wh_supply_request r ON r.id = d.supply_request_id
            SET d.delivered_quantity = d.quantity
            WHERE r.status_id IN (1, 2)
              AND (d.delivered_quantity IS NULL OR d.delivered_quantity = 0)
        ');
    }

    public function down(): void
    {
        // Irreversible sin snapshot
    }
};
