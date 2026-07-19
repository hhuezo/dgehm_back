<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class FixedAssetResponsibleResolver
{
    public const ROLE = 'activo-fijo-solicitante';

    /** @var array<string, Employee> */
    private array $cache = [];

    /** @var array<string, true> */
    private array $countedReuse = [];

    public int $createdEmployees = 0;

    public int $reusedEmployees = 0;

    public function __construct()
    {
        Role::firstOrCreate(['name' => self::ROLE, 'guard_name' => 'web']);

        Employee::query()
            ->with('user')
            ->get(['id', 'name', 'lastname', 'email', 'user_id', 'fa_organizational_unit_id'])
            ->each(function (Employee $employee) {
                $key = $this->normalizeFullName($employee->name . ' ' . $employee->lastname);
                if ($key !== '') {
                    $this->cache[$key] = $employee;
                }
            });
    }

    /**
     * Busca la persona por nombre completo; si no existe, crea empleado + usuario con rol solicitante.
     */
    public function resolve(?string $fullName, ?int $organizationalUnitId = null): ?Employee
    {
        $fullName = trim((string) $fullName);
        if ($fullName === '') {
            return null;
        }

        $key = $this->normalizeFullName($fullName);
        if ($key === '') {
            return null;
        }

        if (isset($this->cache[$key])) {
            $employee = $this->cache[$key];
            $this->ensureSolicitanteUser($employee, $fullName);
            if (!isset($this->countedReuse[$key])) {
                $this->countedReuse[$key] = true;
                $this->reusedEmployees++;
            }

            return $employee;
        }

        [$name, $lastname] = $this->splitName($fullName);
        $slug = $this->uniqueSlug($fullName);
        $email = $slug . '@import.activo-fijo.local';

        $user = User::create([
            'name' => $name,
            'lastname' => $lastname,
            'username' => $slug,
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole(self::ROLE);

        $employee = Employee::create([
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'phone' => null,
            'birthday' => null,
            'marking_required' => false,
            'status' => 1,
            'active' => true,
            'user_id' => $user->id,
            'fa_organizational_unit_id' => $organizationalUnitId,
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
        ]);

        $this->cache[$key] = $employee;
        $this->createdEmployees++;

        return $employee;
    }

    private function ensureSolicitanteUser(Employee $employee, string $fullName): void
    {
        if ($employee->user_id) {
            $user = $employee->user ?? User::find($employee->user_id);
            if ($user && !$user->hasRole(self::ROLE) && !$user->hasRole('admin')) {
                $user->assignRole(self::ROLE);
            }

            return;
        }

        [$name, $lastname] = $this->splitName($fullName);
        $slug = $this->uniqueSlug($fullName);
        $user = User::create([
            'name' => $name,
            'lastname' => $lastname,
            'username' => $slug,
            'email' => $slug . '@import.activo-fijo.local',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole(self::ROLE);

        $employee->user_id = $user->id;
        $employee->save();
        $employee->setRelation('user', $user);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) === 0) {
            return ['Sin', 'Nombre'];
        }

        if (count($parts) === 1) {
            return [$parts[0], '—'];
        }

        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        if (count($parts) === 3) {
            return [$parts[0] . ' ' . $parts[1], $parts[2]];
        }

        // 4+ partes: 2 nombres + resto apellidos (común en SV)
        return [
            $parts[0] . ' ' . $parts[1],
            implode(' ', array_slice($parts, 2)),
        ];
    }

    public function normalizeFullName(string $value): string
    {
        $value = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
        $value = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $value
        );

        return $value;
    }

    private function uniqueSlug(string $fullName): string
    {
        $base = Str::slug($fullName, '.');
        if ($base === '') {
            $base = 'persona.' . Str::lower(Str::random(6));
        }

        // users.username es varchar(32)
        $base = Str::limit($base, 28, '');
        $slug = $base;
        $i = 1;

        while (
            User::where('username', $slug)->exists()
            || User::where('email', $slug . '@import.activo-fijo.local')->exists()
            || Employee::withTrashed()->where('email', $slug . '@import.activo-fijo.local')->exists()
        ) {
            $suffix = '.' . $i;
            $slug = Str::limit($base, 32 - strlen($suffix), '') . $suffix;
            $i++;
        }

        return $slug;
    }
}
