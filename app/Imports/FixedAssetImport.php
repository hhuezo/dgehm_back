<?php

namespace App\Imports;

use App\Models\fixedasset\AssetClass;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\OrganizationalUnit;
use App\Models\fixedasset\Origin;
use App\Models\fixedasset\PhysicalCondition;
use App\Models\fixedasset\Specific;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class FixedAssetImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    /**
     * Fila de encabezados (fila 1).
     */
    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        // Cache de lookups para optimizar
        $origins = Origin::where('is_active', true)->pluck('id', 'name')->toArray();
        $physicalConditions = PhysicalCondition::where('is_active', true)->pluck('id', 'name')->toArray();
        $organizationalUnits = OrganizationalUnit::where('is_active', true)->pluck('id', 'name')->toArray();
        $specifics = Specific::where('is_active', true)->get()->keyBy(function ($item) {
            return mb_strtolower(trim($item->name));
        });
        $classes = AssetClass::with('specific')->get();

        // Obtener primer origen, condición física y unidad organizativa como defaults
        $defaultOriginId = Origin::where('is_active', true)->first()?->id;
        $defaultPhysicalConditionId = PhysicalCondition::where('is_active', true)->first()?->id;
        $defaultOrganizationalUnitId = OrganizationalUnit::where('is_active', true)->first()?->id;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // encabezados en fila 1, datos desde fila 2

            try {
                // Normalizar claves del row (hoja BASE: INVENTARIO NUEVO, DESCRIPCION, etc.)
                $data = $this->normalizeRow($row->toArray());

                // Saltar filas vacías
                if ($this->isEmptyRow($data)) {
                    continue;
                }

                // Obtener código del activo (INVENTARIO NUEVO)
                $code = $this->getValue($data, ['inventario_nuevo', 'no_inventario', 'inventario', 'codigo']);

                if (!$code) {
                    $this->errors[] = "Fila {$rowNumber}: El código (INVENTARIO NUEVO) es obligatorio.";
                    $this->skipped++;
                    continue;
                }

                // Extraer correlativo del código (última parte después del último guion)
                $codeParts = explode('-', $code);
                $correlative = end($codeParts) ?: '0000';

                // Buscar clase por específico o por código (ej: 4123-0104-09-0001 -> clase 09 del específico 0104)
                $specificName = $this->getValue($data, ['especifico', 'specific']);
                $assetClass = $this->findAssetClassBySpecific($specificName, $specifics, $classes);
                if (!$assetClass && count($codeParts) >= 4) {
                    $assetClass = $this->findAssetClassByCodeParts($codeParts, $classes);
                }
                if (!$assetClass) {
                    $assetClass = $classes->first();
                }
                if (!$assetClass) {
                    $this->errors[] = "Fila {$rowNumber}: No hay clases de activos disponibles.";
                    $this->skipped++;
                    continue;
                }

                // Lookups por nombre
                $originName = $this->getValue($data, ['procedencia', 'origen', 'origin']);
                $originId = $this->findForeignKey($originName, $origins) ?? $defaultOriginId;

                $physicalName = $this->getValue($data, ['estado_fisico', 'estado', 'condicion_fisica']);
                $physicalConditionId = $this->findForeignKey($physicalName, $physicalConditions) ?? $defaultPhysicalConditionId;

                $unitName = $this->getValue($data, ['unidad_gerencia', 'unidad', 'gerencia', 'organizational_unit']);
                $organizationalUnitId = $this->findForeignKey($unitName, $organizationalUnits) ?? $defaultOrganizationalUnitId;

                // Preparar fecha de adquisición (convertir número Excel a fecha)
                $acquisitionDate = $this->parseExcelDate($data, ['fecha_de_adquisicion', 'fecha_adquisicion', 'fecha']);

                // Preparar valor de compra (valor_de_compra_facturas o valor_de_compra)
                $purchaseValue = $this->parseDecimal($data, ['valor_de_compra_facturas', 'valor_de_compra', 'valor_compra', 'valor']);

                // Crear el activo
                $asset = new FixedAsset();
                $asset->fa_class_id = $assetClass->id;
                $asset->code = $code;
                $asset->correlative = $correlative;
                $asset->description = $this->getValue($data, ['descripcion', 'description']) ?? 'Sin descripción';
                $asset->brand = $this->getValue($data, ['marca', 'brand']) ?? '';
                $asset->model = $this->getValue($data, ['modelo', 'model']) ?? '';
                $asset->serial_number = $this->getValue($data, ['serie', 'numero_de_serie', 'numero_serie', 'serial']);
                $asset->location = $this->getValue($data, ['ubicacion', 'location']) ?? '';
                $asset->policy = $this->getValue($data, ['poliza', 'policy']);
                $asset->current_responsible = $this->getValue($data, ['responble_actual', 'responsable_actual', 'asignado_a', 'responsable']) ?? '';
                $asset->organizational_unit_id = $organizationalUnitId;
                $asset->asset_type = $this->getValue($data, ['tipo_de_bien_segun_min_hacienda', 'tipo_bien', 'tipo']) ?? 'General';
                $asset->acquisition_date = $acquisitionDate ?? now();
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

                // Validar campos requeridos
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
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    /**
     * Normaliza las claves del row (minúsculas, sin acentos, espacios a guiones bajos).
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeKey($key);
            $normalized[$normalizedKey] = $value;
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

    protected function findAssetClassBySpecific(?string $specificName, $specifics, $classes): ?AssetClass
    {
        if (!$specificName) {
            return null;
        }

        $normalizedSpecific = mb_strtolower(trim($specificName));
        $specific = $specifics->get($normalizedSpecific);

        if ($specific) {
            $class = $classes->first(fn($c) => $c->fa_specific_id === $specific->id);
            if ($class) {
                return $class;
            }
        }

        return null;
    }

    protected function findAssetClassByCodeParts(array $codeParts, $classes): ?AssetClass
    {
        // codeParts: [4123, 0104, 09, 0001] -> específico 0104, clase 09
        if (count($codeParts) < 4) {
            return null;
        }
        $specificCode = $codeParts[1];
        $classCode = $codeParts[2];

        return $classes->first(function ($c) use ($specificCode, $classCode) {
            $spec = $c->specific;
            return $spec && $spec->code === $specificCode && $c->code === $classCode;
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
            if (str_contains(mb_strtolower($name), mb_strtolower($value)) ||
                str_contains(mb_strtolower($value), mb_strtolower($name))) {
                return $id;
            }
        }
        return null;
    }

    protected function parseBoolean(array $data, array $possibleKeys): bool
    {
        $value = $this->getValue($data, $possibleKeys);
        if (!$value) {
            return false;
        }
        $value = mb_strtolower(trim($value));
        return in_array($value, ['si', 'sí', 'yes', '1', 'true', 'x', 'verdadero']);
    }

    protected function getValue(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = $this->normalizeKey($key);
            if (isset($data[$normalizedKey]) && $data[$normalizedKey] !== null && $data[$normalizedKey] !== '') {
                return trim((string) $data[$normalizedKey]);
            }
        }
        return null;
    }

    protected function parseExcelDate(array $data, array $possibleKeys): ?\Carbon\Carbon
    {
        $value = $this->getValue($data, $possibleKeys);

        if (!$value) {
            return null;
        }

        // Si es numérico, es un número de serie de Excel
        if (is_numeric($value)) {
            try {
                return \Carbon\Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Exception $e) {
                // Si falla, intentar como año
                if ((int) $value > 1900 && (int) $value < 2100) {
                    return \Carbon\Carbon::createFromDate((int) $value, 1, 1);
                }
            }
        }

        // Intentar parsear como fecha normal
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseDecimal(array $data, array $possibleKeys): ?float
    {
        $value = $this->getValue($data, $possibleKeys);

        if (!$value) {
            return null;
        }

        // Limpiar el valor (quitar símbolos de moneda, comas, etc.)
        $cleaned = preg_replace('/[^0-9.,\-]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);

        // Si hay múltiples puntos, quitar todos menos el último
        if (substr_count($cleaned, '.') > 1) {
            $parts = explode('.', $cleaned);
            $lastPart = array_pop($parts);
            $cleaned = implode('', $parts) . '.' . $lastPart;
        }

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}
