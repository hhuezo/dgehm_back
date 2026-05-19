<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhysicalConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            ['name' => 'EN BUEN ESTADO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EN MAL ESTADO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EN MAL ESTADO POR VIDA ÚTIL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EN MALAS CONDICIONES', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EN MALAS CONDICIONES POR VIDA ÚTIL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'OBSOLETO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_physical_conditions')->insert($conditions);
    }
}
