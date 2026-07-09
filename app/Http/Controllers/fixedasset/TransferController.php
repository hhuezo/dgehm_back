<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\fixedasset\Transfer;
use App\Services\fixedasset\AssetCustodyService;
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
                'status',
                'created_at',
                'updated_at'
            )
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'personDelivers:id,name,lastname',
                'personReceives:id,name,lastname',
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
            ->whereHas('fixedAssetCategories')
            ->where('active', true)
            ->with(['fixedAssetCategories:id'])
            ->orderBy('name')
            ->orderBy('lastname')
            ->get(['id', 'name', 'lastname', 'email'])
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

    public function store(Request $request): JsonResponse
    {
        [$validated, $details] = $this->prepareTransferData($request);

        $transfer = DB::transaction(function () use ($validated, $details) {
            $validated['status'] = Transfer::STATUS_ENTERED;
            $transfer = Transfer::create($validated);
            $this->syncDetails($transfer, $details);

            return $transfer;
        });

        return response()->json([
            'success' => true,
            'message' => 'Traslado creado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);

        if ((int) $transfer->status !== Transfer::STATUS_ENTERED) {
            throw ValidationException::withMessages([
                'status' => 'No se puede modificar un traslado finalizado.',
            ]);
        }

        [$validated, $details] = $this->prepareTransferData($request);

        DB::transaction(function () use ($transfer, $validated, $details) {
            $transfer->update($validated);
            $this->syncDetails($transfer, $details);
        });

        return response()->json([
            'success' => true,
            'message' => 'Traslado actualizado correctamente',
            'data' => $this->loadTransferResponse($transfer->fresh()),
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
            'details.fixedAsset:id,code,correlative,description,fa_category_id',
            'details.fixedAsset.category:id,name,code',
        ])->loadCount('details');
    }
}
