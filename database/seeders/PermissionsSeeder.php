<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | PERMISSIONS
            |--------------------------------------------------------------------------
            */
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',

            /*
            |--------------------------------------------------------------------------
            | ROLES
            |--------------------------------------------------------------------------
            */
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.show',

            /*
            |--------------------------------------------------------------------------
            | USERS
            |--------------------------------------------------------------------------
            */
            'users.view',
            'users.create',
            'users.show',
            'users.update',
            'users.delete',

            /*
|--------------------------------------------------------------------------
| PURCHASE ORDERS
|--------------------------------------------------------------------------
*/
            'wh.purchase_order.view',
            'wh.purchase_order.create',
            'wh.purchase_order.show',
            'wh.purchase_order.update',
            'wh.purchase_order.report-acta',

            /*
|--------------------------------------------------------------------------
| PURCHASE ORDER DETAILS
|--------------------------------------------------------------------------
*/
            'wh.purchase_order_detail.show',
            'wh.purchase_order_detail.create',
            'wh.purchase_order_detail.update',
            'wh.purchase_order_detail.delete',

            /*
|--------------------------------------------------------------------------
| SUPPLY REQUESTS
|--------------------------------------------------------------------------
*/
            'wh.supply_request.view',
            'wh.supply_request.create',
            'wh.supply_request.show',
            'wh.supply_request.approve',
            'wh.supply_request.finalize',

            /*
|--------------------------------------------------------------------------
| SUPPLY REQUEST DETAILS
|--------------------------------------------------------------------------
*/
            'wh.supply_request_detail.show',
            'wh.supply_request_detail.create',
            'wh.supply_request_detail.update',
            'wh.supply_request_detail.delete',

            /*
|--------------------------------------------------------------------------
| CATALOGS
|--------------------------------------------------------------------------
*/
            // Accounting Accounts
            'wh.accounting_account.view',
            'wh.accounting_account.create',
            'wh.accounting_account.update',
            'wh.accounting_account.delete',

            // Measures
            'wh.measures.view',
            'wh.measures.create',
            'wh.measures.update',
            'wh.measures.delete',

            // Products
            'wh.products.view',
            'wh.products.create',
            'wh.products.update',
            'wh.products.delete',
            'wh.products.kardex',

            // Offices
            'wh.offices.view',
            'wh.offices.create',
            'wh.offices.update',
            'wh.offices.delete',

            // Suppliers
            'wh.suppliers.view',
            'wh.suppliers.create',
            'wh.suppliers.update',
            'wh.suppliers.delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
