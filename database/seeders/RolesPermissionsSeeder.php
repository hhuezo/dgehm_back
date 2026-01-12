<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder para asignar permisos a los roles
 * 
 * Este seeder asigna los permisos correspondientes a cada rol del sistema.
 * Los permisos deben existir previamente (ejecutar PermissionsSeeder primero).
 */
class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // ROL: ADMIN
        // ============================================
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // El admin tiene TODOS los permisos
        $allPermissions = Permission::pluck('name')->toArray();
        $adminRole->syncPermissions($allPermissions);

        $this->command->info('Permisos asignados al rol: admin (' . count($allPermissions) . ' permisos)');

        // ============================================
        // ROL: ALMACEN-ADMIN
        // ============================================
        $almacenAdminRole = Role::firstOrCreate(['name' => 'almacen-admin', 'guard_name' => 'web']);

        $almacenAdminPermissions = [
            // Permisos de Warehouse - Purchase Orders
            'wh.purchase_order.view',
            'wh.purchase_order.create',
            'wh.purchase_order.show',
            'wh.purchase_order.update',
            'wh.purchase_order.report-acta',
            'wh.purchase_order_detail.show',
            'wh.purchase_order_detail.create',
            'wh.purchase_order_detail.update',
            'wh.purchase_order_detail.delete',

            // Permisos de Warehouse - Supply Requests
            'wh.supply_request.view',
            'wh.supply_request.create',
            'wh.supply_request.show',
            'wh.supply_request.approve',
            'wh.supply_request.finalize',
            'wh.supply_request_detail.show',
            'wh.supply_request_detail.create',
            'wh.supply_request_detail.update',
            'wh.supply_request_detail.delete',

            // Permisos de Warehouse - Catálogos
            'wh.accounting_account.view',
            'wh.accounting_account.create',
            'wh.accounting_account.update',
            'wh.accounting_account.delete',
            'wh.measures.view',
            'wh.measures.create',
            'wh.measures.update',
            'wh.measures.delete',
            'wh.products.view',
            'wh.products.create',
            'wh.products.update',
            'wh.products.delete',
            'wh.products.kardex',
            'wh.offices.view',
            'wh.offices.create',
            'wh.offices.update',
            'wh.offices.delete',
            'wh.suppliers.view',
            'wh.suppliers.create',
            'wh.suppliers.update',
            'wh.suppliers.delete',
        ];

        $almacenAdminRole->syncPermissions($almacenAdminPermissions);
        $this->command->info('Permisos asignados al rol: almacen-admin (' . count($almacenAdminPermissions) . ' permisos)');

        // ============================================
        // ROL: ALMACEN-SOLICITANTE
        // ============================================
        $almacenSolicitanteRole = Role::firstOrCreate(['name' => 'almacen-solicitante', 'guard_name' => 'web']);

        $almacenSolicitantePermissions = [
            // Solo puede ver y crear solicitudes de suministros
            'wh.supply_request.view',
            'wh.supply_request.create',
            'wh.supply_request.show',
            'wh.supply_request_detail.show',
            'wh.supply_request_detail.create',
            'wh.supply_request_detail.update',
            'wh.supply_request_detail.delete',
        ];

        $almacenSolicitanteRole->syncPermissions($almacenSolicitantePermissions);
        $this->command->info('Permisos asignados al rol: almacen-solicitante (' . count($almacenSolicitantePermissions) . ' permisos)');

        // ============================================
        // ROL: ALMACEN-JEFE-AREA
        // ============================================
        $almacenJefeAreaRole = Role::firstOrCreate(['name' => 'almacen-jefe-area', 'guard_name' => 'web']);

        $almacenJefeAreaPermissions = [
            // Puede ver y crear solicitudes, y aprobarlas
            'wh.supply_request.view',
            'wh.supply_request.create',
            'wh.supply_request.show',
            'wh.supply_request.approve', // Puede aprobar
            'wh.supply_request_detail.show',
            'wh.supply_request_detail.create',
            'wh.supply_request_detail.update',
            'wh.supply_request_detail.delete',
        ];

        $almacenJefeAreaRole->syncPermissions($almacenJefeAreaPermissions);
        $this->command->info('Permisos asignados al rol: almacen-jefe-area (' . count($almacenJefeAreaPermissions) . ' permisos)');

        $this->command->info('✅ Asignación de permisos a roles completada exitosamente.');
    }
}

