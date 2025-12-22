<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\warehouse\PurchaseOrder;
use App\Models\warehouse\PurchaseOrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PDF;

class PurchaseOrderController extends Controller
{

    public function index()
    {
        $orders = PurchaseOrder::with('supplier')->orderBy('id','desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'supplier_id'              => 'required|exists:wh_suppliers,id',
            'order_number'             => 'required|string|max:50|unique:wh_purchase_order,order_number',
            'invoice_number'           => 'required|string|max:50|unique:wh_purchase_order,invoice_number',
            'budget_commitment_number' => 'required|string|max:50',
            'acta_date'                => 'required|date_format:Y-m-d H:i:s',
            'reception_time'           => 'required|date_format:Y-m-d H:i:s',
            'supplier_representative'  => 'required|string|max:150',
            'invoice_date'             => 'required|date_format:Y-m-d H:i:s',
            'total_amount'             => 'required|numeric|min:0.01|max:9999999999.99',
            'administrative_manager'   => 'required|string|max:150',
            'administrative_technician' => 'required|string|max:150',
        ];

        $messages = [
            'required' => 'El campo ":attribute" es obligatorio.',
            'string'   => 'El campo ":attribute" debe ser texto.',
            'date'     => 'El campo ":attribute" debe ser una fecha válida.',
            'numeric'  => 'El campo ":attribute" debe ser un número.',
            'min'      => 'El campo ":attribute" debe ser mayor a cero (0.00).',

            'supplier_id.exists'         => 'El proveedor seleccionado no es válido o no existe.',
            'order_number.unique'        => 'El número de Orden de Compra ya existe en el sistema.',
            'invoice_number.unique'      => 'El número de Factura ya existe en el sistema y debe ser único.',
            'date_format'                => 'El formato del campo ":attribute" debe ser AAAA-MM-DD HH:MM:SS.',
            'total_amount.min'           => 'El monto total debe ser mayor a cero (0.00).',

            'attributes' => [
                'supplier_id'              => 'proveedor',
                'order_number'             => 'número de orden',
                'invoice_number'           => 'número de factura',
                'budget_commitment_number' => 'número de compromiso presupuestario',
                'acta_date'                => 'fecha y hora del acta',
                'reception_time'           => 'fecha y hora de recepción',
                'supplier_representative'  => 'representante del proveedor',
                'invoice_date'             => 'fecha y hora de la factura',
                'total_amount'             => 'monto total',
                'administrative_manager'   => 'gerente administrativo',
                'administrative_technician' => 'técnico administrativo',
            ],
        ];

        $request->validate($rules, $messages);


        try {
            $order = PurchaseOrder::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Orden de Compra creada exitosamente.',
                'data'    => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la Orden de Compra.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $order = PurchaseOrder::with('supplier')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden de Compra no encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }






    public function update(Request $request, string $id)
    {
        $order = PurchaseOrder::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden de Compra no encontrada para actualizar.',
            ], 404);
        }

        $request->validate([
            'supplier_id'             => 'required|exists:wh_suppliers,id',
            'order_number'            => ['required', 'string', 'max:50', Rule::unique('wh_purchase_order')->ignore($order->id)],
            'invoice_number'          => ['required', 'string', 'max:50', Rule::unique('wh_purchase_order')->ignore($order->id)],
            'budget_commitment_number' => 'nullable|string|max:50',
            'acta_date'               => 'required|date',
            'reception_time'          => 'required|date_format:Y-m-d H:i:s',
            'supplier_representative' => 'required|string|max:150',
            'invoice_date'            => 'required|date',
            'total_amount'            => 'required|numeric|min:0|max:9999999999.99',
            'administrative_manager'  => 'required|string|max:150',
            'administrative_technician' => 'required|string|max:150',
        ]);

        try {
            $order->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Orden de Compra actualizada exitosamente.',
                'data'    => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la Orden de Compra.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $order = PurchaseOrder::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden de Compra no encontrada para eliminar.',
            ], 404);
        }

        try {
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Orden de Compra eliminada exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar. Verifique que no existan detalles o recepciones asociadas a esta Orden.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function reportActa(string $id)
    {
        $order = PurchaseOrder::with('supplier')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Orden de Compra no encontrada.',
            ], 404);
        }

        // 🔹 FORZAR ESPAÑOL
        Carbon::setLocale('es');

        $actaDate = Carbon::parse($order->acta_date);

        $data = [
            'order' => $order,

            'acta_time_part'    => $actaDate->format('H'),
            'acta_minutes_part' => $actaDate->format('i'),
            'acta_date_part'    => $actaDate->format('d'),
            'acta_month_part'   => $actaDate->translatedFormat('F'), // ← español
            'acta_year_part'    => $actaDate->format('Y'),
        ];

        $pdf = PDF::loadView('reports.acta_recepcion', $data);

        $filename = 'Acta_Recepcion_' . $order->order_number . '.pdf';

        return $pdf->download($filename);
    }
}
