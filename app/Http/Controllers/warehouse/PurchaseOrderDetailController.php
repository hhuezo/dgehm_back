<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\PurchaseOrderDetail;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderDetailController extends Controller
{
    public function show(string $id)
    {
        $details = PurchaseOrderDetail::with('product')->where('purchase_order_id', $id)->get();

        return response()->json([
            'success' => true,
            'data'    => $details,
        ]);
    }


    public function store(Request $request)
    {
        $rules = [
            'purchase_order_id' => 'required|exists:wh_purchase_order,id',
            'product_id'        => 'required|exists:wh_products,id',
            'quantity'          => 'required|numeric|min:1',
            'unit_price'        => 'required|numeric|min:0.01',
            'id'                => 'nullable|sometimes|exists:wh_purchase_order_items,id',
        ];

        $messages = [
            'required'                  => 'El campo ":attribute" es obligatorio.',
            'numeric'                   => 'El campo ":attribute" debe ser un número válido.',
            'quantity.min'              => 'La cantidad debe ser al menos :min.',
            'unit_price.min'            => 'El precio unitario debe ser mayor a cero (0.00).',

            'purchase_order_id.exists'  => 'La orden de compra asociada no es válida.',
            'product_id.exists'         => 'El producto seleccionado no es válido o no existe.',
        ];

        $attributes = [
            'purchase_order_id' => 'ID de la orden de compra',
            'product_id'        => 'producto',
            'quantity'          => 'cantidad',
            'unit_price'        => 'precio unitario',
        ];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación al guardar el ítem.',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $data = $validator->validated();
        $data['subtotal'] = $data['quantity'] * $data['unit_price'];

        try {
            if (isset($data['id'])) {
                $item = PurchaseOrderDetail::find($data['id']);
                if (!$item) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ítem de Orden no encontrado para actualizar.',
                    ], 404);
                }

                $item->update($data);
                $action = 'updated';
            } else {
                $item = PurchaseOrderDetail::updateOrCreate(
                    [
                        'purchase_order_id' => $data['purchase_order_id'],
                        'product_id'        => $data['product_id'],
                    ],
                    $data
                );
                $action = $item->wasRecentlyCreated ? 'created' : 'updated';
            }

            $item->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Ítem de Orden ' . ($action === 'created' ? 'creado' : 'actualizado') . ' exitosamente.',
                'data'    => $item,
            ], 200);

        } catch (QueryException $e) {
            // Error común si se intenta crear un producto que ya existe en la orden (violación de índice unique)
            if ($e->getCode() == 23000) { // Código de error de integridad de MySQL/PostgreSQL
                 return response()->json([
                    'success' => false,
                    'message' => 'El producto seleccionado ya existe en esta orden de compra. Use el modo edición para cambiar la cantidad o precio.',
                    'error'   => $e->getMessage(),
                ], 409); // 409 Conflict
            }

            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos al guardar el ítem.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
