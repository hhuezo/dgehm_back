<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmFunctionalPositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('adm_organizational_unit_types')->upsert(
            [
                [
                    'id' => 1,
                    'name' => 'Dirección',
                    'staff' => false,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['id'],
            ['name', 'staff', 'active', 'updated_at']
        );

        DB::table('adm_organizational_units')->upsert(
            [
                [
                    'id' => 1,
                    'name' => 'Dirección General',
                    'abbreviation' => 'DG',
                    'code' => 'DG001',
                    'active' => true,
                    'adm_organizational_unit_type_id' => 1,
                    'adm_organizational_unit_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['id'],
            ['name', 'abbreviation', 'code', 'active', 'adm_organizational_unit_type_id', 'adm_organizational_unit_id', 'updated_at']
        );

        $positions = [
            [
                'id' => 1,
                'name' => 'Administrador',
                'abbreviation' => 'ADMIN',
                'description' => 'Administrador del sistema',
                'amount_required' => 1,
                'salary_min' => 0,
                'salary_max' => 0,
                'boss' => true,
                'boss_hierarchy' => 1,
                'original' => 1,
                'user_required' => 1,
                'active' => true,
                'adm_organizational_unit_id' => 1,
                'adm_functional_position_id' => null,
            ],
            [
                'id' => 2,
                'name' => 'Jefe de área',
                'abbreviation' => 'JEFE',
                'description' => 'Responsable de unidad organizativa',
                'amount_required' => 1,
                'salary_min' => 800,
                'salary_max' => 1500,
                'boss' => true,
                'boss_hierarchy' => 2,
                'original' => 1,
                'user_required' => 1,
                'active' => true,
                'adm_organizational_unit_id' => 1,
                'adm_functional_position_id' => null,
            ],
            [
                'id' => 3,
                'name' => 'Analista',
                'abbreviation' => 'ANAL',
                'description' => 'Analista administrativo',
                'amount_required' => 3,
                'salary_min' => 500,
                'salary_max' => 900,
                'boss' => false,
                'boss_hierarchy' => 0,
                'original' => 1,
                'user_required' => 0,
                'active' => true,
                'adm_organizational_unit_id' => 1,
                'adm_functional_position_id' => null,
            ],
            [
                'id' => 4,
                'name' => 'Asistente administrativo',
                'abbreviation' => 'ASIST',
                'description' => 'Apoyo administrativo general',
                'amount_required' => 5,
                'salary_min' => 400,
                'salary_max' => 700,
                'boss' => false,
                'boss_hierarchy' => 0,
                'original' => 1,
                'user_required' => 0,
                'active' => true,
                'adm_organizational_unit_id' => 1,
                'adm_functional_position_id' => null,
            ],
            [
                'id' => 5,
                'name' => 'Encargado de almacén',
                'abbreviation' => 'ALM',
                'description' => 'Gestión de inventario y bodega',
                'amount_required' => 2,
                'salary_min' => 450,
                'salary_max' => 800,
                'boss' => false,
                'boss_hierarchy' => 0,
                'original' => 1,
                'user_required' => 0,
                'active' => true,
                'adm_organizational_unit_id' => 1,
                'adm_functional_position_id' => null,
            ],
        ];

        foreach ($positions as &$position) {
            $position['created_at'] = $now;
            $position['updated_at'] = $now;
        }
        unset($position);

        DB::table('adm_functional_positions')->upsert(
            $positions,
            ['id'],
            [
                'name', 'abbreviation', 'description', 'amount_required', 'salary_min', 'salary_max',
                'boss', 'boss_hierarchy', 'original', 'user_required', 'active',
                'adm_organizational_unit_id', 'adm_functional_position_id', 'updated_at',
            ]
        );
    }
}
