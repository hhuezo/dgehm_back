<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecificsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specifics = [
            ['code' => '0101', 'name' => 'Mobiliario', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '0102', 'name' => 'Maquinaria y equipo', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '0103', 'name' => 'Equipo médico y de laboratorio', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '0104', 'name' => 'Equipo informático', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '0105', 'name' => 'Equipo de transporte', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '0199', 'name' => 'Diversos', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '0403', 'name' => 'Derechos de propiedad intelectual', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_specifics')->insert($specifics);
    }
}
