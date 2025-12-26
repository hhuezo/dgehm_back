<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\SupplyRequestDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SupplyRequestDetailController extends Controller
{

    public function show(string $id)
    {
        $details = SupplyRequestDetail::with('product.measure')->where('supply_request_id', $id)->get();

        return response()->json([
            'success' => true,
            'data'    => $details,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'supply_request_id' => 'required|integer|exists:wh_supply_request,id',
            'product_id' => 'required|integer|exists:wh_products,id',
            'quantity' => 'required|numeric|min:1',
        ];

        $messages = [
            // Mensajes para campos requeridos
            'supply_request_id.required' => 'El ID de la Solicitud es obligatorio.',
            'product_id.required' => 'El ID del Insumo es obligatorio.',
            'quantity.required' => 'La Cantidad es obligatoria.',

            // Mensajes para reglas de formato y existencia
            'supply_request_id.integer' => 'El ID de la Solicitud debe ser un número entero.',
            'product_id.integer' => 'El ID del Insumo debe ser un número entero.',

            'supply_request_id.exists' => 'La Solicitud de Insumos seleccionada no existe.',
            'product_id.exists' => 'El Insumo seleccionado no es válido.',

            'quantity.numeric' => 'La Cantidad debe ser un valor numérico.',
            'quantity.min' => 'La Cantidad mínima solicitada debe ser 1 o superior.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $item = new SupplyRequestDetail();
            $item->supply_request_id = $validated['supply_request_id'];
            $item->product_id = $validated['product_id'];
            $item->quantity = $validated['quantity'];
            $item->save();

            return response()->json([
                'success' => true,
                'data' => $item,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


   public function update(Request $request, string $id)
    {
        try {
            $item = SupplyRequestDetail::findOrFail($id);
            $requestStatusId = $item->supplyRequest->status_id;

            // --- VALIDACIONES DINÁMICAS ---
            $rules = [];
            $messages = [];

            if ($requestStatusId === 1) { // Estado 1: Pendiente (Modifica la cantidad solicitada)
                $rules = [
                    'quantity' => 'required|numeric|min:1',
                ];
                $messages = [
                    'quantity.required' => 'La Cantidad solicitada es obligatoria para la actualización.',
                    'quantity.numeric' => 'La Cantidad solicitada debe ser un valor numérico.',
                    'quantity.min' => 'La Cantidad mínima solicitada debe ser 1 o superior.',
                ];
            } elseif ($requestStatusId === 2) { // Estado 2: Aprobado (Modifica la cantidad entregada)
                $rules = [
                    'delivered_quantity' => [
                        'required',
                        'numeric',
                        'min:0',
                        // La cantidad entregada no puede ser mayor a la cantidad solicitada
                        'max:' . $item->quantity,
                    ],
                ];
                $messages = [
                    'delivered_quantity.required' => 'La Cantidad entregada es obligatoria.',
                    'delivered_quantity.numeric' => 'La Cantidad entregada debe ser un valor numérico.',
                    'delivered_quantity.min' => 'La Cantidad entregada no puede ser negativa.',
                    'delivered_quantity.max' => 'La Cantidad entregada no puede exceder la cantidad solicitada (' . $item->quantity . ').',
                ];
            } else {
                // Cualquier otro estado (3, 4, etc.) no permite modificación
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede actualizar el ítem porque la solicitud ya fue completada, rechazada o está en otro estado procesado.',
                ], 403); // Código 403 (Forbidden)
            }

            $validated = $request->validate($rules, $messages);

            // --- LÓGICA DE ACTUALIZACIÓN ---

            if ($requestStatusId === 1) {
                // Actualiza la cantidad solicitada
                $item->quantity = $validated['quantity'];
            } elseif ($requestStatusId === 2) {
                // Actualiza la cantidad entregada
                $item->delivered_quantity = $validated['delivered_quantity'];
            }

            $item->save();

            return response()->json([
                'success' => true,
                'data' => $item,
            ], 200); // Código 200 (OK)

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'El detalle de la solicitud (ID: ' . $id . ') no fue encontrado.',
            ], 404); // Código 404 (Not Found)
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura errores de validación
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación al procesar la solicitud.',
            ], 422); // Código 422 (Unprocessable Entity)
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error interno del servidor.',
            ], 500); // Código 500 (Internal Server Error)
        }
    }

    public function destroy(string $id)
    {
        try {
            // 1. Encontrar el ítem por ID
            $item = SupplyRequestDetail::findOrFail($id);

            // 2. Verificar la regla de negocio: Solo editable/eliminable en estado Pendiente (status_id = 1)
            if ($item->supplyRequest->status_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el ítem porque la solicitud ya fue procesada o aprobada.',
                ], Response::HTTP_FORBIDDEN); // 403
            }

            // 3. Eliminar el ítem
            $item->delete();

            // 4. Respuesta Exitosa
            return response()->json([
                'success' => true,
                'message' => 'Detalle de la solicitud eliminado correctamente.',
            ]);
        } catch (ModelNotFoundException $e) {
            // 5. Manejo de Error: Ítem no encontrado
            return response()->json([
                'success' => false,
                'message' => 'El detalle de la solicitud (ID: ' . $id . ') no fue encontrado.',
            ], Response::HTTP_NOT_FOUND); // 404
        } catch (\Exception $e) {
            // 6. Manejo de Errores General
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }
}
