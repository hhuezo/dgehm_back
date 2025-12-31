<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\SupplyReturnDetail;
use Illuminate\Http\Request;

class SupplyReturnDetailController extends Controller
{

    public function store(Request $request)
    {
        $rules = [
            'supply_return_id' => 'required|integer|exists:wh_supply_returns,id',
            'product_id' => 'required|integer|exists:wh_products,id',
            'returned_quantity' => 'required|integer|min:1',
            'observation' => 'nullable|string|max:1000',
        ];

        $messages = [
            'supply_return_id.required' => 'El ID de la devolución es obligatorio.',
            'supply_return_id.integer' => 'El ID de la devolución debe ser un número entero.',
            'supply_return_id.exists' => 'La devolución seleccionada no es válida.',

            'product_id.required' => 'El producto es obligatorio.',
            'product_id.integer' => 'El ID del producto debe ser un número entero.',
            'product_id.exists' => 'El producto seleccionado no es válido.',

            'returned_quantity.required' => 'La cantidad devuelta es obligatoria.',
            'returned_quantity.integer' => 'La cantidad devuelta debe ser un número entero.',
            'returned_quantity.min' => 'La cantidad devuelta debe ser al menos 1.',

            'observation.string' => 'La observación debe ser texto.',
            'observation.max' => 'La observación no puede exceder los 1000 caracteres.',
        ];

        $validated = $request->validate($rules, $messages);

        $detail = new SupplyReturnDetail();
        $detail->supply_return_id = $validated['supply_return_id'];
        $detail->product_id = $validated['product_id'];
        $detail->returned_quantity = $validated['returned_quantity'];
        $detail->observation = $validated['observation'] ?? null;
        $detail->save();

        return response()->json([
            'success' => true,
            'data' => $detail,
        ], 201);
    }


    public function show(string $id)
    {
        $suppluReturnDetails = SupplyReturnDetail::with([
            'product' => function ($query) {
                $query->select('id', 'name', 'measure_id');
            },
            'product.measure' => function ($query) {
                $query->select('id', 'name');
            }
        ])
            ->where('supply_return_id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $suppluReturnDetails,
        ]);
    }


    public function update(Request $request, string $id)
    {
        $rules = [
            'supply_return_id' => 'sometimes|required|integer|exists:wh_supply_returns,id',
            'product_id' => 'sometimes|required|integer|exists:wh_products,id',
            'returned_quantity' => 'sometimes|required|integer|min:1',
            'observation' => 'nullable|string|max:1000',
        ];

        $messages = [
            'supply_return_id.required' => 'El ID de la devolución es obligatorio.',
            'supply_return_id.integer' => 'El ID de la devolución debe ser un número entero.',
            'supply_return_id.exists' => 'La devolución seleccionada no es válida.',

            'product_id.required' => 'El producto es obligatorio.',
            'product_id.integer' => 'El ID del producto debe ser un número entero.',
            'product_id.exists' => 'El producto seleccionado no es válido.',

            'returned_quantity.required' => 'La cantidad devuelta es obligatoria.',
            'returned_quantity.integer' => 'La cantidad devuelta debe ser un número entero.',
            'returned_quantity.min' => 'La cantidad devuelta debe ser al menos 1.',

            'observation.string' => 'La observación debe ser texto.',
            'observation.max' => 'La observación no puede exceder los 1000 caracteres.',
        ];

        $validated = $request->validate($rules, $messages);

        $detail = SupplyReturnDetail::find($id);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Detalle de devolución no encontrado.',
            ], 404);
        }

        $detail->supply_return_id = $validated['supply_return_id'];
        $detail->product_id = $validated['product_id'];
        $detail->returned_quantity = $validated['returned_quantity'];
        $detail->observation = $validated['observation'] ?? null;
        $detail->save();

        return response()->json([
            'success' => true,
            'data' => $detail,
        ], 200);
    }


    public function destroy(string $id)
    {
        try {
            $detail = SupplyReturnDetail::find($id);

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detalle de devolución no encontrado.',
                ], 404);
            }

            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Detalle de devolución eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el detalle de devolución.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
