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

        // Nombres alineados con PermissionsSeeder (p. ej. suppliers view), no wh.suppliers.view
        $almacenAdminPermissions = [
            'purchase_order view',
            'purchase_order create',
            'purchase_order show',
            'purchase_order update',
            'purchase_order report-acta',
            'purchase_order_detail show',
            'purchase_order_detail create',
            'purchase_order_detail update',
            'purchase_order_detail delete',

            'supply_request view',
            'supply_request create',
            'supply_request show',
            'supply_request approve',
            'supply_request finalize',
            'supply_request_detail show',
            'supply_request_detail create',
            'supply_request_detail update',
            'supply_request_detail delete',

            'accounting_account view',
            'accounting_account create',
            'accounting_account update',
            'accounting_account delete',
            'measures view',
            'measures create',
            'measures update',
            'measures delete',
            'products view',
            'products create',
            'products update',
            'products delete',
            'products kardex',
            'offices view',
            'offices create',
            'offices update',
            'offices delete',
            'suppliers view',
            'suppliers create',
            'suppliers update',
            'suppliers delete',
        ];

        $almacenAdminRole->syncPermissions($almacenAdminPermissions);
        $this->command->info('Permisos asignados al rol: almacen-admin (' . count($almacenAdminPermissions) . ' permisos)');

        // ============================================
        // ROL: ALMACEN-SOLICITANTE
        // ============================================
        $almacenSolicitanteRole = Role::firstOrCreate(['name' => 'almacen-solicitante', 'guard_name' => 'web']);

        $almacenSolicitantePermissions = [
            'supply_request view',
            'supply_request create',
            'supply_request show',
            'supply_request_detail show',
            'supply_request_detail create',
            'supply_request_detail update',
            'supply_request_detail delete',
        ];

        $almacenSolicitanteRole->syncPermissions($almacenSolicitantePermissions);
        $this->command->info('Permisos asignados al rol: almacen-solicitante (' . count($almacenSolicitantePermissions) . ' permisos)');

        // ============================================
        // ROL: ALMACEN-JEFE-AREA
        // ============================================
        $almacenJefeAreaRole = Role::firstOrCreate(['name' => 'almacen-jefe-area', 'guard_name' => 'web']);

        $almacenJefeAreaPermissions = [
            'supply_request view',
            'supply_request create',
            'supply_request show',
            'supply_request approve',
            'supply_request_detail show',
            'supply_request_detail create',
            'supply_request_detail update',
            'supply_request_detail delete',
        ];

        $almacenJefeAreaRole->syncPermissions($almacenJefeAreaPermissions);
        $this->command->info('Permisos asignados al rol: almacen-jefe-area (' . count($almacenJefeAreaPermissions) . ' permisos)');

        $this->command->info('✅ Asignación de permisos a roles completada exitosamente.');
    }
}

