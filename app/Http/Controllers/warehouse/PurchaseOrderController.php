<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\warehouse\PurchaseOrder;
use App\Models\warehouse\PurchaseOrderDetail;
use App\Models\warehouse\SupplyRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\WarehouseInventoryImportService;
use PDF;

class PurchaseOrderController extends Controller
{
    private const FILE_DIRECTORY = 'purchase_orders';

    private const FILE_RULE = 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,gif|max:10240';

    public function index(Request $request)
    {
        if ($request->boolean('lookup')) {
            return $this->lookupByOrderNumber($request);
        }

        $purchase_orders = PurchaseOrder::with(['supplier', 'fundingSource', 'purchaseOrderAdministrator', 'administrativeTechnician'])
            ->withDetailsTotal()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $purchase_orders,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->storeRules(),
            $this->purchaseOrderValidationMessages(),
            $this->purchaseOrderValidationAttributes()
        );

        $orderNumberError = $this->validateOrderNumberUniqueness(
            $data['order_number'],
            (bool) ($data['partial_delivery'] ?? false)
        );

        if ($orderNumberError) {
            return response()->json([
                'success' => false,
                'message' => $orderNumberError,
                'errors'  => ['order_number' => [$orderNumberError]],
            ], 422);
        }

        try {
            $order = new PurchaseOrder();

            $order->supplier_id              = $data['supplier_id'];
            $order->wh_funding_sources_id    = $data['wh_funding_sources_id'];
            $order->order_number             = $data['order_number'];
            $order->invoice_number           = $data['invoice_number'];
            $order->budget_commitment_number = $data['budget_commitment_number'];
            $order->acta_date                = $data['acta_date'];
            $order->reception_date           = $data['reception_date'];
            $order->supplier_representative  = $data['supplier_representative'];
            $order->invoice_date             = $data['invoice_date'];
            $order->purchase_order_administrator_id = $data['purchase_order_administrator_id'];
            $order->administrative_technician_id = $data['administrative_technician_id'];
            $order->partial_delivery = $data['partial_delivery'] ?? false;

            $order->save();

            if ($request->hasFile('file')) {
                $order->file = $this->storePurchaseOrderFile(
                    $request->file('file'),
                    $order->order_number
                );
                $order->save();
            }

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

    /**
     * Importar inventario desde archivo Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'El archivo es obligatorio.',
            'file.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $path);

            $service = new WarehouseInventoryImportService();
            $service->import($fullPath);

            Storage::disk('local')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Importación completada.',
                'data' => [
                    'imported' => $service->imported,
                    'skipped' => $service->skipped,
                    'purchase_order_id' => $service->purchaseOrderId,
                    'errors' => $service->errors,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        $order = PurchaseOrder::with(['supplier', 'fundingSource', 'purchaseOrderAdministrator', 'administrativeTechnician'])
            ->withDetailsTotal()
            ->find($id);

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






    public function lookupByOrderNumber(Request $request)
    {
        $request->validate(
            ['order_number' => 'required|string|max:50'],
            [
                'required' => 'El campo :attribute es obligatorio.',
                'string' => 'El campo :attribute debe ser texto.',
                'max.string' => 'El campo :attribute no debe superar :max caracteres.',
            ],
            ['order_number' => 'número de orden']
        );

        $orderNumber = trim($request->order_number);

        if ($orderNumber === '') {
            return response()->json([
                'success' => true,
                'status'  => 'available',
                'data'    => null,
            ]);
        }

        $orders = PurchaseOrder::with([
            'supplier',
            'fundingSource',
            'purchaseOrderAdministrator',
            'administrativeTechnician',
        ])
            ->where('order_number', $orderNumber)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'status'  => 'available',
                'data'    => null,
            ]);
        }

        if ($orders->contains(fn (PurchaseOrder $order) => !$order->partial_delivery)) {
            return response()->json([
                'success' => false,
                'status'  => 'exists',
                'message' => 'No se puede ingresar una orden ya existente.',
            ], 422);
        }

        $partialOrder = $orders
            ->where('partial_delivery', true)
            ->sortByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'status'  => 'partial_found',
            'data'    => $partialOrder,
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

        $request->validate(
            $this->updateRules($order),
            $this->purchaseOrderValidationMessages(),
            $this->purchaseOrderValidationAttributes()
        );

        $orderNumberError = $this->validateOrderNumberUniqueness(
            $request->order_number,
            $request->boolean('partial_delivery'),
            (int) $order->id
        );

        if ($orderNumberError) {
            return response()->json([
                'success' => false,
                'message' => $orderNumberError,
                'errors'  => ['order_number' => [$orderNumberError]],
            ], 422);
        }

        try {
            $previousOrderNumber = $order->order_number;

            $order->supplier_id              = $request->supplier_id;
            $order->wh_funding_sources_id    = $request->wh_funding_sources_id;
            $order->order_number             = $request->order_number;
            $order->invoice_number           = $request->invoice_number;
            $order->budget_commitment_number = $request->budget_commitment_number;
            $order->acta_date                = $request->acta_date;
            $order->reception_date           = $request->reception_date;
            $order->supplier_representative  = $request->supplier_representative;
            $order->invoice_date             = $request->invoice_date;
            $order->purchase_order_administrator_id = $request->purchase_order_administrator_id;
            $order->administrative_technician_id = $request->administrative_technician_id;
            $order->partial_delivery = $request->boolean('partial_delivery');

            if ($request->hasFile('file')) {
                $order->file = $this->storePurchaseOrderFile(
                    $request->file('file'),
                    $order->order_number,
                    $order->file
                );
            } elseif ($previousOrderNumber !== $order->order_number && $order->file) {
                $order->file = $this->renamePurchaseOrderFile($order->file, $order->order_number);
            }

            $order->save();

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
            $this->deletePurchaseOrderFile($order->file);
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
        $order = PurchaseOrder::with(['supplier', 'fundingSource', 'purchaseOrderAdministrator', 'administrativeTechnician'])
            ->withDetailsTotal()
            ->find($id);

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

        //return view('reports.acta_recepcion',$data);

        $pdf = PDF::loadView('reports.acta_recepcion', $data);

        return $pdf->download("Acta_Recepcion_{$id}.pdf");
    }

    public function downloadFile(string $id)
    {
        $order = PurchaseOrder::find($id);

        if (!$order || !$order->file) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        $path = self::FILE_DIRECTORY . '/' . $order->file;

        if (!Storage::disk('local')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe en el almacenamiento.',
            ], 404);
        }

        return Storage::disk('local')->download($path, $order->file);
    }

    private function storeRules(): array
    {
        return [
            'supplier_id'              => 'required|exists:wh_suppliers,id',
            'wh_funding_sources_id'    => 'required|integer|exists:wh_funding_sources,id',
            'order_number'             => 'required|string|max:50',
            'invoice_number'           => 'required|string|max:50|unique:wh_purchase_order,invoice_number',
            'budget_commitment_number' => 'required|string|max:50',
            'acta_date'                => 'required|date_format:Y-m-d H:i:s',
            'reception_date'           => 'required|date_format:Y-m-d H:i:s',
            'supplier_representative'  => 'required|string|max:150',
            'invoice_date'             => 'required|date_format:Y-m-d H:i:s',
            'purchase_order_administrator_id' => 'required|integer|exists:adm_employees,id',
            'administrative_technician_id' => $this->administrativeTechnicianRule(),
            'file' => self::FILE_RULE,
            'partial_delivery' => 'nullable|in:0,1,true,false',
        ];
    }

    private function updateRules(PurchaseOrder $order): array
    {
        return [
            'supplier_id'             => 'required|exists:wh_suppliers,id',
            'wh_funding_sources_id'   => 'required|integer|exists:wh_funding_sources,id',
            'order_number'            => 'required|string|max:50',
            'invoice_number'          => ['required', 'string', 'max:50', Rule::unique('wh_purchase_order')->ignore($order->id)],
            'budget_commitment_number' => 'required|string|max:50',
            'acta_date'               => 'required|date_format:Y-m-d H:i:s',
            'reception_date'          => 'required|date_format:Y-m-d H:i:s',
            'supplier_representative' => 'required|string|max:150',
            'invoice_date'            => 'required|date_format:Y-m-d H:i:s',
            'purchase_order_administrator_id' => 'required|integer|exists:adm_employees,id',
            'administrative_technician_id' => $this->administrativeTechnicianRule(),
            'file' => self::FILE_RULE,
            'partial_delivery' => 'nullable|in:0,1,true,false',
        ];
    }

    private function administrativeTechnicianRule(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('adm_employees', 'id')->where(function ($query) {
                $query->where('warehouse_manager', true);
            }),
        ];
    }

    private function purchaseOrderValidationMessages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'date_format' => 'El formato del campo :attribute debe ser AAAA-MM-DD HH:MM:SS.',
            'numeric' => 'El campo :attribute debe ser un número.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
            'max.string' => 'El campo :attribute no debe superar :max caracteres.',
            'exists' => 'El :attribute seleccionado no es válido o no existe.',
            'unique' => 'El :attribute ya existe en el sistema.',
            'supplier_id.exists' => 'El proveedor seleccionado no es válido o no existe.',
            'wh_funding_sources_id.exists' => 'La fuente de financiamiento seleccionada no es válida o no existe.',
            'order_number.unique' => 'El número de Orden de Compra ya existe en el sistema.',
            'invoice_number.unique' => 'El número de Factura ya existe en el sistema y debe ser único.',
            'purchase_order_administrator_id.exists' => 'El administrador de orden de compra seleccionado no es válido o no existe.',
            'administrative_technician_id.exists' => 'El encargado de almacén seleccionado no es válido o no está habilitado como encargado de almacén.',
            'file.mimes' => 'El archivo debe ser PDF o imagen (jpg, jpeg, png, webp, gif).',
            'file.max' => 'El archivo no debe superar 10 MB.',
        ];
    }

    private function purchaseOrderValidationAttributes(): array
    {
        return [
            'supplier_id' => 'proveedor',
            'wh_funding_sources_id' => 'fuente de financiamiento',
            'order_number' => 'número de orden',
            'invoice_number' => 'número de factura',
            'budget_commitment_number' => 'número de compromiso presupuestario',
            'acta_date' => 'fecha del acta',
            'reception_date' => 'fecha de recepción',
            'supplier_representative' => 'representante del proveedor',
            'invoice_date' => 'fecha de factura',
            'purchase_order_administrator_id' => 'administrador de orden de compra',
            'administrative_technician_id' => 'encargado de almacén',
            'file' => 'archivo adjunto',
            'partial_delivery' => 'entrega parcial',
        ];
    }

    private function validateOrderNumberUniqueness(
        string $orderNumber,
        bool $isPartial,
        ?int $ignoreId = null
    ): ?string {
        $orderNumber = trim($orderNumber);

        if ($orderNumber === '') {
            return null;
        }

        $query = PurchaseOrder::where('order_number', $orderNumber);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existing = $query->get();

        if ($existing->isEmpty()) {
            return null;
        }

        $hasNonPartial = $existing->contains(fn (PurchaseOrder $order) => !$order->partial_delivery);

        if (!$isPartial) {
            return 'No se puede ingresar una orden ya existente.';
        }

        if ($hasNonPartial) {
            return 'La orden ya existe pero no es de entrega parcial.';
        }

        return null;
    }

    private function storePurchaseOrderFile(UploadedFile $uploadedFile, string $orderNumber, ?string $previousFile = null): string
    {
        $this->deletePurchaseOrderFile($previousFile);

        $filename = $this->buildPurchaseOrderFilename($orderNumber, $uploadedFile);
        $uploadedFile->storeAs(self::FILE_DIRECTORY, $filename, 'local');

        return $filename;
    }

    private function renamePurchaseOrderFile(string $currentFilename, string $orderNumber): string
    {
        $extension = pathinfo($currentFilename, PATHINFO_EXTENSION);
        $newFilename = $this->buildPurchaseOrderFilename($orderNumber, null, $extension);
        $disk = Storage::disk('local');
        $currentPath = self::FILE_DIRECTORY . '/' . $currentFilename;
        $newPath = self::FILE_DIRECTORY . '/' . $newFilename;

        if ($disk->exists($currentPath)) {
            if ($currentFilename !== $newFilename) {
                $disk->move($currentPath, $newPath);
            }

            return $newFilename;
        }

        return $currentFilename;
    }

    private function buildPurchaseOrderFilename(string $orderNumber, ?UploadedFile $uploadedFile = null, ?string $extension = null): string
    {
        if ($extension === null) {
            $extension = strtolower($uploadedFile?->getClientOriginalExtension()
                ?: $uploadedFile?->guessExtension()
                ?: 'bin');
        }

        $safeOrderNumber = preg_replace('/[^A-Za-z0-9._-]/', '_', trim($orderNumber));

        return $safeOrderNumber . '.' . strtolower($extension);
    }

    private function deletePurchaseOrderFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = self::FILE_DIRECTORY . '/' . $filename;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}
