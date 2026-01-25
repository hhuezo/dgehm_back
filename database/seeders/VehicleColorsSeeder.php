<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => 'GRIS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GRIS CLARO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AZUL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ROJO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BLANCO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VERDE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PLATEADO METÁLICO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BEING', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_vehicle_colors')->insert($colors);
    }
}
