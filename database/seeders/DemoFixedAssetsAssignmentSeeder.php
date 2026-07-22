<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\fixedasset\AssetType;
use App\Models\fixedasset\Assignment;
use App\Models\fixedasset\Category;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\OrganizationalUnit;
use App\Models\fixedasset\Origin;
use App\Models\fixedasset\PhysicalCondition;
use App\Models\fixedasset\Specific;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoFixedAssetsAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::query()
            ->whereHas('fixedAssetCategories')
            ->where('active', true)
            ->orderBy('id')
            ->first();

        if (!$employee) {
            $this->command?->error('No hay empleados asignables (fa_category_employee).');

            return;
        }

        $categories = Category::with('specific')->get();
        $specifics = Specific::where('is_active', true)->get()->keyBy(
            fn ($item) => mb_strtolower(trim($item->name))
        );
        $origins = Origin::where('is_active', true)->pluck('id', 'name')->toArray();
        $physicalConditions = PhysicalCondition::where('is_active', true)->pluck('id', 'name')->toArray();
        $organizationalUnits = OrganizationalUnit::where('is_active', true)->pluck('id', 'name')->toArray();
        $assetTypes = AssetType::query()->pluck('id', 'name')->toArray();

        $defaultOriginId = Origin::where('is_active', true)->value('id');
        $defaultPhysicalConditionId = PhysicalCondition::where('is_active', true)->value('id');
        $defaultOrganizationalUnitId = OrganizationalUnit::where('is_active', true)->value('id');
        $defaultAssetTypeId = $assetTypes['01'] ?? AssetType::query()->orderBy('id')->value('id');

        $assetIds = [];

        foreach ($this->rows() as $index => $row) {
            $code = $row['code'];
            $codeParts = explode('-', $code);
            $correlative = end($codeParts) ?: '0000';

            $category = $this->findCategoryByCodeParts($codeParts, $categories)
                ?? $categories->first();

            if (!$category) {
                $this->command?->warn("Fila {$index}: sin categoría para {$code}");
                continue;
            }

            $asset = FixedAsset::query()->updateOrCreate(
                ['code' => $code],
                [
                    'fa_category_id' => $category->id,
                    'correlative' => $correlative,
                    'description' => $row['description'],
                    'brand' => $row['brand'] ?? '',
                    'model' => $row['model'] ?? '',
                    'serial_number' => $row['serial'] ?? null,
                    'location' => $row['location'] ?? '',
                    'policy' => $row['policy'] ?? null,
                    'current_responsible' => trim("{$employee->name} {$employee->lastname}"),
                    'organizational_unit_id' => $this->findForeignKey($row['organizational_unit'] ?? null, $organizationalUnits)
                        ?? $employee->resolveFaOrganizationalUnitId()
                        ?? $defaultOrganizationalUnitId,
                    'asset_type_id' => $this->resolveAssetTypeId($row['asset_type'] ?? null, $assetTypes)
                        ?? $defaultAssetTypeId,
                    'acquisition_date' => $this->parseDate($row['acquisition_date'] ?? null) ?? now(),
                    'supplier' => $row['supplier'] ?? null,
                    'invoice' => $row['invoice'] ?? null,
                    'origin_id' => $this->findForeignKey($row['origin'] ?? null, $origins) ?? $defaultOriginId,
                    'physical_condition_id' => $this->findForeignKey($row['physical_condition'] ?? null, $physicalConditions)
                        ?? $defaultPhysicalConditionId,
                    'additional_description' => $row['additional_description'] ?? null,
                    'measurements' => $row['measurements'] ?? null,
                    'observation' => $row['observation'] ?? null,
                    'is_insured' => $this->parseInsured($row['insured'] ?? null),
                    'insured_description' => $row['insured'] ?? null,
                    'purchase_value' => $row['purchase_value'] ?? 0,
                ]
            );

            $assetIds[] = $asset->id;
        }

        if (empty($assetIds)) {
            $this->command?->error('No se crearon activos.');

            return;
        }

        $organizationalUnitId = $employee->resolveFaOrganizationalUnitId() ?? $defaultOrganizationalUnitId;

        $assignment = Assignment::query()->create([
            'date' => now()->toDateString(),
            'organizational_unit_id' => $organizationalUnitId,
            'person_id' => $employee->id,
            'observation' => 'Asignación de prueba con activos demo importados.',
        ]);

        foreach ($assetIds as $assetId) {
            $assignment->details()->create([
                'fa_fixed_asset_id' => $assetId,
                'observation' => null,
            ]);
        }

        $this->command?->info(sprintf(
            'Creados/actualizados %d activos y asignación #%d a %s %s.',
            count($assetIds),
            $assignment->id,
            $employee->name,
            $employee->lastname
        ));
    }

    private function rows(): array
    {
        return [
            ['code' => '4123-0104-09-0001', 'description' => 'Acces Point', 'brand' => '3COM', 'model' => '7760', 'serial' => '9TZ46XHFC7700', 'location' => 'Bodega San Ramón (para descargo)', 'policy' => null, 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '04', 'origin' => 'DHM (ACTA)', 'acquisition_date' => '30/10/09', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'ACCES POINT: Marca 3COM, Modelo, 7760, Serie 9TZ46XHFC7700', 'insured' => 'NO, OFICINA BIENES TECNOLOGICOS', 'purchase_value' => 0],
            ['code' => '4123-0104-09-0080', 'description' => 'Acces Point', 'brand' => 'FORTINET', 'model' => 'FAP-231F', 'serial' => null, 'location' => '17', 'policy' => 'Poliza 2025', 'organizational_unit' => null, 'asset_type' => null, 'origin' => 'DGEHM-LAGEO', 'acquisition_date' => '17/9/24', 'supplier' => 'JM TELCOM', 'invoice' => '01-M001P001-192', 'physical_condition' => 'En buenas condiciones', 'insured' => null, 'purchase_value' => 519.80],
            ['code' => '4123-0104-09-0064', 'description' => 'Acces Point', 'location' => '16', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Unidad de Tecnología de la Información', 'asset_type' => '04', 'origin' => null, 'acquisition_date' => '17/10/23', 'supplier' => 'Comunicaciones IBM El Salvador S.A de C.V', 'invoice' => '19222', 'physical_condition' => 'En buenas condiciones', 'additional_description' => 'Acces Point: Marca , Modelo', 'measurements' => 'DATA CENTER', 'insured' => 'SI, BIENES TECNOLÓGICOS (USO INTERNO)', 'purchase_value' => 735.07],
            ['code' => '4123-0104-09-0065', 'description' => 'Acces Point', 'location' => '16', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Unidad de Tecnología de la Información', 'asset_type' => '04', 'acquisition_date' => '17/10/23', 'supplier' => 'Comunicaciones IBM El Salvador S.A de C.V', 'invoice' => '19222', 'physical_condition' => 'En buenas condiciones', 'additional_description' => 'Acces Point: Marca , Modelo', 'measurements' => 'DATA CENTER', 'insured' => 'SI, BIENES TECNOLÓGICOS (USO INTERNO)', 'purchase_value' => 735.07],
            ['code' => '4123-0104-09-0066', 'description' => 'Acces Point', 'location' => '16', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Unidad de Tecnología de la Información', 'asset_type' => '04', 'acquisition_date' => '17/10/23', 'supplier' => 'Comunicaciones IBM El Salvador S.A de C.V', 'invoice' => '19222', 'physical_condition' => 'En buenas condiciones', 'additional_description' => 'Acces Point: Marca , Modelo', 'measurements' => 'DATA CENTER', 'insured' => 'SI, BIENES TECNOLÓGICOS (USO INTERNO)', 'purchase_value' => 735.07],
            ['code' => '4123-0104-09-0067', 'description' => 'Acces Point', 'location' => '16', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Unidad de Tecnología de la Información', 'asset_type' => '04', 'acquisition_date' => '17/10/23', 'supplier' => 'Comunicaciones IBM El Salvador S.A de C.V', 'invoice' => '19222', 'physical_condition' => 'En buenas condiciones', 'additional_description' => 'Acces Point: Marca , Modelo', 'measurements' => 'DATA CENTER', 'insured' => 'SI, BIENES TECNOLÓGICOS (USO INTERNO)', 'purchase_value' => 735.07],
            ['code' => '4123-0199-04-0036', 'description' => 'Accesorios para sistema fotovoltaico', 'brand' => 'SOLAR MODULE', 'model' => 'JR-50A', 'serial' => '0', 'location' => '16', 'policy' => 'Poliza 2025', 'organizational_unit' => 'División de Desarrollo Sostenible y Equidad Energética', 'asset_type' => '09', 'origin' => 'CNE', 'acquisition_date' => '3/11/16', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Accesorios para sistema fotovoltaico: Marca SOLAR MODULE, Modelo, JR-50A, Serie 0', 'observation' => 'Oficina Analista de Acceso y Equidad Energética', 'insured' => 'SI, MOBILIARIO Y EQUIPO (USO INTERNO)', 'purchase_value' => 0],
            ['code' => '4123-0104-09-0002', 'description' => 'Adaptador de red USB', 'brand' => 'DLINK', 'model' => 'DWA140', 'serial' => 'P1GK17C000697', 'location' => '16', 'policy' => 'Poliza 2025', 'organizational_unit' => 'Unidad de Tecnología de la Información', 'asset_type' => '04', 'origin' => 'DHM (ACTA)', 'acquisition_date' => '4/9/09', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'ADAPTADOR DE RED USB: Marca DLINK, Modelo, DWA140, Serie P1GK17C000697', 'insured' => 'SI, BIENES TECNOLÓGICOS (USO INTERNO)', 'purchase_value' => 176.96],
            ['code' => '4123-0103-10-0001', 'description' => 'Agitador', 'brand' => 'FISHERSCIENTIFIC', 'model' => 'NOESPECIFICADO', 'serial' => '61001129', 'location' => 'Laboratorio Acajutla', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Departamento de Supervisión y Control (Petroleo)', 'asset_type' => '06', 'origin' => 'DHM (ACTA)', 'acquisition_date' => '8/4/94', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'AGITADOR: Marca FISHERSCIENTIFIC, Modelo, NOESPECIFICADO, Serie 61001129', 'insured' => 'SI, MOBILIARIO Y EQUIPO (USO INTERNO)', 'purchase_value' => 340.16],
            ['code' => '4123-0103-10-0002', 'description' => 'Agitador', 'brand' => 'THERMOLINE', 'model' => 'NOESPECIFICADO', 'serial' => '1313050456540', 'location' => 'Laboratorio Acajutla', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Departamento de Supervisión y Control (Petroleo)', 'asset_type' => '06', 'origin' => 'DHM (ACTA)', 'acquisition_date' => '12/5/05', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'AGITADOR: Marca THERMOLINE, Modelo, NOESPECIFICADO, Serie 1313050456540', 'insured' => 'SI, MOBILIARIO Y EQUIPO (USO INTERNO)', 'purchase_value' => 450.00],
            ['code' => '4123-0102-05-0002', 'description' => 'AIRE ACONDICIONADO', 'brand' => 'COMMODAIRE', 'model' => 'CFU-36', 'serial' => '36510987', 'location' => 'Bodega San Ramón (para descargo)', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '07', 'origin' => 'DHM (ACTA)', 'acquisition_date' => '16/2/95', 'physical_condition' => 'AVERIADO', 'additional_description' => 'UNIDAD CONDESADORA: Marca COMMODAIRE, Modelo, CFU-36, Serie 36510987', 'insured' => 'SI, MOBILIARIO Y EQUIPO (USO INTERNO)', 'purchase_value' => 2857.14],
            ['code' => '4123-0102-05-0186', 'description' => 'Aire acondicionado', 'brand' => 'INNOVAIR', 'model' => 'WO24C2DB3', 'serial' => '540K695020338250860142', 'location' => 'Local de equipos', 'policy' => 'Poliza 2025', 'organizational_unit' => 'División Técnica Administrativa Petrolera', 'asset_type' => '11', 'origin' => 'RP-CEL', 'acquisition_date' => '15/4/24', 'supplier' => 'GRANADA', 'invoice' => '01-M001P001-17015', 'physical_condition' => 'EN BUEN ESTADO', 'purchase_value' => 497.93],
            ['code' => '4123-0102-05-0001', 'description' => 'AIRE ACONDICIONADO', 'brand' => 'COLDPOINT', 'model' => 'CP-18', 'serial' => '42718', 'location' => 'Laboratorio Acajutla', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Departamento de Supervisión y Control (Petroleo)', 'asset_type' => '11', 'origin' => 'DHM (ACTA)', 'acquisition_date' => '1/1/96', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'AIRE ACONDICIONADO: Marca COLDPOINT, Modelo, CP-18, Serie 42718', 'insured' => 'SI, MOBILIARIO Y EQUIPO (USO INTERNO)', 'purchase_value' => 837.14],
            ['code' => '4123-0102-05-0003', 'description' => 'Aire Acondicionado (0.75TN)', 'brand' => 'CONFORT STAR', 'model' => 'S/M', 'serial' => '0', 'location' => 'Bodega San Ramón (para descargo)', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '11', 'origin' => 'CNE', 'acquisition_date' => '12/4/11', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Aire Acondicionado (0.75TN): Marca CONFORT STAR, Modelo, S/M, Serie 0', 'observation' => 'EN BODEGA SAN RAMÓN', 'insured' => 'NO, OFICINA MOBILIARIO Y EQUIPO', 'purchase_value' => 415.00],
            ['code' => '4123-0102-05-0004', 'description' => 'Aire Acondicionado (0.75TN)', 'brand' => 'CONFORT STAR', 'model' => 'S/M', 'serial' => '0', 'location' => 'Bodega San Ramón (para descargo)', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '11', 'origin' => 'CNE', 'acquisition_date' => '12/4/11', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Aire Acondicionado (0.75TN): Marca CONFORT STAR, Modelo, S/M, Serie 0', 'observation' => 'EN BODEGA SAN RAMÓN', 'insured' => 'NO, OFICINA MOBILIARIO Y EQUIPO', 'purchase_value' => 490.00],
            ['code' => '4123-0102-05-0005', 'description' => 'Aire Acondicionado (0.75TN)', 'brand' => 'CONFORT STAR', 'model' => 'S/M', 'serial' => '0', 'location' => 'Bodega San Ramón (para descargo)', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '11', 'origin' => 'CNE', 'acquisition_date' => '12/4/11', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Aire Acondicionado (0.75TN): Marca CONFORT STAR, Modelo, S/M, Serie 0', 'observation' => 'EN BODEGA SAN RAMÓN', 'insured' => 'NO, OFICINA MOBILIARIO Y EQUIPO', 'purchase_value' => 490.00],
            ['code' => '4123-0102-05-0006', 'description' => 'Aire Acondicionado (0.75TN)', 'brand' => 'CONFORT STAR', 'model' => 'S/M', 'serial' => '0', 'location' => 'Bodega San Ramón (para descargo)', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '11', 'origin' => 'CNE', 'acquisition_date' => '4/7/12', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Aire Acondicionado (0.75TN): Marca CONFORT STAR, Modelo, S/M, Serie 0', 'observation' => 'EN BODEGA SAN RAMÓN', 'insured' => 'NO, OFICINA MOBILIARIO Y EQUIPO', 'purchase_value' => 495.00],
            ['code' => '4123-0102-05-0008', 'description' => 'Aire Acondicionado (1.5TN)', 'brand' => 'CONFORT STAR', 'model' => 'Inverter', 'serial' => 'R-410A', 'location' => 'Bodega San Ramón (para descargo)', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '11', 'origin' => 'CNE', 'acquisition_date' => '8/6/21', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Aire Acondicionado (1.5TN): Marca CONFORT STAR, Modelo, Inverter, Serie R-410A', 'observation' => 'EN BODEGA SAN RAMÓN', 'insured' => 'NO, OFICINA MOBILIARIO Y EQUIPO', 'purchase_value' => 1000.00],
            ['code' => '4123-0102-05-0009', 'description' => 'Aire Acondicionado (1.5TN)', 'brand' => 'CONFORT STAR', 'model' => 'Inverter', 'serial' => 'R-410A', 'location' => 'Bodega San Ramón (para descargo)', 'policy' => 'Poliza 2024', 'organizational_unit' => 'Gerencia Administrativa', 'asset_type' => '11', 'origin' => 'CNE', 'acquisition_date' => '8/6/21', 'physical_condition' => 'EN BUEN ESTADO', 'additional_description' => 'Aire Acondicionado (1.5TN): Marca CONFORT STAR, Modelo, Inverter, Serie R-410A', 'observation' => 'EN BODEGA SAN RAMÓN', 'insured' => 'NO, OFICINA MOBILIARIO Y EQUIPO', 'purchase_value' => 1000.00],
        ];
    }

    private function findCategoryByCodeParts(array $codeParts, $categories): ?Category
    {
        if (count($codeParts) < 4) {
            return null;
        }

        $specificCode = $codeParts[1];
        $categoryCode = $codeParts[2];

        return $categories->first(function ($category) use ($specificCode, $categoryCode) {
            $specific = $category->specific;

            return $specific
                && $specific->code === $specificCode
                && $category->code === $categoryCode;
        });
    }

    private function findForeignKey(?string $value, array $lookupTable): ?int
    {
        if (!$value) {
            return null;
        }

        if (isset($lookupTable[$value])) {
            return $lookupTable[$value];
        }

        foreach ($lookupTable as $name => $id) {
            $normalizedName = mb_strtolower(trim($name));
            $normalizedValue = mb_strtolower(trim($value));

            if ($normalizedName === $normalizedValue) {
                return $id;
            }

            if (str_contains($normalizedName, $normalizedValue)
                || str_contains($normalizedValue, $normalizedName)) {
                return $id;
            }
        }

        return null;
    }

    private function resolveAssetTypeId(?string $value, array $lookupTable): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $raw = trim($value);
        $normalized = preg_match('/^\d+$/', $raw)
            ? str_pad($raw, 2, '0', STR_PAD_LEFT)
            : $raw;

        return $lookupTable[$normalized] ?? $this->findForeignKey($normalized, $lookupTable);
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $value, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

            if ($year < 100) {
                $year += $year >= 70 ? 1900 : 2000;
            }

            return Carbon::createFromDate($year, $month, $day);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseInsured(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return Str::startsWith(mb_strtolower(trim($value)), 'si');
    }
}
