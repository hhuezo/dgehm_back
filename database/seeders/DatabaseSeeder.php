<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // -----------------------------------------------------
        // 1. CUENTAS CONTABLES (wh_accounting_accounts)
        // -----------------------------------------------------
        DB::table('wh_accounting_accounts')->delete();

        $accounts = [
            ['code' => '54101', 'name' => 'PRODUCTOS ALIMENTICIOS PARA PERSONAS', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54105', 'name' => 'PRODUCTOS DE PAPEL Y CARTON', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54107', 'name' => 'PRODUCTOS QUIMICOS', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54114', 'name' => 'MATERIALES DE OFICINA', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54115', 'name' => 'MATERIALES INFORMATICOS', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54119', 'name' => 'MATERIALES ELECTRICOS', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54199', 'name' => 'BIENES DE USO Y CONSUMO DIVERSOS', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('wh_accounting_accounts')->insert($accounts);

        // -----------------------------------------------------
        // 2. UNIDADES DE MEDIDA (wh_measures) - ¡SOLO 'description'!
        // Los IDs se usarán para relacionar productos.
        // -----------------------------------------------------
        DB::table('wh_measures')->delete();

        $units = [
            // IDs fijos: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11
            ['name' => 'Unidad', 'created_at' => now(), 'updated_at' => now()], // ID 1
            ['name' => 'Kilogramo', 'created_at' => now(), 'updated_at' => now()], // ID 2
            ['name' => 'Gramo', 'created_at' => now(), 'updated_at' => now()], // ID 3
            ['name' => 'Litro', 'created_at' => now(), 'updated_at' => now()], // ID 4
            ['name' => 'Metro', 'created_at' => now(), 'updated_at' => now()], // ID 5
            ['name' => 'Caja', 'created_at' => now(), 'updated_at' => now()], // ID 6
            ['name' => 'Resma', 'created_at' => now(), 'updated_at' => now()], // ID 7
            ['name' => 'Galón', 'created_at' => now(), 'updated_at' => now()], // ID 8
            ['name' => 'Paquete', 'created_at' => now(), 'updated_at' => now()], // ID 9
            ['name' => 'Libra', 'created_at' => now(), 'updated_at' => now()], // ID 10
            ['name' => 'Bolsa', 'created_at' => now(), 'updated_at' => now()], // ID 11
        ];
        DB::table('wh_measures')->insert($units);

        // -----------------------------------------------------
        // 3. OFICINAS/DEPENDENCIAS (wh_offices) - USANDO 'name'
        // -----------------------------------------------------
        DB::table('wh_offices')->delete();

        $offices = [
            ['name' => 'GERENCIA ADMINISTRATIVA / SERVICIOS GENERALES', 'phone' => '2200-0001', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LABORATORIO DE ACAJUTLA', 'phone' => '2200-0002', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UNIDAD DE TECNOLOGIA E INFORMACION', 'phone' => '2200-0003', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GERENCIA FINANCIERA', 'phone' => '2200-0009', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GERENCIA LEGAL', 'phone' => '2200-0015', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('wh_offices')->insert($offices);

        // -----------------------------------------------------
        // 4. PROVEEDORES (wh_suppliers) - ¡CORREGIDO según tu migración!
        // -----------------------------------------------------
        DB::table('wh_suppliers')->delete();

        $suppliers = [
            [
                'name' => 'PROVEEDORA DE ALIMENTOS EL SALVADOR S.A. DE C.V.',
                'contact_person' => 'Juan Pérez',
                'phone' => '2255-0000',
                'email' => 'ventas@proveedora.com',
                'address' => 'Avenida Principal, San Salvador',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'SUMINISTROS DE OFICINA CENTRAL S.A. DE C.V.',
                'contact_person' => 'Maria López',
                'phone' => '2266-1111',
                'email' => 'info@suministros.com',
                'address' => 'Calle Secundaria, Santa Ana',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        DB::table('wh_suppliers')->insert($suppliers);


      // -----------------------------------------------------
        // 5. PRODUCTOS/INSUMOS (wh_products) - ¡SIN measure_id!
        // -----------------------------------------------------

        DB::table('wh_products')->delete();

        // Búsqueda de Cuenta Contable
        $cuentaId = DB::table('wh_accounting_accounts')->where('code', '54101')->value('id');
        $cuentaId = $cuentaId ?: 1;

        $products = [
            // Insertamos sin 'measure_id' para evitar el error 42S22.
            ['name' => 'AZUCAR DE 1 LIBRA DEL CAÑAL', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AZUCAR BLANCA GRANULADA EN SOBRE BOLSA DE 1000 UNIDADES', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CAFE MOLIDO MAJADA ORO, BOLSA DE 1 LIBRA CON EMPAQUE METALIZADO CON VALVULA', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CREMORA EN SOBRE BOLSA DE 200 UNIDADES, MARCA CREMAFE', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PAPEL DE ALUMINIO ROLLO DE 500 PIES', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SAL EN SOBRE BOLSA DE 500 UNIDADES SOBRE DE 0.39 GRS. MARCA CODIPA', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SOBRE DE AZUCAR DIETETICA, CAJA DE 200 UNIDADES', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TE DE CANELA CAJA DE 25 SOBRE DE 1.3GRS. MARCA MANZATE', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TE DE MANZANA CANELA. CAJA DE 20 UNIDADES MARCA SELECTOS', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TE DE MANZANILLA CANELA. CAJA DE 20 UNIDADES MARCA MC CORMICK', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TE DE MENTA CAJA DE 25 SOBRE DE 1.3GRS. MARCA MANZATE', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TE NEGRO CAJA DE 25 SOBRE DE 1.8GRS. MARCA MANZATE', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TE VERDE CAJA DE 25 SOBRE DE 1.3GRS. MARCA MANZATE', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SOBRE DE AZUCAR SPLENDA (CAJA DE 100 UNIDADES)', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BOLSAS DE CAFÉ TOSTADO Y MOLIDO', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BOLSAS DE AZUCAR BLANCA DE 500G', 'accounting_account_id' => $cuentaId,  'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('wh_products')->insert($products);

        // -----------------------------------------------------
        // 6. ORDENES DE COMPRA (purchase_order)
        // No se insertan datos, ya que es una tabla transaccional.
        // -----------------------------------------------------




       // =====================
        // ROLES
        // =====================
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
        ]);

        $almacenRole = Role::firstOrCreate([
            'name' => 'almacen-admin',
        ]);

        // =========================
        // USUARIO ADMIN
        // =========================
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'lastname' => 'Principal',
                'username' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        $adminUser->assignRole('admin');

        // =========================
        // USUARIO ALMACEN
        // =========================
        $almacenUser = User::firstOrCreate(
            ['email' => 'almacen@mail.com'],
            [
                'name' => 'Almacen',
                'lastname' => 'Admin',
                'username' => 'almacen',
                'password' => Hash::make('password123'),
            ]
        );

         $almacenUser->assignRole('almacen-admin');
    }
}
