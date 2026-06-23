<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundingSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'name'       => 'PFondos GOES',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Fondos Convenios',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Fondos Recursos Propios',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Fondos La GEO',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('wh_funding_sources')->upsert(
            $rows,
            ['name'],
            ['is_active', 'updated_at']
        );
    }
}
