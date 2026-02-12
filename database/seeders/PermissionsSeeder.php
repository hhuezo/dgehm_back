<?php

namespace Database\Seeders;

use App\Models\PermissionType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $types = [
            'Security'    => PermissionType::firstOrCreate(['name' => 'Security'], ['is_active' => true]),
            'Warehouse'   => PermissionType::firstOrCreate(['name' => 'Warehouse'], ['is_active' => true]),
            'Fixed Asset' => PermissionType::firstOrCreate(['name' => 'Fixed Asset'], ['is_active' => true]),
        ];

        $permissionsByType = [
            'Security' => [
                'permissions view',
                'permissions create',
                'permissions update',
                'permissions delete',
                'permission_type view',
                'permission_type create',
                'permission_type show',
                'permission_type update',
                'permission_type delete',
                'roles view',
                'roles create',
                'roles update',
                'roles show',
                'users view',
                'users create',
                'users show',
                'users update',
                'users delete',
            ],
            'Warehouse' => [
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
            ],
            'Fixed Asset' => [
                'classes view',
                'classes create',
                'classes update',
                'classes delete',
                'institutions view',
                'institutions create',
                'institutions update',
                'institutions delete',
                'categories view',
                'categories create',
                'categories update',
                'categories delete',
                'organizational_unit_types view',
                'organizational_unit_types create',
                'organizational_unit_types update',
                'organizational_unit_types delete',
                'organizational_units view',
                'organizational_units create',
                'organizational_units update',
                'organizational_units delete',
                'organizational_units assign-parent',
                'organizational_units tree',
                'origins view',
                'origins create',
                'origins update',
                'origins delete',
                'physical_conditions view',
                'physical_conditions create',
                'physical_conditions update',
                'physical_conditions delete',
                'specifics view',
                'specifics create',
                'specifics update',
                'specifics delete',
                'vehicle_brands view',
                'vehicle_brands create',
                'vehicle_brands update',
                'vehicle_brands delete',
                'vehicle_colors view',
                'vehicle_colors create',
                'vehicle_colors update',
                'vehicle_colors delete',
                'vehicle_drive_types view',
                'vehicle_drive_types create',
                'vehicle_drive_types update',
                'vehicle_drive_types delete',
                'vehicle_types view',
                'vehicle_types create',
                'vehicle_types update',
                'vehicle_types delete',
                'fixed_assets view',
                'fixed_assets create',
                'fixed_assets update',
                'fixed_assets delete',
                'fixed_assets import',
            ],
        ];

        foreach ($permissionsByType as $typeName => $permissionNames) {
            $typeId = $types[$typeName]->id;
            foreach ($permissionNames as $name) {
                $p = Permission::firstOrCreate(
                    ['name' => $name],
                    ['guard_name' => 'web']
                );
                \Illuminate\Support\Facades\DB::table('permissions')
                    ->where('id', $p->id)
                    ->update(['permission_type_id' => $typeId]);
            }
        }

        // El admin tiene TODOS los permisos
        $allPermissions = Permission::pluck('name')->toArray();
        $adminRole = Role::Where('name', 'admin')->first();
        $adminRole->syncPermissions($allPermissions);
    }
}
