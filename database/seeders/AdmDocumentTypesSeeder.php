<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmDocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            ['id' => 1, 'name' => 'DUI', 'format' => '00000000-0', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'NIT', 'format' => '0000-000000-000-0', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'NUP', 'format' => '000000000000', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'ISSS', 'format' => '000000000', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Licencia de Conducir', 'format' => '0000-000000-000-0', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Hacienda ID', 'format' => '00000000000000', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Código de Empleado', 'format' => '0000000', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('adm_document_types')->upsert(
            $rows,
            ['id'],
            ['name', 'format', 'active', 'updated_at']
        );
    }
}
