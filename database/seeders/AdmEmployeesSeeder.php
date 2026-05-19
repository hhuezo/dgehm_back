<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmEmployeesSeeder extends Seeder
{
    private const TOTAL = 20;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->where('email', 'admin@mail.com')->value('id');

        $now = now();

        $firstNames = [
            'María', 'Carlos', 'Ana', 'Luis', 'Rosa', 'Pedro', 'Lucía', 'Jorge', 'Carmen', 'Miguel',
            'Laura', 'Andrés', 'Patricia', 'Roberto', 'Gabriela', 'Daniel', 'Sofía', 'Fernando', 'Valeria', 'Ricardo',
        ];

        $lastNames = [
            'García', 'Martínez', 'López', 'Hernández', 'Pérez', 'González', 'Ramírez', 'Torres', 'Flores', 'Rivera',
            'Vargas', 'Castillo', 'Morales', 'Núñez', 'Silva', 'Reyes', 'Medina', 'Aguilar', 'Campos', 'Peña',
        ];

        $rows = [];

        for ($i = 1; $i <= self::TOTAL; $i++) {
            if ($i === 1) {
                $rows[] = [
                    'id' => 1,
                    'name' => 'Administrador',
                    'lastname' => 'Sistema',
                    'email' => 'admin@empresa.com',
                    'email_personal' => 'admin.personal@empresa.com',
                    'phone' => '12345678',
                    'phone_personal' => '87654321',
                    'photo_name' => 'admin.jpg',
                    'photo_route' => '/photos/admin.jpg',
                    'photo_route_sm' => '/photos/admin_sm.jpg',
                    'birthday' => '1990-01-01',
                    'marking_required' => true,
                    'status' => 1,
                    'active' => true,
                    'user_id' => $userId,
                    'adm_gender_id' => 1,
                    'adm_marital_status_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'remote_mark' => false,
                    'external' => false,
                    'viatic' => false,
                    'children' => false,
                    'vehicle' => false,
                    'adhonorem' => false,
                    'parking' => false,
                    'disabled' => false,
                    'unsubscribe_justification' => null,
                ];

                continue;
            }

            $idx = $i - 2;
            $name = $firstNames[$idx % count($firstNames)];
            $lastname = $lastNames[$idx % count($lastNames)] . ' Demo' . $i;

            $rows[] = [
                'id' => $i,
                'name' => $name,
                'lastname' => $lastname,
                'email' => "empleado.semillero.{$i}@example.test",
                'email_personal' => "empleado.semillero.{$i}.personal@example.test",
                'phone' => sprintf('22%02d-%04d', ($i % 99), 1000 + $i),
                'phone_personal' => $i % 3 === 0 ? null : sprintf('78%02d-%04d', ($i % 99), 2000 + $i),
                'photo_name' => null,
                'photo_route' => null,
                'photo_route_sm' => null,
                'birthday' => sprintf(
                    '%d-%02d-%02d',
                    1975 + (($i - 2) % 25),
                    (($i * 3) % 12) + 1,
                    (($i * 5) % 28) + 1
                ),
                'marking_required' => $i % 4 !== 0,
                'status' => 1,
                'active' => $i % 7 !== 0,
                'user_id' => null,
                'adm_gender_id' => (($i - 1) % 3) + 1,
                'adm_marital_status_id' => (($i - 1) % 5) + 1,
                'created_at' => $now,
                'updated_at' => $now,
                'remote_mark' => $i % 5 === 0,
                'external' => $i % 6 === 0,
                'viatic' => $i % 4 === 0,
                'children' => $i % 3 === 0,
                'vehicle' => $i % 5 === 1,
                'adhonorem' => $i % 8 === 0,
                'parking' => $i % 4 === 2,
                'disabled' => $i % 11 === 0,
                'unsubscribe_justification' => null,
            ];
        }

        DB::table('adm_employees')->upsert(
            $rows,
            ['id'],
            [
                'name', 'lastname', 'email', 'email_personal', 'phone', 'phone_personal',
                'photo_name', 'photo_route', 'photo_route_sm', 'birthday', 'marking_required',
                'status', 'active', 'user_id', 'adm_gender_id', 'adm_marital_status_id',
                'remote_mark', 'external', 'viatic', 'children', 'vehicle', 'adhonorem',
                'parking', 'disabled', 'unsubscribe_justification', 'updated_at',
            ]
        );
    }
}
