<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\fixedasset\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Crea 3 encargados de categoría adicionales (además de Laura)
 * y reparte las categorías sin responsable entre ellos.
 */
class FixedAssetCategoryManagersSeeder extends Seeder
{
    public const ROLE = 'activo-fijo-encargado-categoria';

    public function run(): void
    {
        Role::firstOrCreate(['name' => self::ROLE, 'guard_name' => 'web']);

        $defaultUnitId = DB::table('fa_organizational_units')->orderBy('id')->value('id');

        $profiles = [
            [
                'email' => 'af.categoria2@mail.com',
                'username' => 'af.categoria2',
                'name' => 'Carlos',
                'lastname' => 'Mendoza Ruiz',
                'employee_email' => 'af.categoria2@empresa.com',
            ],
            [
                'email' => 'af.categoria3@mail.com',
                'username' => 'af.categoria3',
                'name' => 'Ana',
                'lastname' => 'López Hernández',
                'employee_email' => 'af.categoria3@empresa.com',
            ],
            [
                'email' => 'af.categoria4@mail.com',
                'username' => 'af.categoria4',
                'name' => 'Pedro',
                'lastname' => 'Ramírez Soto',
                'employee_email' => 'af.categoria4@empresa.com',
            ],
        ];

        $managers = [];

        foreach ($profiles as $profile) {
            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'lastname' => $profile['lastname'],
                    'username' => $profile['username'],
                    'password' => Hash::make('password123'),
                ]
            );

            if (!$user->hasRole(self::ROLE)) {
                $user->assignRole(self::ROLE);
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
                'phone' => '2222-1000',
                'phone_personal' => null,
                'birthday' => '1990-01-01',
                'marking_required' => false,
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
                'warehouse_manager' => false,
                'fixed_asset_manager' => false,
                'unsubscribe_justification' => null,
            ]);
            $employee->save();

            $managers[] = $employee;
            $this->command?->info("Encargado-categoría: {$profile['email']} → empleado #{$employee->id}");
        }

        $unassignedCategoryIds = Category::query()
            ->where('is_active', true)
            ->whereDoesntHave('responsibles')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($unassignedCategoryIds === [] || $managers === []) {
            $this->command?->info('No hay categorías pendientes de encargado.');

            return;
        }

        $buckets = array_fill(0, count($managers), []);
        foreach ($unassignedCategoryIds as $index => $categoryId) {
            $buckets[$index % count($managers)][] = $categoryId;
        }

        foreach ($managers as $i => $employee) {
            $ids = $buckets[$i];
            if ($ids === []) {
                continue;
            }
            $employee->fixedAssetCategories()->syncWithoutDetaching($ids);
            $this->command?->info(
                "{$employee->name} {$employee->lastname}: +" . count($ids) . ' categorías'
            );
        }

        $stillEmpty = Category::query()
            ->where('is_active', true)
            ->whereDoesntHave('responsibles')
            ->count();

        $this->command?->info("Categorías aún sin encargado: {$stillEmpty}");
    }
}
