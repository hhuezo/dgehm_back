<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('fa_institutions')->upsert(
            [
                [
                    'name' => 'DIRECCIÓN GENERAL DE ENERGIA HODROCARBUROS Y MINAS',
                    'code' => '4123',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['code'],
            ['name', 'is_active', 'updated_at']
        );
    }
}
