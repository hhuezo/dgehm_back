<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdmEmployeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->where('email', 'admin@mail.com')->value('id');
        if ($userId === null) {
            return;
        }

        $now = now();

        DB::table('adm_employees')->upsert(
            [
                [
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
                ],
            ],
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
