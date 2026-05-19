<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmGendersMaritalStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('adm_genders')->upsert(
            [
                ['id' => 1, 'name' => 'Masculino', 'active' => true, 'created_at' => '2023-05-05 20:58:20', 'updated_at' => '2023-05-05 20:58:20'],
                ['id' => 2, 'name' => 'Femenino', 'active' => true, 'created_at' => '2023-05-05 20:58:20', 'updated_at' => '2023-05-05 20:58:20'],
                ['id' => 3, 'name' => 'Gei', 'active' => true, 'created_at' => '2025-09-01 20:50:09', 'updated_at' => '2025-09-01 20:50:09'],
            ],
            ['id'],
            ['name', 'active', 'updated_at']
        );

        DB::table('adm_marital_statuses')->upsert(
            [
                ['id' => 1, 'name' => 'Soltero/a', 'active' => true, 'created_at' => '2023-01-03 06:00:00', 'updated_at' => '2023-01-03 06:00:00'],
                ['id' => 2, 'name' => 'Casado/a', 'active' => true, 'created_at' => '2023-01-03 06:00:00', 'updated_at' => '2023-01-03 06:00:00'],
                ['id' => 3, 'name' => 'Divorciado/a', 'active' => true, 'created_at' => '2023-01-03 06:00:00', 'updated_at' => '2023-01-03 06:00:00'],
                ['id' => 4, 'name' => 'Viudo/a', 'active' => true, 'created_at' => '2023-01-03 06:00:00', 'updated_at' => '2023-01-03 06:00:00'],
                ['id' => 5, 'name' => 'Union Libre', 'active' => true, 'created_at' => '2023-01-03 06:00:00', 'updated_at' => '2023-01-03 06:00:00'],
            ],
            ['id'],
            ['name', 'active', 'updated_at']
        );
    }
}
