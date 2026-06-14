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
                'name'       => 'Presupuesto General de la Nación',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Donación',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Cooperación Internacional',
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
