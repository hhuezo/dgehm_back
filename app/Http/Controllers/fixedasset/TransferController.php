<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\fixedasset\Transfer;
use App\Models\fixedasset\MovementStatus;
use App\Services\fixedasset\AssetCustodyService;
use App\Services\fixedasset\FixedAssetAttachmentService;
use App\Services\fixedasset\MovementStatusService;
use App\Services\fixedasset\TransferExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    public function index(): JsonResponse
    {
        $transfers = Transfer::query()
            ->select(
                'id',
                'date',
                'organizational_unit_id',
                'person_delivers_id',
                'person_receives_id',
                'observation',
                'file',
                'status_id',
                'created_at',
                'updated_at'
            )
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'personDelivers:id,name,lastname',
                'personReceives:id,name,lastname',
                'status:id,name',
            ])
            ->withCount('details')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transfers,
        ]);
    }

    public function assignablePersons(): JsonResponse
    {
        $employees = Employee::query()
            ->where('active', true)
            ->whereHas('fixedAssetCategories')
            ->whereHas('user.roles', function ($query) {
                $query->where('name', 'activo-fijo-encargado-categoria');
            })
            ->with(['fixedAssetCategories:id'])
            ->orderBy('name')
            ->orderBy('lastname')
            ->get(['id', 'name', 'lastname', 'email', 'fa_organizational_unit_id'])
            ->map(function (Employee $employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'lastname' => $employee->lastname,
                    'email' => $employee->email,
                    'fixed_asset_categories' => $employee->fixedAssetCategories,
                    'fa_organizational_unit_id' => $employee->resolveFaOrganizationalUnitId(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    public function assignedAssets(string $personId, AssetCustodyService $custodyService): JsonResponse
    {
        $excludeTransferId = request()->integer('exclude_transfer_id') ?: null;

        $assets = $custodyService->getAssetsForPerson((int) $personId, $excludeTransferId);

        return response()->json([
            'success' => true,
            'data' => $assets,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $transfer = Transfer::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'personDelivers:id,name,lastname,email',
                'personReceives:id,name,lastname,email',
                'details' => fn ($query) => $query
                    ->select('id', 'fa_transfer_id', 'fa_fixed_asset_id', 'observation')
                    ->with([
                        'fixedAsset:id,code,correlative,description,fa_category_id',
                        'fixedAsset.category:id,name,code',
                    ]),
            ])
            ->withCount('details')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transfer,
        ]);
    }

    public function store(Request $request, FixedAssetAttachmentService $attachments): JsonResponse
    {
        $this->normalizeMultipartPayload($request);
        [$validated, $details] = $this->prepareTransferData($request);
        unset($validated['file']);

        $transfer = DB::transaction(function () use ($validated, $details, $request, $attachments) {
            $validated['status_id'] = MovementStatus::PENDING_APPROVAL;
            $transfer = Transfer::create($validated);
            $this->syncDetails($transfer, $details);

            if ($request->hasFile('file')) {
                $transfer->file = $attachments->store(
                    $request->file('file'),
                    FixedAssetAttachmentService::DIRECTORY_TRANSFERS,
                    $transfer->id
                );
                $transfer->save();
            }

            return $transfer;
        });

        return response()->json([
            'success' => true,
            'message' => 'Traslado creado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ], 201);
    }

    public function update(Request $request, string $id, FixedAssetAttachmentService $attachments): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);

        if ((int) $transfer->status_id !== MovementStatus::PENDING_APPROVAL) {
            throw ValidationException::withMessages([
                'status_id' => 'Solo se puede modificar un traslado pendiente de aprobación.',
            ]);
        }

        $this->normalizeMultipartPayload($request);
        [$validated, $details] = $this->prepareTransferData($request);
        unset($validated['file']);

        DB::transaction(function () use ($transfer, $validated, $details, $request, $attachments) {
            $transfer->update($validated);
            $this->syncDetails($transfer, $details);

            if ($request->hasFile('file')) {
                $transfer->file = $attachments->store(
                    $request->file('file'),
                    FixedAssetAttachmentService::DIRECTORY_TRANSFERS,
                    $transfer->id,
                    $transfer->file
                );
                $transfer->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Traslado actualizado correctamente',
            'data' => $this->loadTransferResponse($transfer->fresh()),
        ]);
    }

    public function downloadFile(string $id, FixedAssetAttachmentService $attachments)
    {
        $transfer = Transfer::find($id);

        if (!$transfer || !$transfer->file) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        return $attachments->download(
            FixedAssetAttachmentService::DIRECTORY_TRANSFERS,
            $transfer->file
        );
    }

    public function approve(string $id, MovementStatusService $statusService): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);
        $transfer = $statusService->approve($transfer);

        return response()->json([
            'success' => true,
            'message' => 'Traslado aprobado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ]);
    }

    public function reject(string $id, MovementStatusService $statusService): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);
        $transfer = $statusService->reject($transfer);

        return response()->json([
            'success' => true,
            'message' => 'Traslado rechazado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ]);
    }

    public function execute(string $id, TransferExecutionService $executionService): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);
        $transfer = $executionService->execute($transfer);

        return response()->json([
            'success' => true,
            'message' => 'Traslado ejecutado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ]);
    }

    private function normalizeMultipartPayload(Request $request): void
    {
        $details = $request->input('details');
        if (is_string($details)) {
            $decoded = json_decode($details, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge(['details' => $decoded]);
            }
        }
    }

    private function prepareTransferData(Request $request): array
    {
        $validated = $this->validateTransfer($request);
        $details = $validated['details'];
        unset($validated['details']);

        if ((int) $validated['person_delivers_id'] === (int) $validated['person_receives_id']) {
            throw ValidationException::withMessages([
                'person_receives_id' => 'La persona que recibe debe ser diferente a la que entrega.',
            ]);
        }

        $excludeTransferId = $request->route('id') ? (int) $request->route('id') : null;
        $custodyService = app(AssetCustodyService::class);

        foreach ($details as $detail) {
            $assetId = (int) $detail['fa_fixed_asset_id'];
            if (!$custodyService->personOwnsAsset((int) $validated['person_delivers_id'], $assetId, $excludeTransferId)) {
                throw ValidationException::withMessages([
                    'details' => 'Uno o más activos no están asignados a la persona que entrega.',
                ]);
            }
        }

        $receiver = Employee::query()->findOrFail($validated['person_receives_id']);
        $organizationalUnitId = $receiver->resolveFaOrganizationalUnitId();

        if (!$organizationalUnitId) {
            throw ValidationException::withMessages([
                'person_receives_id' => 'No se pudo determinar la unidad organizativa de la persona que recibe.',
            ]);
        }

        $validated['organizational_unit_id'] = $organizationalUnitId;

        return [$validated, $details];
    }

    private function validateTransfer(Request $request): array
    {
        return $request->validate([
            'date' => 'required|date',
            'person_delivers_id' => [
                'required',
                'integer',
                'different:person_receives_id',
                Rule::exists('adm_employees', 'id'),
                Rule::exists('fa_category_employee', 'adm_employee_id'),
            ],
            'person_receives_id' => [
                'required',
                'integer',
                'different:person_delivers_id',
                Rule::exists('adm_employees', 'id'),
                Rule::exists('fa_category_employee', 'adm_employee_id'),
            ],
            'observation' => 'nullable|string|max:2000',
            'file' => FixedAssetAttachmentService::FILE_RULE,
            'details' => 'required|array|min:1',
            'details.*.fa_fixed_asset_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('fa_fixed_assets', 'id'),
            ],
            'details.*.observation' => 'nullable|string|max:1000',
        ], [
            'person_delivers_id.different' => 'La persona que entrega debe ser diferente a la que recibe.',
            'person_receives_id.different' => 'La persona que recibe debe ser diferente a la que entrega.',
        ]);
    }

    private function syncDetails(Transfer $transfer, array $details): void
    {
        $transfer->details()->delete();

        foreach ($details as $detail) {
            $transfer->details()->create([
                'fa_fixed_asset_id' => $detail['fa_fixed_asset_id'],
                'observation' => $detail['observation'] ?? null,
            ]);
        }
    }

    private function loadTransferResponse(Transfer $transfer): Transfer
    {
        return $transfer->load([
            'organizationalUnit:id,name,abbreviation',
            'personDelivers:id,name,lastname,email',
            'personReceives:id,name,lastname,email',
            'status:id,name',
            'details.fixedAsset:id,code,correlative,description,fa_category_id',
            'details.fixedAsset.category:id,name,code',
        ])->loadCount('details');
    }
}
