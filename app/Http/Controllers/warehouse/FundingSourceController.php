<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\FundingSource;
use App\Models\warehouse\PurchaseOrder;
use Illuminate\Http\Request;

class FundingSourceController extends Controller
{
    public function index(Request $request)
    {
        $query = FundingSource::select('id', 'name', 'is_active');

        if (!$request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        $fundingSources = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $fundingSources,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:wh_funding_sources,name',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una fuente de financiamiento con este nombre.',
        ];

        $request->validate($rules, $messages);

        $fundingSource = FundingSource::create([
            'name'      => $request->name,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fuente de financiamiento creada correctamente.',
            'data'    => $fundingSource,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $rules = [
            'name'      => 'required|unique:wh_funding_sources,name,' . $id,
            'is_active' => 'sometimes|boolean',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique'   => 'Ya existe una fuente de financiamiento con este nombre.',
        ];

        $request->validate($rules, $messages);

        $fundingSource = FundingSource::findOrFail($id);
        $fundingSource->name = $request->name;

        if ($request->has('is_active')) {
            $fundingSource->is_active = $request->boolean('is_active');
        }

        $fundingSource->save();

        return response()->json([
            'success' => true,
            'message' => 'Fuente de financiamiento actualizada correctamente.',
            'data'    => $fundingSource,
        ], 200);
    }

    public function destroy(string $id)
    {
        $fundingSource = FundingSource::findOrFail($id);

        if (PurchaseOrder::where('wh_funding_sources_id', $fundingSource->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la fuente está asociada a órdenes de compra.',
            ], 422);
        }

        $fundingSource->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fuente de financiamiento eliminada correctamente.',
        ], 200);
    }
}
