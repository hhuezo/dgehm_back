<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Product;
use App\Models\warehouse\SupplyReturn;
use App\Models\warehouse\SupplyReturnDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SupplyReturnController extends Controller
{
    public function index()
    {
        $supplyReturns = SupplyReturn::with([
            'returnedBy:id,name,lastname',
            'office:id,name',
            'immediateSupervisor:id,name,lastname',
            'receivedBy:id,name,lastname'
        ])->get();

        return response()->json([
            'success' => true,
            'data'    => $supplyReturns,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'return_date'            => 'required|date',
            'returned_by_id'         => 'required|exists:users,id',
            'wh_office_id'           => 'required|exists:wh_offices,id',
            'immediate_supervisor_id' => 'required|exists:users,id',
            'received_by_id'         => 'required|exists:users,id',
            'phone_extension'        => 'nullable|string|max:10',
            'general_observations'   => 'nullable|string|max:1000',
        ];

        $messages = [
            'return_date.required'             => 'La fecha de devolución es obligatoria.',
            'return_date.date'                 => 'La fecha de devolución debe tener un formato de fecha válido.',

            'returned_by_id.required'          => 'El usuario que devuelve el suministro es obligatorio.',
            'wh_office_id.required'            => 'La oficina a la que pertenece la devolución es obligatoria.',
            'immediate_supervisor_id.required' => 'El supervisor inmediato es obligatorio.',
            'received_by_id.required'          => 'El usuario que recibe la devolución es obligatorio.',

            'returned_by_id.exists'            => 'El usuario que devuelve no existe en el sistema.',
            'wh_office_id.exists'              => 'La oficina seleccionada no es válida.',
            'immediate_supervisor_id.exists'   => 'El supervisor inmediato seleccionado no existe.',
            'received_by_id.exists'            => 'El usuario receptor seleccionado no existe.',

            'phone_extension.string'           => 'La extensión telefónica debe ser texto.',
            'phone_extension.max'              => 'La extensión telefónica no debe exceder los 10 caracteres.',
            'general_observations.string'      => 'Las observaciones generales deben ser texto.',
            'general_observations.max'         => 'Las observaciones generales no deben exceder los 1000 caracteres.',
        ];

        $request->validate($rules, $messages);

        try {
            $supplyReturn = new SupplyReturn();

            $supplyReturn->return_date = $request->input('return_date');
            $supplyReturn->returned_by_id = $request->input('returned_by_id');
            $supplyReturn->wh_office_id = $request->input('wh_office_id');
            $supplyReturn->immediate_supervisor_id = $request->input('immediate_supervisor_id');
            $supplyReturn->received_by_id = $request->input('received_by_id');

            $supplyReturn->phone_extension = $request->input('phone_extension');
            $supplyReturn->general_observations = $request->input('general_observations');
            $supplyReturn->status_id = 1;

            $supplyReturn->save();


            return response()->json([
                'success' => true,
                'message' => 'Devolución de suministros registrada correctamente.',
                'data'    => $supplyReturn,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la devolución de suministros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $supplyReturn = SupplyReturn::with([
            'returnedBy:id,name,lastname',
            'office:id,name',
            'immediateSupervisor:id,name,lastname',
            'receivedBy:id,name,lastname',
            'status:id,name'
        ])->find($id);

        if (!$supplyReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Devolución de suministros no encontrada.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $supplyReturn,
        ]);
    }




    public function update(Request $request, string $id)
    {
        $supplyReturn = SupplyReturn::find($id);

        if (!$supplyReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Devolución de suministros no encontrada para actualizar.',
                'data'    => null,
            ], 404);
        }

        $rules = [
            'return_date'            => 'required|date',
            'returned_by_id'         => 'required|exists:users,id',
            'wh_office_id'           => 'required|exists:wh_offices,id',
            'immediate_supervisor_id' => 'required|exists:users,id',
            'received_by_id'         => 'required|exists:users,id',
            'phone_extension'        => 'nullable|string|max:10',
            'general_observations'   => 'nullable|string|max:1000',
        ];

        $messages = [
            'return_date.required'             => 'La fecha de devolución es obligatoria.',
            'return_date.date'                 => 'La fecha de devolución debe tener un formato de fecha válido.',

            'returned_by_id.required'          => 'El usuario que devuelve el suministro es obligatorio.',
            'wh_office_id.required'            => 'La oficina a la que pertenece la devolución es obligatoria.',
            'immediate_supervisor_id.required' => 'El supervisor inmediato es obligatorio.',
            'received_by_id.required'          => 'El usuario que recibe la devolución es obligatorio.',

            'returned_by_id.exists'            => 'El usuario que devuelve no existe en el sistema.',
            'wh_office_id.exists'              => 'La oficina seleccionada no es válida.',
            'immediate_supervisor_id.exists'   => 'El supervisor inmediato seleccionado no existe.',
            'received_by_id.exists'            => 'El usuario receptor seleccionado no existe.',

            'phone_extension.string'           => 'La extensión telefónica debe ser texto.',
            'phone_extension.max'              => 'La extensión telefónica no debe exceder los 10 caracteres.',
            'general_observations.string'      => 'Las observaciones generales deben ser texto.',
            'general_observations.max'         => 'Las observaciones generales no deben exceder los 1000 caracteres.',
        ];

        $request->validate($rules, $messages);

        try {
            $supplyReturn->return_date = $request->input('return_date');
            $supplyReturn->returned_by_id = $request->input('returned_by_id');
            $supplyReturn->wh_office_id = $request->input('wh_office_id');
            $supplyReturn->immediate_supervisor_id = $request->input('immediate_supervisor_id');
            $supplyReturn->received_by_id = $request->input('received_by_id');

            $supplyReturn->phone_extension = $request->input('phone_extension');
            $supplyReturn->general_observations = $request->input('general_observations');

            $supplyReturn->save();

            return response()->json([
                'success' => true,
                'message' => 'Devolución de suministros actualizada correctamente.',
                'data'    => $supplyReturn,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la devolución de suministros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $supplyReturn = SupplyReturn::find($id);

        if (!$supplyReturn) {
            return response()->json([
                'success' => false,
                'message' => 'Devolución de suministros no encontrada para eliminar.',
                'data'    => null,
            ], 404);
        }

        try {
            $supplyReturn->delete();

            return response()->json([
                'success' => true,
                'message' => 'Devolución de suministros eliminada correctamente.',
                'data'    => $supplyReturn,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la devolución de suministros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function send(string $id)
    {
        try {
            $supplyReturn = SupplyReturn::findOrFail($id);


            if ($supplyReturn->details->count() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud debe tener al menos un detalle antes de ser enviada.',
                ], 403); // HTTP_FORBIDDEN
            }

            if ($supplyReturn->status_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes que están en estado Pendiente.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyReturn->status_id = 2;
            $supplyReturn->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente. Estado: Aprobado.',
                'data' => $supplyReturn,
            ], 200); // HTTP_OK

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La Solicitud de Insumos (ID: ' . $id . ') no fue encontrada.',
            ], 404); // HTTP_NOT_FOUND
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al aprobar la solicitud: ' . $e->getMessage(),
            ], 500); // HTTP_INTERNAL_SERVER_ERROR
        }
    }


    public function approve(string $id)
    {
        try {
            $supplyReturn = SupplyReturn::findOrFail($id);

            if ($supplyReturn->status_id !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes que están en estado Enviada.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyReturn->status_id = 3;
            $supplyReturn->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente. Estado: Aprobado.',
                'data' => $supplyReturn,
            ], 200); // HTTP_OK

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La Solicitud de Insumos (ID: ' . $id . ') no fue encontrada.',
            ], 404); // HTTP_NOT_FOUND
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al aprobar la solicitud: ' . $e->getMessage(),
            ], 500); // HTTP_INTERNAL_SERVER_ERROR
        }
    }

    public function reject(string $id)
    {
        try {
            $supplyReturn = SupplyReturn::findOrFail($id);

            if ($supplyReturn->status_id !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden rechazar solicitudes que están en estado Enviada.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyReturn->status_id = 5;
            $supplyReturn->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos rechazada correctamente. Estado: Rechazado.',
                'data' => $supplyReturn,
            ], 200); // HTTP_OK

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'La Solicitud de Insumos (ID: ' . $id . ') no fue encontrada.',
            ], 404); // HTTP_NOT_FOUND
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al aprobar la solicitud: ' . $e->getMessage(),
            ], 500); // HTTP_INTERNAL_SERVER_ERROR
        }
    }


    public function finalize(string $id)
    {
        DB::beginTransaction();

        try {
            $supplyReturn = SupplyReturn::findOrFail($id);

            // Verifica que el estado sea Aprobado (ID 3)
            if ($supplyReturn->status_id !== 3) {
                throw ValidationException::withMessages([
                    'status' => ['La devolución no se encuentra en estado Aprobado.'],
                ]);
            }

            // Verifica que haya al menos un ítem con cantidad devuelta (> 0)
            $detailsWithPositiveQuantity = $supplyReturn->details->where('returned_quantity', '>', 0)->count();

            if ($supplyReturn->details->isEmpty() || $detailsWithPositiveQuantity === 0) {
                throw ValidationException::withMessages([
                    'error' => ['La devolución debe tener cantidades devueltas mayores a cero en al menos un ítem.'],
                ]);
            }

            // Se usa supply_return_id para obtener los detalles
            $details = SupplyReturnDetail::where('supply_return_id', $id)->get();

            $kardexToInsert = [];
            $validationErrors = [];

            foreach ($details as $detail) {
                if ($detail->returned_quantity <= 0) {
                    continue;
                }

                $product = Product::find($detail->product_id);
                $productName = $product ? $product->name : 'Producto ID ' . $detail->product_id;

                try {
                    // Obtiene la entrada de Kárdex para la devolución
                    $kardexEntry = $this->resolveKardexReturn(
                        $detail->product_id,
                        $detail->returned_quantity, // Se usa returned_quantity
                        (int) $id
                    );

                    // Prepara el registro de ENTRADA (movement_type = 1)
                    $kardexToInsert[] = [
                        'purchase_order_id' => $kardexEntry['purchase_order_id'],
                        'product_id'        => $kardexEntry['product_id'],
                        'movement_type'     => $kardexEntry['movement_type'],
                        'quantity'          => $kardexEntry['quantity'],
                        'unit_price'        => $kardexEntry['unit_price'],
                        'subtotal'          => $kardexEntry['subtotal'],
                        'supply_request_id' => null, // No aplica para devoluciones
                        'supply_return_id'  => $kardexEntry['supply_return_id'],
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                } catch (\Exception $e) {
                    $validationErrors["products.{$detail->product_id}"][] =
                        "Error de costeo/devolución para el producto: {$productName}. Error: " . $e->getMessage();
                }
            }

            if (!empty($validationErrors)) {
                DB::rollBack();
                throw ValidationException::withMessages($validationErrors);
            }

            // Inserta las entradas de Kárdex
            if (!empty($kardexToInsert)) {
                DB::table('wh_kardex')->insert($kardexToInsert);
            }

            // Cambia el estado a Finalizado (ID 4)
            $supplyReturn->status_id = 4;
            $supplyReturn->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Devolución de insumos finalizada correctamente y stock actualizado.',
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'La Devolución de Insumos no fue encontrada.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar la devolución de insumos', [
                'supply_return_id' => $id,
                'exception'        => $e->getMessage(),
                'file'             => $e->getFile(),
                'line'             => $e->getLine(),
            ]);
            return response()->json([
                'message' => 'Error al finalizar la devolución.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function resolveKardexReturn(string $productId, int $returnedQuantity, int $supplyReturnId)
    {
        // Busca el precio de costo de la última entrada (movement_type 1)
        $lastEntry = DB::table('wh_kardex')
            ->where('product_id', $productId)
            ->where('movement_type', 1)
            ->orderBy('created_at', 'desc')
            ->select('purchase_order_id', 'unit_price')
            ->first();

        if (!$lastEntry) {
            throw new \Exception("No se encontró precio de costo o Purchase Order previo para el Producto ID: {$productId}.");
        }

        $unitPrice = (float) $lastEntry->unit_price;
        $purchaseOrderId = $lastEntry->purchase_order_id;

        $result = [
            'purchase_order_id' => $purchaseOrderId,
            'supply_request_id' => null,
            'supply_return_id'  => $supplyReturnId,
            'product_id'        => (int) $productId,
            'movement_type'     => 1, // Entrada al inventario
            'quantity'          => $returnedQuantity,
            'unit_price'        => $unitPrice,
            'subtotal'          => round($returnedQuantity * $unitPrice, 4),
        ];

        return $result;
    }
}
