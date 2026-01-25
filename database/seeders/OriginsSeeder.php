<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OriginsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $origins = [
            ['name' => 'CNE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DHM (ACTA)', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DHM (ACTA 2)', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'RP-LAGEO', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DGHM-CEL', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cooperación alemana', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fa_origins')->insert($origins);
    }
}
