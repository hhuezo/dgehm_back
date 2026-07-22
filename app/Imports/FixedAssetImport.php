<?php

namespace App\Imports;

use App\Models\fixedasset\AssetType;
use App\Models\fixedasset\Category;
use App\Models\fixedasset\DepreciationStatus;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\OrganizationalUnit;
use App\Models\fixedasset\Origin;
use App\Models\fixedasset\PhysicalCondition;
use App\Models\fixedasset\Specific;
use App\Services\FixedAssetResponsibleResolver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class FixedAssetImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;
    public int $duplicates = 0;
    public int $personsCreated = 0;
    public int $personsReused = 0;

    public function __construct(private readonly bool $dryRun = false)
    {
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        $this->processRows($rows, 0);
    }

    /**
     * Procesa filas ya normalizadas (claves tipo heading) o crudas de Excel.
     *
     * @param  Collection<int, mixed>  $rows
     */
    public function processRows(Collection $rows, int $rowOffset = 0): void
    {
        $origins = Origin::where('is_active', true)->pluck('id', 'name')->toArray();
        $physicalConditions = PhysicalCondition::where('is_active', true)->pluck('id', 'name')->toArray();
        $organizationalUnits = OrganizationalUnit::where('is_active', true)->pluck('id', 'name')->toArray();
        $assetTypes = AssetType::query()->pluck('id', 'name')->toArray();
        $specifics = Specific::where('is_active', true)->get()->keyBy(function ($item) {
            return mb_strtolower(trim($item->name));
        });
        $categories = Category::with('specific')->get();

        $defaultOriginId = Origin::where('is_active', true)->first()?->id;
        $defaultPhysicalConditionId = PhysicalCondition::where('is_active', true)->first()?->id;
        $defaultOrganizationalUnitId = OrganizationalUnit::where('is_active', true)->first()?->id;

        $resolver = new FixedAssetResponsibleResolver();
        $existingCodes = FixedAsset::query()->pluck('id', 'code')->all();

        foreach ($rows as $index => $row) {
            $rowNumber = $rowOffset + $index + 2;

            try {
                $raw = is_array($row) ? $row : (method_exists($row, 'toArray') ? $row->toArray() : (array) $row);
                $data = $this->normalizeRow($raw);

                if ($this->isEmptyRow($data)) {
                    continue;
                }

                $code = $this->getValue($data, ['inventario_nuevo', 'no_inventario', 'inventario', 'codigo']);

                if (!$code) {
                    $this->errors[] = "Fila {$rowNumber}: El código (INVENTARIO NUEVO) es obligatorio.";
                    $this->skipped++;
                    continue;
                }

                if (isset($existingCodes[$code])) {
                    $this->duplicates++;
                    continue;
                }

                $codeParts = explode('-', $code);
                $correlative = end($codeParts) ?: '0000';

                $specificName = $this->getValue($data, ['especifico', 'specific']);
                $assetCategory = $this->findCategoryBySpecific($specificName, $specifics, $categories);
                if (!$assetCategory && count($codeParts) >= 4) {
                    $assetCategory = $this->findCategoryByCodeParts($codeParts, $categories);
                }
                if (!$assetCategory) {
                    $assetCategory = $categories->first();
                }
                if (!$assetCategory) {
                    $this->errors[] = "Fila {$rowNumber}: No hay categorías de activos disponibles.";
                    $this->skipped++;
                    continue;
                }

                $originName = $this->getValue($data, ['procedencia', 'origen', 'origin']);
                $originId = $this->findForeignKey($originName, $origins) ?? $defaultOriginId;

                $physicalName = $this->getValue($data, ['estado_fisico', 'estado', 'condicion_fisica']);
                $physicalConditionId = $this->findForeignKey($physicalName, $physicalConditions) ?? $defaultPhysicalConditionId;

                $unitName = $this->getValue($data, ['unidad_gerencia', 'unidad', 'gerencia', 'organizational_unit']);
                $organizationalUnitId = $this->findForeignKey($unitName, $organizationalUnits) ?? $defaultOrganizationalUnitId;

                $assetTypeRaw = $this->getValue($data, [
                    'tipo_de_bien_segun_min_hacienda',
                    'tipo_bien_segun_min_hacienda',
                    'tipo_de_bien_segun_mh',
                    'tipo_bien_segun_mh',
                    'tipo_bien_segun_mhacienda',
                    'tipo_de_bien',
                    'tipo_bien',
                    'tipo',
                    'asset_type',
                ]);
                $assetTypeId = $this->resolveAssetTypeId($assetTypeRaw, $assetTypes);

                if ($assetTypeRaw !== null && $assetTypeRaw !== '' && !$assetTypeId) {
                    $this->errors[] = "Fila {$rowNumber}: Tipo de bien «{$assetTypeRaw}» no existe en fa_asset_types.";
                }

                $acquisitionDate = $this->parseExcelDate($data, [
                    'fecha_de_adquisicion',
                    'fecha_adquisicion',
                    'f_adquisicion',
                    'fecha_adq',
                    'acquisition_date',
                ]);
                $purchaseValue = $this->parseDecimal($data, ['valor_de_compra_facturas', 'valor_de_compra', 'valor_compra', 'valor']);

                $responsibleName = $this->getValue($data, [
                    'responble_actual',
                    'responsable_actual',
                    'asignado_a',
                    'responsable',
                ]);

                $employee = $resolver->resolve($responsibleName, $organizationalUnitId);

                if ($this->dryRun) {
                    $this->imported++;
                    $existingCodes[$code] = 0;
                    continue;
                }

                $asset = new FixedAsset();
                $asset->fa_category_id = $assetCategory->id;
                $asset->code = $code;
                $asset->correlative = $correlative;
                $asset->description = $this->getValue($data, ['descripcion', 'description']) ?? 'Sin descripción';
                $asset->brand = $this->getValue($data, ['marca', 'brand']);
                $asset->model = $this->getValue($data, ['modelo', 'model']);
                $asset->serial_number = $this->getValue($data, ['serie', 'numero_de_serie', 'numero_serie', 'serial']);
                $asset->location = $this->getValue($data, ['ubicacion', 'location']) ?? '';
                $asset->policy = $this->getValue($data, ['poliza', 'policy']);
                $asset->current_responsible = $employee
                    ? trim($employee->name . ' ' . $employee->lastname)
                    : ($responsibleName ?: null);
                $asset->depreciation_status_id = $employee
                    ? DepreciationStatus::ACTIVE
                    : DepreciationStatus::PENDING_ASSIGNMENT;
                $asset->organizational_unit_id = $organizationalUnitId;
                $asset->asset_type_id = $assetTypeId; // null si vacío o no encontrado en catálogo
                $asset->acquisition_date = $acquisitionDate ?? now();
                if (!$acquisitionDate) {
                    $rawDate = $this->getRawValue($data, [
                        'fecha_de_adquisicion',
                        'fecha_adquisicion',
                        'f_adquisicion',
                        'fecha_adq',
                        'acquisition_date',
                    ]);
                    if ($rawDate !== null && $rawDate !== '') {
                        $this->errors[] = "Fila {$rowNumber}: No se pudo interpretar la fecha de adquisición «{$rawDate}»; se usó la fecha actual.";
                    }
                }
                $asset->supplier = $this->getValue($data, ['proveedor', 'supplier']);
                $asset->invoice = $this->getValue($data, ['factura', 'no_de_factura', 'no_factura', 'invoice']);
                $asset->origin_id = $originId;
                $asset->physical_condition_id = $physicalConditionId;
                $asset->additional_description = $this->getValue($data, ['descripcion_del_bien', 'descripcion_adicional']);
                $asset->measurements = $this->getValue($data, ['medidas', 'measurements']);
                $asset->observation = $this->getValue($data, ['observacion', 'observaciones', 'observation']);
                $asset->is_insured = $this->parseBoolean($data, ['asegurado_si_no', 'asegurado', 'is_insured']);
                $asset->insured_description = null;
                $asset->purchase_value = $purchaseValue ?? 0;

                if (!$asset->organizational_unit_id) {
                    $this->errors[] = "Fila {$rowNumber}: No hay unidades organizativas disponibles.";
                    $this->skipped++;
                    continue;
                }

                if (!$asset->origin_id) {
                    $this->errors[] = "Fila {$rowNumber}: No hay orígenes disponibles.";
                    $this->skipped++;
                    continue;
                }

                if (!$asset->physical_condition_id) {
                    $this->errors[] = "Fila {$rowNumber}: No hay estados físicos disponibles.";
                    $this->skipped++;
                    continue;
                }

                $asset->save();
                $existingCodes[$code] = $asset->id;
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                $this->skipped++;
            }
        }

        $this->personsCreated = $resolver->createdEmployees;
        $this->personsReused = $resolver->reusedEmployees;
    }

    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$this->normalizeKey($key)] = $value;
        }

        return $normalized;
    }

    protected function normalizeKey($key): string
    {
        if (!is_string($key)) {
            return (string) $key;
        }

        $key = mb_strtolower($key);
        $key = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');

        return $key;
    }

    protected function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if (!empty($value) && $value !== null) {
                return false;
            }
        }

        return true;
    }

    protected function findCategoryBySpecific(?string $specificName, $specifics, $categories): ?Category
    {
        if (!$specificName) {
            return null;
        }

        $normalizedSpecific = mb_strtolower(trim($specificName));
        $specific = $specifics->get($normalizedSpecific);

        if ($specific) {
            $category = $categories->first(fn ($c) => $c->fa_specific_id === $specific->id);
            if ($category) {
                return $category;
            }
        }

        return null;
    }

    protected function findCategoryByCodeParts(array $codeParts, $categories): ?Category
    {
        if (count($codeParts) < 4) {
            return null;
        }
        $specificCode = $codeParts[1];
        $categoryCode = $codeParts[2];

        return $categories->first(function ($c) use ($specificCode, $categoryCode) {
            $spec = $c->specific;

            return $spec && $spec->code === $specificCode && $c->code === $categoryCode;
        });
    }

    protected function findForeignKey(?string $value, array $lookupTable): ?int
    {
        if (!$value) {
            return null;
        }

        if (isset($lookupTable[$value])) {
            return $lookupTable[$value];
        }

        foreach ($lookupTable as $name => $id) {
            if (mb_strtolower(trim($name)) === mb_strtolower(trim($value))) {
                return $id;
            }
            if (str_contains(mb_strtolower($name), mb_strtolower($value))
                || str_contains(mb_strtolower($value), mb_strtolower($name))) {
                return $id;
            }
        }

        return null;
    }

    protected function resolveAssetTypeId(?string $value, array $lookupTable): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $raw = trim($value);

        // Excel a veces manda "4" o "4.0"; catálogo usa "04"
        if (is_numeric($raw)) {
            $raw = (string) (int) round((float) $raw);
            $normalized = str_pad($raw, 2, '0', STR_PAD_LEFT);

            return $lookupTable[$normalized] ?? null;
        }

        if (preg_match('/^\d+$/', $raw)) {
            $normalized = str_pad($raw, 2, '0', STR_PAD_LEFT);

            return $lookupTable[$normalized] ?? null;
        }

        return $lookupTable[$raw] ?? null;
    }

    protected function parseBoolean(array $data, array $possibleKeys): bool
    {
        $value = $this->getValue($data, $possibleKeys);
        if (!$value) {
            return false;
        }
        $value = mb_strtolower(trim($value));

        return in_array($value, ['si', 'sí', 'yes', '1', 'true', 'x', 'verdadero'], true);
    }

    protected function getRawValue(array $data, array $possibleKeys): mixed
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = $this->normalizeKey($key);
            if (array_key_exists($normalizedKey, $data) && $data[$normalizedKey] !== null && $data[$normalizedKey] !== '') {
                return $data[$normalizedKey];
            }
        }

        return null;
    }

    protected function getValue(array $data, array $possibleKeys): ?string
    {
        $value = $this->getRawValue($data, $possibleKeys);

        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return trim((string) $value);
    }

    protected function parseExcelDate(array $data, array $possibleKeys): ?\Carbon\Carbon
    {
        $value = $this->getRawValue($data, $possibleKeys);

        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance(\DateTimeImmutable::createFromInterface($value));
        }

        if (is_numeric($value)) {
            try {
                return \Carbon\Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }

        $raw = trim((string) $value);

        // Formato local SV: d/m/Y o d/m/y (ej. 30/10/09)
        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})$/', $raw, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

            if ($year < 100) {
                $year += $year >= 70 ? 1900 : 2000;
            }

            if (checkdate($month, $day, $year)) {
                return \Carbon\Carbon::createFromDate($year, $month, $day)->startOfDay();
            }
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'm/d/Y'] as $format) {
            try {
                $parsed = \Carbon\Carbon::createFromFormat($format, $raw);

                if ($parsed !== false) {
                    return $parsed->startOfDay();
                }
            } catch (\Throwable) {
                // siguiente formato
            }
        }

        try {
            return \Carbon\Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseDecimal(array $data, array $possibleKeys): ?float
    {
        $value = $this->getValue($data, $possibleKeys);

        if (!$value) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.,\-]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);

        if (substr_count($cleaned, '.') > 1) {
            $parts = explode('.', $cleaned);
            $lastPart = array_pop($parts);
            $cleaned = implode('', $parts) . '.' . $lastPart;
        }

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}
