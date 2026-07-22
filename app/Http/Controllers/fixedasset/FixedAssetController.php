<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Imports\FixedAssetImport;
use App\Models\fixedasset\Category;
use App\Models\fixedasset\DepreciationStatus;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\Institution;
use App\Services\FixedAssetMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = FixedAsset::select(
            'id',
            'fa_category_id',
            'code',
            'correlative',
            'description',
            'brand',
            'model',
            'serial_number',
            'location',
            'policy',
            'current_responsible',
            'organizational_unit_id',
            'asset_type_id',
            'acquisition_date',
            'supplier',
            'invoice',
            'origin_id',
            'physical_condition_id',
            'additional_description',
            'measurements',
            'observation',
            'is_insured',
            'insured_description',
            'purchase_value',
            'depreciation_status_id'
        )
            ->with([
                'category:id,fa_specific_id,code,name,useful_life',
                'category.specific:id,code,name',
                'organizationalUnit:id,name',
                'assetType:id,name',
                'origin:id,name',
                'physicalCondition:id,name',
                'depreciationStatus:id,name',
            ])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assets,
        ], 200);
    }

    public function depreciationStatuses()
    {
        $statuses = DepreciationStatus::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'fa_category_id' => 'required|exists:fa_categories,id',
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:150',
            'location' => 'required|string|max:255',
            'policy' => 'nullable|string|max:150',
            'current_responsible' => 'nullable|string|max:255',
            'organizational_unit_id' => 'required|exists:fa_organizational_units,id',
            'asset_type_id' => 'nullable|exists:fa_asset_types,id',
            'acquisition_date' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'invoice' => 'nullable|string|max:150',
            'origin_id' => 'required|exists:fa_origins,id',
            'physical_condition_id' => 'required|exists:fa_physical_conditions,id',
            'additional_description' => 'nullable|string',
            'measurements' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'is_insured' => 'nullable|boolean',
            'insured_description' => 'nullable|string|max:255',
            'purchase_value' => 'required|numeric|min:0',
            'depreciation_status_id' => 'nullable|integer|exists:fa_depreciation_statuses,id',
        ];

        $messages = [
            'fa_category_id.required' => 'La categoría es obligatoria.',
            'fa_category_id.exists' => 'La categoría seleccionada no existe.',
            'description.required' => 'La descripción es obligatoria.',
            'location.required' => 'La ubicación es obligatoria.',
            'organizational_unit_id.required' => 'La unidad organizativa es obligatoria.',
            'organizational_unit_id.exists' => 'La unidad organizativa seleccionada no existe.',
            'asset_type_id.exists' => 'El tipo de bien seleccionado no existe.',
            'acquisition_date.required' => 'La fecha de adquisición es obligatoria.',
            'acquisition_date.date' => 'La fecha de adquisición no es válida.',
            'origin_id.required' => 'El origen es obligatorio.',
            'origin_id.exists' => 'El origen seleccionado no existe.',
            'physical_condition_id.required' => 'El estado físico es obligatorio.',
            'physical_condition_id.exists' => 'El estado físico seleccionado no existe.',
            'purchase_value.required' => 'El valor de compra es obligatorio.',
            'purchase_value.numeric' => 'El valor de compra debe ser numérico.',
            'purchase_value.min' => 'El valor de compra no puede ser negativo.',
        ];

        $data = $request->validate($rules, $messages);

        $asset = null;

        DB::transaction(function () use (&$asset, $data) {
            $institution = Institution::select('code')
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->first();

            $institutionCode = $institution?->code ?? '0000';

            /** @var \App\Models\fixedasset\Category $category */
            $category = Category::with('specific:id,code')
                ->findOrFail($data['fa_category_id']);

            $specificCode = $category->specific?->code ?? '0000';
            $categoryCode = $category->code;

            $lastCorrelative = FixedAsset::where('fa_category_id', $category->id)->max('correlative');
            $nextCorrelativeNumber = $lastCorrelative ? (int)$lastCorrelative + 1 : 1;
            $correlative = str_pad($nextCorrelativeNumber, 4, '0', STR_PAD_LEFT);

            $code = $institutionCode . '-' . $specificCode . '-' . $categoryCode . '-' . $correlative;

            $statusId = (int) ($data['depreciation_status_id'] ?? DepreciationStatus::ACTIVE);
            $responsible = trim((string) ($data['current_responsible'] ?? ''));
            if ($statusId === DepreciationStatus::PENDING_ASSIGNMENT) {
                $responsible = '';
            }

            $asset = new FixedAsset();
            $asset->fa_category_id = $category->id;
            $asset->code = $code;
            $asset->correlative = $correlative;
            $asset->description = $data['description'];
            $asset->brand = $data['brand'] ?? null;
            $asset->model = $data['model'] ?? null;
            $asset->serial_number = $data['serial_number'] ?? null;
            $asset->location = $data['location'];
            $asset->policy = $data['policy'] ?? null;
            $asset->current_responsible = $responsible !== '' ? $responsible : null;
            $asset->organizational_unit_id = $data['organizational_unit_id'];
            $asset->asset_type_id = $data['asset_type_id'] ?? null;
            $asset->acquisition_date = $data['acquisition_date'];
            $asset->supplier = $data['supplier'] ?? null;
            $asset->invoice = $data['invoice'] ?? null;
            $asset->origin_id = $data['origin_id'];
            $asset->physical_condition_id = $data['physical_condition_id'];
            $asset->additional_description = $data['additional_description'] ?? null;
            $asset->measurements = $data['measurements'] ?? null;
            $asset->observation = $data['observation'] ?? null;
            $asset->is_insured = $data['is_insured'] ?? false;
            $asset->insured_description = $data['insured_description'] ?? null;
            $asset->purchase_value = $data['purchase_value'];
            $asset->depreciation_status_id = $statusId;
            $asset->save();

            $asset->load([
                'category:id,fa_specific_id,code,name,useful_life',
                'category.specific:id,code,name',
                'organizationalUnit:id,name',
                'assetType:id,name',
                'origin:id,name',
                'physicalCondition:id,name',
                'depreciationStatus:id,name',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Activo fijo creado correctamente.',
            'data' => $asset,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'fa_category_id' => 'required|exists:fa_categories,id',
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:150',
            'location' => 'required|string|max:255',
            'policy' => 'nullable|string|max:150',
            'current_responsible' => 'nullable|string|max:255',
            'organizational_unit_id' => 'required|exists:fa_organizational_units,id',
            'asset_type_id' => 'nullable|exists:fa_asset_types,id',
            'acquisition_date' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'invoice' => 'nullable|string|max:150',
            'origin_id' => 'required|exists:fa_origins,id',
            'physical_condition_id' => 'required|exists:fa_physical_conditions,id',
            'additional_description' => 'nullable|string',
            'measurements' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
            'is_insured' => 'nullable|boolean',
            'insured_description' => 'nullable|string|max:255',
            'purchase_value' => 'required|numeric|min:0',
            'depreciation_status_id' => 'nullable|integer|exists:fa_depreciation_statuses,id',
        ];

        $messages = [
            'fa_category_id.required' => 'La categoría es obligatoria.',
            'fa_category_id.exists' => 'La categoría seleccionada no existe.',
            'description.required' => 'La descripción es obligatoria.',
            'location.required' => 'La ubicación es obligatoria.',
            'organizational_unit_id.required' => 'La unidad organizativa es obligatoria.',
            'organizational_unit_id.exists' => 'La unidad organizativa seleccionada no existe.',
            'asset_type_id.exists' => 'El tipo de bien seleccionado no existe.',
            'acquisition_date.required' => 'La fecha de adquisición es obligatoria.',
            'acquisition_date.date' => 'La fecha de adquisición no es válida.',
            'origin_id.required' => 'El origen es obligatorio.',
            'origin_id.exists' => 'El origen seleccionado no existe.',
            'physical_condition_id.required' => 'El estado físico es obligatorio.',
            'physical_condition_id.exists' => 'El estado físico seleccionado no existe.',
            'purchase_value.required' => 'El valor de compra es obligatorio.',
            'purchase_value.numeric' => 'El valor de compra debe ser numérico.',
            'purchase_value.min' => 'El valor de compra no puede ser negativo.',
        ];

        $data = $request->validate($rules, $messages);

        $asset = null;

        DB::transaction(function () use (&$asset, $data, $id) {
            /** @var \App\Models\fixedasset\FixedAsset $asset */
            $asset = FixedAsset::findOrFail($id);

            $categoryChanged = $asset->fa_category_id !== (int) $data['fa_category_id'];

            if ($categoryChanged) {
                $institution = Institution::select('code')
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->first();

                $institutionCode = $institution?->code ?? '0000';

                /** @var \App\Models\fixedasset\Category $category */
                $category = Category::with('specific:id,code')
                    ->findOrFail($data['fa_category_id']);

                $specificCode = $category->specific?->code ?? '0000';
                $categoryCode = $category->code;

                $lastCorrelative = FixedAsset::where('fa_category_id', $category->id)->max('correlative');
                $nextCorrelativeNumber = $lastCorrelative ? (int)$lastCorrelative + 1 : 1;
                $correlative = str_pad($nextCorrelativeNumber, 4, '0', STR_PAD_LEFT);

                $code = $institutionCode . '-' . $specificCode . '-' . $categoryCode . '-' . $correlative;

                $asset->fa_category_id = $category->id;
                $asset->code = $code;
                $asset->correlative = $correlative;
            }

            $statusId = (int) ($data['depreciation_status_id'] ?? $asset->depreciation_status_id ?? DepreciationStatus::ACTIVE);
            $responsible = trim((string) ($data['current_responsible'] ?? ''));
            if ($statusId === DepreciationStatus::PENDING_ASSIGNMENT) {
                $responsible = '';
            }

            $asset->description = $data['description'];
            $asset->brand = $data['brand'] ?? null;
            $asset->model = $data['model'] ?? null;
            $asset->serial_number = $data['serial_number'] ?? null;
            $asset->location = $data['location'];
            $asset->policy = $data['policy'] ?? null;
            $asset->current_responsible = $responsible !== '' ? $responsible : null;
            $asset->organizational_unit_id = $data['organizational_unit_id'];
            $asset->asset_type_id = $data['asset_type_id'] ?? null;
            $asset->acquisition_date = $data['acquisition_date'];
            $asset->supplier = $data['supplier'] ?? null;
            $asset->invoice = $data['invoice'] ?? null;
            $asset->origin_id = $data['origin_id'];
            $asset->physical_condition_id = $data['physical_condition_id'];
            $asset->additional_description = $data['additional_description'] ?? null;
            $asset->measurements = $data['measurements'] ?? null;
            $asset->observation = $data['observation'] ?? null;
            $asset->is_insured = $data['is_insured'] ?? false;
            $asset->insured_description = $data['insured_description'] ?? null;
            $asset->purchase_value = $data['purchase_value'];
            $asset->depreciation_status_id = $statusId;
            $asset->save();

            $asset->load([
                'category:id,fa_specific_id,code,name,useful_life',
                'category.specific:id,code,name',
                'organizationalUnit:id,name',
                'assetType:id,name',
                'origin:id,name',
                'physicalCondition:id,name',
                'depreciationStatus:id,name',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Activo fijo actualizado correctamente.',
            'data' => $asset,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $asset = FixedAsset::findOrFail($id);
        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activo fijo eliminado correctamente.',
        ], 200);
    }

    /**
     * Importar activos fijos desde archivo Excel (síncrono, legado).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'file.required' => 'El archivo es obligatorio.',
            'file.file' => 'Debe ser un archivo válido.',
            'file.mimes' => 'El archivo debe ser de tipo Excel (.xlsx, .xls) o CSV.',
            'file.max' => 'El archivo no puede superar los 20MB.',
        ]);

        try {
            $import = new FixedAssetImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Importación completada.',
                'data' => [
                    'imported' => $import->imported,
                    'skipped' => $import->skipped,
                    'duplicates' => $import->duplicates,
                    'persons_created' => $import->personsCreated,
                    'persons_reused' => $import->personsReused,
                    'errors' => $import->errors,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inicia migración por chunks (sube archivo y prepara el job).
     */
    public function importStart(Request $request, FixedAssetMigrationService $migration)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
            'fresh' => 'nullable|boolean',
        ], [
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser Excel (.xlsx o .xls).',
            'file.max' => 'El archivo no puede superar los 20MB.',
        ]);

        try {
            $fresh = $request->boolean('fresh');
            $data = $migration->start($request->file('file'), $fresh);

            return response()->json([
                'success' => true,
                'message' => 'Archivo cargado. Iniciando migración.',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo preparar la migración: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesa el siguiente chunk de la migración.
     */
    public function importProcess(Request $request, FixedAssetMigrationService $migration)
    {
        $request->validate([
            'job_id' => 'required|string',
            'chunk_size' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $state = $migration->process(
                $request->input('job_id'),
                $request->integer('chunk_size') ?: null
            );

            return response()->json([
                'success' => true,
                'message' => $state['message'] ?? 'OK',
                'data' => $state,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consulta el avance de una migración.
     */
    public function importProgress(string $jobId, FixedAssetMigrationService $migration)
    {
        try {
            $state = $migration->progress($jobId);

            return response()->json([
                'success' => true,
                'data' => $state,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}

