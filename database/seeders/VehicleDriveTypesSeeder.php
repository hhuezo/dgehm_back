<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleDriveTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => '4X4', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SENCILLA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DOBLE CABINA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_vehicle_drive_types')->insert($types);
    }
}
