<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmEmployeeRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $employeeIds = DB::table('adm_employees')->orderBy('id')->pluck('id');

        if ($employeeIds->isEmpty()) {
            return;
        }

        $documentRows = [];
        $positionRows = [];
        $positionIds = [1, 2, 3, 4, 5];
        $positionSalaries = [0, 1200, 750, 550, 600];

        foreach ($employeeIds as $index => $employeeId) {
            $i = (int) $employeeId;
            $duiNumber = str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT);
            $duiCheck = $i % 10;
            $documentRows[] = [
                'value' => "{$duiNumber}-{$duiCheck}",
                'adm_employee_id' => $employeeId,
                'adm_document_type_id' => 1,
            ];

            if ($i % 2 === 0) {
                $nitCheck = $i % 10;
                $documentRows[] = [
                    'value' => sprintf('%04d-%06d-%03d-%d', 1000 + $i, 100000 + $i, $i % 1000, $nitCheck),
                    'adm_employee_id' => $employeeId,
                    'adm_document_type_id' => 2,
                ];
            }

            if ($i % 3 === 0) {
                $documentRows[] = [
                    'value' => str_pad((string) (200000000000 + $i), 12, '0', STR_PAD_LEFT),
                    'adm_employee_id' => $employeeId,
                    'adm_document_type_id' => 3,
                ];
            }

            $documentRows[] = [
                'value' => str_pad((string) (3000000 + $i), 7, '0', STR_PAD_LEFT),
                'adm_employee_id' => $employeeId,
                'adm_document_type_id' => 7,
            ];

            $positionIndex = $i === 1 ? 0 : ($i % count($positionIds));
            $positionRows[] = [
                'date_start' => $now->copy()->subMonths(6 + ($i % 12))->toDateString(),
                'date_end' => $i % 9 === 0 ? $now->copy()->subMonth()->toDateString() : null,
                'principal' => true,
                'salary' => $positionSalaries[$positionIndex],
                'active' => $i % 9 !== 0,
                'adm_employee_id' => $employeeId,
                'adm_functional_position_id' => $positionIds[$positionIndex],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('adm_document_type_adm_employee')->upsert(
            $documentRows,
            ['value'],
            ['adm_employee_id', 'adm_document_type_id']
        );

        DB::table('adm_employee_adm_functional_position')->delete();

        DB::table('adm_employee_adm_functional_position')->insert($positionRows);
    }
}
