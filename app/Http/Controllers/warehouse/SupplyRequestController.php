<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\Office;
use App\Models\warehouse\Product;
use App\Models\warehouse\SupplyRequest;
use App\Models\warehouse\SupplyRequestDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Type\Integer;

class SupplyRequestController extends Controller
{
    public function index()
    {
        $supplyRequests = SupplyRequest::with('status')->with('requester')->with('immediateBoss')->get();

        return response()->json([
            'success' => true,
            'data'    => $supplyRequests,
        ]);
    }

    public function getBoss(String $officeId)
    {
        $office = Office::findOrFail($officeId);

        $bosses = $office->users()
            ->whereHas('roles', function ($query) {
                $query->where('id', 4);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bosses,
        ]);
    }


    public function store(Request $request)
    {
        $rules = [
            'date'              => 'required|date',
            'office_id'      => 'required|exists:wh_offices,id',
            'immediate_boss_id' => 'required|exists:users,id',
            'requester_id'      => 'required|exists:users,id',
            'observation'       => 'nullable|string|max:1000',
        ];
        $messages = [
            // Mensajes directos para campos requeridos
            'date.required'              => 'La fecha de solicitud es obligatoria.',
            'office_id.required'      => 'La oficina solicitante es obligatoria.',
            'immediate_boss_id.required' => 'El jefe inmediato es obligatorio.',
            'requester_id.required'      => 'El ID del solicitante es obligatorio.',
            'observation.required'       => 'La observación es obligatoria.', // Aunque 'observation' es nullable, la regla 'required' aquí aplicaría si la hubieras puesto. La mantengo por si la regla de negocio cambia.

            // Mensajes directos para reglas de formato y existencia
            'date.date'                  => 'La fecha de solicitud debe tener un formato de fecha válido.',

            'office_id.exists'        => 'La oficina seleccionada no es válida.',
            'immediate_boss_id.exists'   => 'El jefe inmediato seleccionado no existe.',
            'requester_id.exists'        => 'El usuario solicitante no existe.',
            'observation.string'         => 'La observación debe ser texto.',
        ];

        $request->validate($rules, $messages);

        $requesterId = $request->input('requester_id');

        try {
            $supplyRequest = new SupplyRequest();

            $supplyRequest->date = $request->input('date');
            $supplyRequest->observation = $request->input('observation');
            $supplyRequest->requester_id = $requesterId;
            $supplyRequest->immediate_boss_id = $request->input('immediate_boss_id');
            $supplyRequest->office_id = $request->input('office_id');
            $supplyRequest->status_id = 1;

            $supplyRequest->save();

            $supplyRequest->load('status', 'requester');

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos creada correctamente.',
                'data'    => $supplyRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la solicitud.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function show(string $id)
    {
        $supplyRequest = SupplyRequest::with('status')->with('requester')->with('office')->with('immediateBoss')->find($id);

        return response()->json([
            'success' => true,
            'data'    => $supplyRequest,
        ]);
    }

    public function send(string $id)
    {
        try {
            $supplyRequest = SupplyRequest::findOrFail($id);


            if ($supplyRequest->details->count() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud debe tener al menos un detalle antes de ser enviada.',
                ], 403); // HTTP_FORBIDDEN
            }

            if ($supplyRequest->status_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes que están en estado Pendiente.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyRequest->status_id = 2;
            $supplyRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente. Estado: Aprobado.',
                'data' => $supplyRequest,
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


    public function approve(Request $request, string $id)
    {
        try {
            $supplyRequest = SupplyRequest::findOrFail($id);

            if ($supplyRequest->status_id !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar solicitudes que están en estado Enviada.',
                ], 403); // HTTP_FORBIDDEN
            }

            $supplyRequest->approved_by_id = $request->userId;
            $supplyRequest->status_id = 3;
            $supplyRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente. Estado: Aprobado.',
                'data' => $supplyRequest,
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

    public function reject(Request $request, string $id)
    {
        try {
            $supplyRequest = SupplyRequest::findOrFail($id);

            if ($supplyRequest->status_id !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden rechazar solicitudes que están en estado Enviada.',
                ], 403); // HTTP_FORBIDDEN
            }
            $supplyRequest->rejected_by_id = $request->userId;
            $supplyRequest->status_id = 5;
            $supplyRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos rechazada correctamente. Estado: Rechazado.',
                'data' => $supplyRequest,
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


    public function finalize(Request $request, string $id)
    {
        DB::beginTransaction();

        try {

            $supplyRequest = SupplyRequest::findOrFail($id);

            if ($supplyRequest->status_id !== 3) {
                throw ValidationException::withMessages([
                    'status' => ['La solicitud no se encuentra en estado Aprobado.'],
                ]);
            }

            if ($supplyRequest->details->where('delivered_quantity',  0)->count() > 0 && $supplyRequest->details->where('delivered_quantity', '>', 0)->count() == 0) {
                throw ValidationException::withMessages([
                    'error' => ['La solicitud debe tener cantidades entregadas mayores a cero en todos sus detalles.'],
                ]);
            }

            $details = SupplyRequestDetail::where('supply_request_id', $id)
                ->where('delivered_quantity', '>', 0)
                ->get();

            $kardexToInsert = [];
            $validationErrors = [];

            foreach ($details as $detail) {

                $product = Product::find($detail->product_id);
                $productName = $product ? $product->name : 'Producto ID ' . $detail->product_id;

                try {

                    $distribution = $this->resolveKardexStock(
                        $detail->product_id,
                        $detail->delivered_quantity
                    );

                    foreach ($distribution as $item) {
                        $kardexToInsert[] = [
                            'purchase_order_id' => $item['purchase_order_id'],
                            'product_id'        => $item['product_id'],
                            'movement_type'     => 2,
                            'quantity'          => $item['quantity'],
                            'unit_price'        => $item['unit_price'],
                            'subtotal'          => $item['subtotal'],
                            'supply_request_id' => $id,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ];
                    }
                } catch (\Exception $e) {

                    $validationErrors["products.{$detail->product_id}"][] =
                        "Existencia insuficiente para el producto: {$productName}";
                }
            }

            if (!empty($validationErrors)) {
                DB::rollBack();
                throw ValidationException::withMessages($validationErrors);
            }

            DB::table('wh_kardex')->insert($kardexToInsert);

            $supplyRequest->delivery_date = $request->delivery_date;
            $supplyRequest->delivered_by_id = $request->userId;
            $supplyRequest->status_id = 4;
            $supplyRequest->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de insumos aprobada correctamente.',
            ], 200);
        } catch (ValidationException $e) {

            DB::rollBack();
            throw $e;
        } catch (ModelNotFoundException $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'La Solicitud de Insumos no fue encontrada.',
            ], 404);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error al aprobar la solicitud de insumos', [
                'supply_request_id' => $id,
                'exception'         => $e->getMessage(),
                'file'              => $e->getFile(),
                'line'              => $e->getLine(),
                'trace'             => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al aprobar la solicitud.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }






    public function resolveKardexStock(string $id, int $delivered_quantity)
    {
        $result = [];
        $remaining = $delivered_quantity;

        $orders = DB::table('wh_kardex')
            ->select(
                'purchase_order_id',
                'product_id',
                'unit_price',
                DB::raw("
                SUM(
                    CASE
                        WHEN movement_type = 1 THEN quantity
                        WHEN movement_type = 2 THEN -quantity
                        ELSE 0
                    END
                ) AS stock
            ")
            )
            ->where('product_id', $id)
            ->groupBy('purchase_order_id', 'product_id', 'unit_price')
            ->havingRaw('stock > 0')
            ->orderBy('purchase_order_id')
            ->get();

        foreach ($orders as $order) {

            if ($remaining <= 0) {
                break;
            }

            $take = min($order->stock, $remaining);

            $result[] = [
                'purchase_order_id' => $order->purchase_order_id,
                'product_id'        => $order->product_id,
                'quantity'          => $take,
                'unit_price'        => (float) $order->unit_price,
                'subtotal'          => round($take * $order->unit_price, 4),
            ];

            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \Exception('Existencia insuficiente para el producto solicitado');
        }

        return $result;
    }





    public function RequestFormReport(string $id)
    {
        // 1. Solicitud (CABECERA)
        $request = DB::table('wh_supply_request')
            ->join('users as requester', 'wh_supply_request.requester_id', '=', 'requester.id')
            ->leftJoin('users as boss', 'wh_supply_request.immediate_boss_id', '=', 'boss.id')
            ->leftJoin('users as delivered', 'wh_supply_request.delivered_by_id', '=', 'delivered.id')
            ->join('wh_offices', 'wh_supply_request.office_id', '=', 'wh_offices.id')
            ->select(
                'wh_supply_request.*',
                DB::raw("CONCAT(requester.name,' ',requester.lastname) as requester_name"),
                DB::raw("CONCAT(boss.name,' ',boss.lastname) as boss_name"),
                DB::raw("CONCAT(delivered.name,' ',delivered.lastname) as delivered_name"),
                'wh_offices.name as office_name'
            )
            ->where('wh_supply_request.id', $id)
            ->first();

        if (!$request) {
            abort(404, 'Solicitud no encontrada');
        }

        // 2. PRODUCTOS DE LA SOLICITUD (TABLA CORRECTA)
        $products = DB::table('wh_supply_request_detail')
            ->join('wh_products', 'wh_supply_request_detail.product_id', '=', 'wh_products.id')
            ->join('wh_measures', 'wh_products.measure_id', '=', 'wh_measures.id')
            ->select(
                'wh_products.name as product_name',
                'wh_measures.name as measure_name',
                'wh_supply_request_detail.quantity as requested_quantity',
                'wh_supply_request_detail.delivered_quantity'
            )
            ->where('wh_supply_request_detail.supply_request_id', $id)
            ->orderBy('wh_products.name')
            ->get();

        // 3. PDF
        $pdf = Pdf::loadView('reports.request_form', [
            'request'  => $request,
            'products' => $products,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->download("Solicitud_Insumos_{$id}.pdf");
    }
}
