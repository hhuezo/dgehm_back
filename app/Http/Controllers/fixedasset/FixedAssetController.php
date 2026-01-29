<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\AssetClass;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = FixedAsset::select(
            'id',
            'fa_class_id',
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
            'asset_type',
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
            'purchase_value'
        )
            ->with([
                'assetClass:id,fa_specific_id,code,name,useful_life',
                'assetClass.specific:id,code,name',
                'organizationalUnit:id,name',
                'origin:id,name',
                'physicalCondition:id,name',
            ])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assets,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'fa_class_id' => 'required|exists:fa_classes,id',
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:150',
            'location' => 'required|string|max:255',
            'policy' => 'nullable|string|max:150',
            'current_responsible' => 'required|string|max:255',
            'organizational_unit_id' => 'required|exists:fa_organizational_units,id',
            'asset_type' => 'required|string|max:150',
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
        ];

        $messages = [
            'fa_class_id.required' => 'La clase es obligatoria.',
            'fa_class_id.exists' => 'La clase seleccionada no existe.',
            'description.required' => 'La descripción es obligatoria.',
            'location.required' => 'La ubicación es obligatoria.',
            'current_responsible.required' => 'El responsable actual es obligatorio.',
            'organizational_unit_id.required' => 'La unidad organizativa es obligatoria.',
            'organizational_unit_id.exists' => 'La unidad organizativa seleccionada no existe.',
            'asset_type.required' => 'El tipo de bien es obligatorio.',
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

            /** @var \App\Models\fixedasset\AssetClass $class */
            $class = AssetClass::with('specific:id,code')
                ->findOrFail($data['fa_class_id']);

            $specificCode = $class->specific?->code ?? '0000';
            $classCode = $class->code;

            $lastCorrelative = FixedAsset::where('fa_class_id', $class->id)->max('correlative');
            $nextCorrelativeNumber = $lastCorrelative ? (int)$lastCorrelative + 1 : 1;
            $correlative = str_pad($nextCorrelativeNumber, 4, '0', STR_PAD_LEFT);

            $code = $institutionCode . '-' . $specificCode . '-' . $classCode . '-' . $correlative;

            $asset = new FixedAsset();
            $asset->fa_class_id = $class->id;
            $asset->code = $code;
            $asset->correlative = $correlative;
            $asset->description = $data['description'];
            $asset->brand = $data['brand'] ?? null;
            $asset->model = $data['model'] ?? null;
            $asset->serial_number = $data['serial_number'] ?? null;
            $asset->location = $data['location'];
            $asset->policy = $data['policy'] ?? null;
            $asset->current_responsible = $data['current_responsible'];
            $asset->organizational_unit_id = $data['organizational_unit_id'];
            $asset->asset_type = $data['asset_type'];
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
            $asset->save();

            $asset->load([
                'assetClass:id,fa_specific_id,code,name,useful_life',
                'assetClass.specific:id,code,name',
                'organizationalUnit:id,name',
                'origin:id,name',
                'physicalCondition:id,name',
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
            'fa_class_id' => 'required|exists:fa_classes,id',
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:150',
            'location' => 'required|string|max:255',
            'policy' => 'nullable|string|max:150',
            'current_responsible' => 'required|string|max:255',
            'organizational_unit_id' => 'required|exists:fa_organizational_units,id',
            'asset_type' => 'required|string|max:150',
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
        ];

        $messages = [
            'fa_class_id.required' => 'La clase es obligatoria.',
            'fa_class_id.exists' => 'La clase seleccionada no existe.',
            'description.required' => 'La descripción es obligatoria.',
            'location.required' => 'La ubicación es obligatoria.',
            'current_responsible.required' => 'El responsable actual es obligatorio.',
            'organizational_unit_id.required' => 'La unidad organizativa es obligatoria.',
            'organizational_unit_id.exists' => 'La unidad organizativa seleccionada no existe.',
            'asset_type.required' => 'El tipo de bien es obligatorio.',
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

            $classChanged = $asset->fa_class_id !== (int) $data['fa_class_id'];

            if ($classChanged) {
                $institution = Institution::select('code')
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->first();

                $institutionCode = $institution?->code ?? '0000';

                /** @var \App\Models\fixedasset\AssetClass $class */
                $class = AssetClass::with('specific:id,code')
                    ->findOrFail($data['fa_class_id']);

                $specificCode = $class->specific?->code ?? '0000';
                $classCode = $class->code;

                $lastCorrelative = FixedAsset::where('fa_class_id', $class->id)->max('correlative');
                $nextCorrelativeNumber = $lastCorrelative ? (int)$lastCorrelative + 1 : 1;
                $correlative = str_pad($nextCorrelativeNumber, 4, '0', STR_PAD_LEFT);

                $code = $institutionCode . '-' . $specificCode . '-' . $classCode . '-' . $correlative;

                $asset->fa_class_id = $class->id;
                $asset->code = $code;
                $asset->correlative = $correlative;
            }

            $asset->description = $data['description'];
            $asset->brand = $data['brand'] ?? null;
            $asset->model = $data['model'] ?? null;
            $asset->serial_number = $data['serial_number'] ?? null;
            $asset->location = $data['location'];
            $asset->policy = $data['policy'] ?? null;
            $asset->current_responsible = $data['current_responsible'];
            $asset->organizational_unit_id = $data['organizational_unit_id'];
            $asset->asset_type = $data['asset_type'];
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
            $asset->save();

            $asset->load([
                'assetClass:id,fa_specific_id,code,name,useful_life',
                'assetClass.specific:id,code,name',
                'organizationalUnit:id,name',
                'origin:id,name',
                'physicalCondition:id,name',
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
}

