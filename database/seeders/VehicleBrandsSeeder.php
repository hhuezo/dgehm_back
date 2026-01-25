<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleBrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'MITSUBISHI', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TOYOTA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NISSAN', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ISUZU', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAZDA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_vehicle_brands')->insert($brands);
    }
}
