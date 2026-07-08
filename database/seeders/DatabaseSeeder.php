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
        // Permisos y roles (invocar primero)
        // -----------------------------------------------------
        $this->call(PermissionsSeeder::class);
        $this->call(RolesPermissionsSeeder::class);

        // -----------------------------------------------------
        // Catálogo administrativo: géneros y estado civil (adm_*)
        // -----------------------------------------------------
        $this->call(AdmGendersMaritalStatusesSeeder::class);
        $this->call(AdmDocumentTypesSeeder::class);
        $this->call(AdmFunctionalPositionsSeeder::class);

        // -----------------------------------------------------
        // Unidades organizativas
        // -----------------------------------------------------
        $this->call(OrganizationalUnitsSeeder::class);

        // -----------------------------------------------------
        // Instituciones (fa_institutions)
        // -----------------------------------------------------
        $this->call(InstitutionsSeeder::class);

        // -----------------------------------------------------
        // Específicos (fa_specifics)
        // -----------------------------------------------------
        $this->call(SpecificsSeeder::class);

        // -----------------------------------------------------
        // Categorías (fa_categories)
        // -----------------------------------------------------
        $this->call(ClassesSeeder::class);

        // -----------------------------------------------------
        // Orígenes (fa_origins)
        // -----------------------------------------------------
        $this->call(OriginsSeeder::class);

        // -----------------------------------------------------
        // Condiciones físicas (fa_physical_conditions)
        // -----------------------------------------------------
        $this->call(PhysicalConditionsSeeder::class);

        // -----------------------------------------------------
        // Marcas de vehículos (fa_vehicle_brands)
        // -----------------------------------------------------
        $this->call(VehicleBrandsSeeder::class);

        // -----------------------------------------------------
        // Tipos de vehículos (fa_vehicle_types)
        // -----------------------------------------------------
        $this->call(VehicleTypesSeeder::class);

        // -----------------------------------------------------
        // Tipos de tracción de vehículos (fa_vehicle_drive_types)
        // -----------------------------------------------------
        $this->call(VehicleDriveTypesSeeder::class);

        // -----------------------------------------------------
        // Colores de vehículos (fa_vehicle_colors)
        // -----------------------------------------------------
        $this->call(VehicleColorsSeeder::class);

        // -----------------------------------------------------
        // 1. CUENTAS CONTABLES (wh_accounting_accounts)
        // -----------------------------------------------------
        $accounts = [
            ['code' => '54101', 'name' => 'PRODUCTOS ALIMENTICIOS PARA PERSONAS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54105', 'name' => 'PRODUCTOS DE PAPEL Y CARTON', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54107', 'name' => 'PRODUCTOS QUIMICOS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54114', 'name' => 'MATERIALES DE OFICINA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54115', 'name' => 'MATERIALES INFORMATICOS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54119', 'name' => 'MATERIALES ELECTRICOS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '54199', 'name' => 'BIENES DE USO Y CONSUMO DIVERSOS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('wh_accounting_accounts')->upsert(
            $accounts,
            ['code'],
            ['name', 'is_active', 'updated_at']
        );

        // -----------------------------------------------------
        // 2. UNIDADES DE MEDIDA (wh_measures)
        // -----------------------------------------------------
        $units = [
            ['name' => 'Unidad', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kilogramo', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gramo', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Litro', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Metro', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Caja', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Resma', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Galón', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paquete', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Libra', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bolsa', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('wh_measures')->upsert(
            $units,
            ['name'],
            ['is_active', 'updated_at']
        );

        // -----------------------------------------------------
        // 3. PROVEEDORES (wh_suppliers)
        // -----------------------------------------------------
        $suppliers = [
            [
                'name' => 'PROVEEDORA DE ALIMENTOS EL SALVADOR S.A. DE C.V.',
                'contact_person' => 'Juan Pérez',
                'phone' => '2255-0000',
                'email' => 'ventas@proveedora.com',
                'address' => 'Avenida Principal, San Salvador',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SUMINISTROS DE OFICINA CENTRAL S.A. DE C.V.',
                'contact_person' => 'Maria López',
                'phone' => '2266-1111',
                'email' => 'info@suministros.com',
                'address' => 'Calle Secundaria, Santa Ana',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('wh_suppliers')->upsert(
            $suppliers,
            ['email'],
            ['name', 'contact_person', 'phone', 'address', 'is_active', 'updated_at']
        );

        // -----------------------------------------------------
        // 4. FUENTES DE FINANCIAMIENTO (wh_funding_sources)
        // -----------------------------------------------------
        $this->call(FundingSourcesSeeder::class);

        // -----------------------------------------------------
        // 5. PRODUCTOS/INSUMOS (wh_products)
        // -----------------------------------------------------
        $cuentaId = DB::table('wh_accounting_accounts')->where('code', '54101')->value('id');
        $cuentaId = $cuentaId ?: 1;

        $measureIds = DB::table('wh_measures')->pluck('id')->all();
        $defaultMeasureId = $measureIds[0] ?? 1;

        $productNames = [
            'AZUCAR DE 1 LIBRA DEL CAÑAL',
            'AZUCAR BLANCA GRANULADA EN SOBRE BOLSA DE 1000 UNIDADES',
            'CAFE MOLIDO MAJADA ORO, BOLSA DE 1 LIBRA CON EMPAQUE METALIZADO CON VALVULA',
            'CREMORA EN SOBRE BOLSA DE 200 UNIDADES, MARCA CREMAFE',
            'PAPEL DE ALUMINIO ROLLO DE 500 PIES',
            'SAL EN SOBRE BOLSA DE 500 UNIDADES SOBRE DE 0.39 GRS. MARCA CODIPA',
            'SOBRE DE AZUCAR DIETETICA, CAJA DE 200 UNIDADES',
            'TE DE CANELA CAJA DE 25 SOBRE DE 1.3GRS. MARCA MANZATE',
            'TE DE MANZANA CANELA. CAJA DE 20 UNIDADES MARCA SELECTOS',
            'TE DE MANZANILLA CANELA. CAJA DE 20 UNIDADES MARCA MC CORMICK',
            'TE DE MENTA CAJA DE 25 SOBRE DE 1.3GRS. MARCA MANZATE',
            'TE NEGRO CAJA DE 25 SOBRE DE 1.8GRS. MARCA MANZATE',
            'TE VERDE CAJA DE 25 SOBRE DE 1.3GRS. MARCA MANZATE',
            'SOBRE DE AZUCAR SPLENDA (CAJA DE 100 UNIDADES)',
            'BOLSAS DE CAFÉ TOSTADO Y MOLIDO',
            'BOLSAS DE AZUCAR BLANCA DE 500G',
        ];

        $products = array_map(function (string $name) use ($cuentaId, $measureIds, $defaultMeasureId) {
            $measureId = !empty($measureIds)
                ? $measureIds[array_rand($measureIds)]
                : $defaultMeasureId;

            return [
                'name' => $name,
                'accounting_account_id' => $cuentaId,
                'measure_id' => $measureId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $productNames);

        if (!DB::table('wh_products')->exists()) {
            DB::table('wh_products')->insert($products);
        }

        // -----------------------------------------------------
        // 6. ORDENES DE COMPRA (purchase_order)
        // No se insertan datos, ya que es una tabla transaccional.
        // -----------------------------------------------------

        // =====================
        // ROLES (ya creados por RolesPermissionsSeeder, pero los obtenemos)
        // =====================
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $almacenAdminRole = Role::firstOrCreate([
            'name' => 'almacen-admin',
            'guard_name' => 'web',
        ]);

        $almacenSolicitanteRole = Role::firstOrCreate([
            'name' => 'almacen-solicitante',
            'guard_name' => 'web',
        ]);

        $almacenJefeAreaRole = Role::firstOrCreate([
            'name' => 'almacen-jefe-area',
            'guard_name' => 'web',
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


        // =========================
        // USUARIO SOLICITANTE
        // =========================
        $solicitanteUser = User::firstOrCreate(
            ['email' => 'solicitante@mail.com'],
            [
                'name' => 'Maria',
                'lastname' => 'Solicitante',
                'username' => 'solicitante',
                'password' => Hash::make('password123'),
            ]
        );

        $solicitanteUser->assignRole('almacen-solicitante');

        // =========================
        // USUARIO JEFE DE ÁREA
        // =========================
        $jefeAreaUser = User::firstOrCreate(
            ['email' => 'jefe.area@mail.com'],
            [
                'name' => 'Pedro',
                'lastname' => 'Jefe',
                'username' => 'jefe.area',
                'password' => Hash::make('password123'),
            ]
        );

        $jefeAreaUser->assignRole('almacen-jefe-area');

        // -----------------------------------------------------
        // Empleado administrador (adm_employees), ligado al user admin
        // -----------------------------------------------------
        $this->call(AdmEmployeesSeeder::class);
        $this->call(AdmEmployeeRelationsSeeder::class);
    }
}
