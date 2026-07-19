<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\fixedasset\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class FixedAssetRolesUsersSeeder extends Seeder
{
    /**
     * Crea roles FA (si faltan), usuarios demo, empleados ligados y categorías.
     */
    public function run(): void
    {
        $now = now();
        $defaultUnitId = DB::table('fa_organizational_units')->orderBy('id')->value('id');

        $profiles = [
            [
                'role' => 'activo-fijo-encargado',
                'email' => 'af.encargado@mail.com',
                'username' => 'af.encargado',
                'name' => 'Elena',
                'lastname' => 'Encargada AF',
                'employee_email' => 'af.encargado@empresa.com',
                'fixed_asset_manager' => true,
                'category_codes' => null, // todas las categorías
            ],
            [
                'role' => 'activo-fijo-solicitante',
                'email' => 'af.solicitante@mail.com',
                'username' => 'af.solicitante',
                'name' => 'Mario',
                'lastname' => 'Solicitante AF',
                'employee_email' => 'af.solicitante@empresa.com',
                'fixed_asset_manager' => false,
                'category_codes' => ['01', '02'], // Archivos, Escritorios
            ],
            [
                'role' => 'activo-fijo-encargado-categoria',
                'email' => 'af.categoria@mail.com',
                'username' => 'af.categoria',
                'name' => 'Laura',
                'lastname' => 'Categoría AF',
                'employee_email' => 'af.categoria@empresa.com',
                'fixed_asset_manager' => false,
                'category_codes' => ['04', '05'], // Silla, Varios
            ],
        ];

        foreach ($profiles as $profile) {
            Role::firstOrCreate(['name' => $profile['role'], 'guard_name' => 'web']);

            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'lastname' => $profile['lastname'],
                    'username' => $profile['username'],
                    'password' => Hash::make('password123'),
                ]
            );

            if (!$user->hasRole($profile['role'])) {
                $user->assignRole($profile['role']);
            }

            $employee = Employee::withTrashed()
                ->where(function ($query) use ($profile, $user) {
                    $query->where('email', $profile['employee_email'])
                        ->orWhere('user_id', $user->id);
                })
                ->first();

            if (!$employee) {
                $employee = new Employee();
                $employee->email = $profile['employee_email'];
            }

            if ($employee->trashed()) {
                $employee->restore();
            }

            $employee->fill([
                'name' => $profile['name'],
                'lastname' => $profile['lastname'],
                'email' => $profile['employee_email'],
                'email_personal' => null,
                'phone' => '2222-0000',
                'phone_personal' => null,
                'birthday' => '1990-01-01',
                'marking_required' => true,
                'status' => 1,
                'active' => true,
                'user_id' => $user->id,
                'adm_gender_id' => 1,
                'adm_marital_status_id' => 1,
                'fa_organizational_unit_id' => $defaultUnitId,
                'remote_mark' => false,
                'external' => false,
                'viatic' => false,
                'children' => false,
                'vehicle' => false,
                'adhonorem' => false,
                'parking' => false,
                'disabled' => false,
                'fixed_asset_manager' => $profile['fixed_asset_manager'],
                'unsubscribe_justification' => null,
            ]);
            $employee->save();

            $categoryIds = $this->resolveCategoryIds($profile['category_codes']);
            $employee->fixedAssetCategories()->sync($categoryIds);

            $this->command?->info(
                "Usuario {$profile['email']} → empleado #{$employee->id} ({$profile['role']}), categorías: "
                . count($categoryIds)
            );
        }
    }

    /**
     * @param  array<int, string>|null  $codes
     * @return array<int, int>
     */
    private function resolveCategoryIds(?array $codes): array
    {
        if ($codes === null) {
            return Category::query()->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return Category::query()
            ->whereIn('code', $codes)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
