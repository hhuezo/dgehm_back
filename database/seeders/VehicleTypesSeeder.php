<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'PICK UP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SEDAN', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CAMIONETA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MICROBUS', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'AUTOMÓVIL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_vehicle_types')->insert($types);
    }
}
