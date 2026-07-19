<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\fixedasset\OrganizationalUnit;
use App\Models\fixedasset\Transfer;
use App\Models\fixedasset\MovementStatus;
use App\Services\fixedasset\AssetCustodyService;
use App\Services\fixedasset\FixedAssetAttachmentService;
use App\Services\fixedasset\MovementStatusService;
use App\Services\fixedasset\TransferExecutionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class TransferController extends Controller
{
    public const PERMISSION_MANAGE = 'administrar traslado';

    public function index(Request $request): JsonResponse
    {
        $query = Transfer::query()
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
            ->withCount('details');

        $this->scopeVisibleTransfers($query, $this->authUser($request));

        $transfers = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transfers,
        ]);
    }

    public function assignablePersons(Request $request): JsonResponse
    {
        $this->authUser($request);

        $employees = Employee::query()
            ->where('active', true)
            ->orderBy('name')
            ->orderBy('lastname')
            ->get(['id', 'name', 'lastname', 'email', 'fa_organizational_unit_id'])
            ->map(function (Employee $employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'lastname' => $employee->lastname,
                    'email' => $employee->email,
                    'fa_organizational_unit_id' => $employee->resolveFaOrganizationalUnitId(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    public function assignedAssets(Request $request, string $personId, AssetCustodyService $custodyService): JsonResponse
    {
        $user = $this->authUser($request);
        $requestedPersonId = (int) $personId;

        if (
            !$this->canManageTransfers($user)
            && $this->currentEmployeeId($user) !== $requestedPersonId
        ) {
            abort(403, 'Solo puedes consultar los activos de tu custodia.');
        }

        $excludeTransferId = $request->integer('exclude_transfer_id') ?: null;
        $assets = $custodyService->getAssetsForPerson($requestedPersonId, $excludeTransferId);

        return response()->json([
            'success' => true,
            'data' => $assets,
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
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

        $this->assertCanViewTransfer($this->authUser($request), $transfer);

        return response()->json([
            'success' => true,
            'data' => $transfer,
        ]);
    }

    public function store(Request $request, FixedAssetAttachmentService $attachments): JsonResponse
    {
        $user = $this->authUser($request);
        $this->assertCanCreateTransfer($user);
        $this->normalizeMultipartPayload($request);
        [$validated, $details] = $this->prepareTransferData($request, $user);
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
        $user = $this->authUser($request);
        $transfer = Transfer::findOrFail($id);
        $this->assertCanEditTransfer($user, $transfer);

        if ((int) $transfer->status_id !== MovementStatus::PENDING_APPROVAL) {
            throw ValidationException::withMessages([
                'status_id' => 'Solo se puede modificar un traslado pendiente de aprobación.',
            ]);
        }

        $this->normalizeMultipartPayload($request);
        [$validated, $details] = $this->prepareTransferData($request, $user, $transfer);
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

    public function downloadFile(Request $request, string $id, FixedAssetAttachmentService $attachments)
    {
        $transfer = Transfer::find($id);

        if (!$transfer || !$transfer->file) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        $this->assertCanViewTransfer($this->authUser($request), $transfer);

        return $attachments->download(
            FixedAssetAttachmentService::DIRECTORY_TRANSFERS,
            $transfer->file
        );
    }

    public function approve(Request $request, string $id, MovementStatusService $statusService): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);
        $this->assertIsReceiver($this->authUser($request), $transfer);
        $transfer = $statusService->approve($transfer);

        return response()->json([
            'success' => true,
            'message' => 'Traslado aprobado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ]);
    }

    public function reject(Request $request, string $id, MovementStatusService $statusService): JsonResponse
    {
        $transfer = Transfer::findOrFail($id);
        $this->assertIsReceiver($this->authUser($request), $transfer);
        $transfer = $statusService->reject($transfer);

        return response()->json([
            'success' => true,
            'message' => 'Traslado rechazado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ]);
    }

    public function execute(Request $request, string $id, TransferExecutionService $executionService): JsonResponse
    {
        $this->assertCanManageTransfers($this->authUser($request));
        $transfer = Transfer::findOrFail($id);
        $transfer = $executionService->execute($transfer, $request);

        return response()->json([
            'success' => true,
            'message' => 'Traslado ejecutado correctamente',
            'data' => $this->loadTransferResponse($transfer),
        ]);
    }

    public function report(Request $request, string $id): Response
    {
        $transfer = Transfer::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'personDelivers:id,name,lastname,fa_organizational_unit_id',
                'personReceives:id,name,lastname,fa_organizational_unit_id',
                'personReceives.organizationalUnit:id,name,abbreviation',
                'details' => fn ($query) => $query
                    ->select('id', 'fa_transfer_id', 'fa_fixed_asset_id', 'observation')
                    ->with([
                        'fixedAsset:id,code,correlative,description,brand,model,fa_category_id',
                        'fixedAsset.category:id,name,code',
                    ]),
            ])
            ->findOrFail($id);

        $this->assertCanViewTransfer($this->authUser($request), $transfer);

        $receiver = $transfer->personReceives;
        $deliverer = $transfer->personDelivers;
        $unitName = $receiver?->organizationalUnit?->name
            ?? $transfer->organizationalUnit?->name
            ?? '';

        if ($unitName === '' && $receiver) {
            $resolvedUnitId = $receiver->resolveFaOrganizationalUnitId();
            if ($resolvedUnitId) {
                $unitName = OrganizationalUnit::query()
                    ->whereKey($resolvedUnitId)
                    ->value('name') ?? '';
            }
        }

        $pdf = Pdf::loadView('reports.transfer', [
            'transfer' => $transfer,
            'receiver' => $receiver,
            'deliverer' => $deliverer,
            'unitName' => $unitName,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("Ficha_Traslado_Activo_Fijo_{$id}.pdf");
    }

    private function authUser(Request $request)
    {
        $user = $request->user() ?? $request->user('sanctum');

        if (!$user && $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());
            $user = $accessToken?->tokenable;
        }

        if (!$user) {
            abort(401, 'Token no proporcionado o inválido.');
        }

        return $user;
    }

    private function canManageTransfers($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        return $user->can(self::PERMISSION_MANAGE);
    }

    private function currentEmployeeId($user): ?int
    {
        if (!$user) {
            return null;
        }

        $employeeId = Employee::query()->where('user_id', $user->id)->value('id');

        return $employeeId ? (int) $employeeId : null;
    }

    private function scopeVisibleTransfers($query, $user): void
    {
        if ($this->canManageTransfers($user)) {
            return;
        }

        $employeeId = $this->currentEmployeeId($user);
        if (!$employeeId) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($builder) use ($employeeId) {
            $builder
                ->where('person_delivers_id', $employeeId)
                ->orWhere('person_receives_id', $employeeId);
        });
    }

    private function assertCanManageTransfers($user): void
    {
        if (!$this->canManageTransfers($user)) {
            abort(403, 'No tienes permiso para administrar traslados.');
        }
    }

    private function assertCanCreateTransfer($user): void
    {
        if ($this->canManageTransfers($user)) {
            return;
        }

        if ($user && $user->can('transfers create')) {
            return;
        }

        abort(403, 'No tienes permiso para crear traslados.');
    }

    private function assertCanViewTransfer($user, Transfer $transfer): void
    {
        if ($this->canManageTransfers($user)) {
            return;
        }

        $employeeId = $this->currentEmployeeId($user);
        if (
            $employeeId
            && (
                (int) $transfer->person_delivers_id === $employeeId
                || (int) $transfer->person_receives_id === $employeeId
            )
        ) {
            return;
        }

        abort(403, 'No tienes permiso para ver este traslado.');
    }

    private function assertCanEditTransfer($user, Transfer $transfer): void
    {
        if ($this->canManageTransfers($user)) {
            return;
        }

        $employeeId = $this->currentEmployeeId($user);
        if (
            $employeeId
            && (int) $transfer->person_delivers_id === $employeeId
            && $user->can('transfers update')
        ) {
            return;
        }

        abort(403, 'No tienes permiso para editar este traslado.');
    }

    private function assertIsReceiver($user, Transfer $transfer): void
    {
        $employeeId = $this->currentEmployeeId($user);
        if (!$employeeId || (int) $transfer->person_receives_id !== $employeeId) {
            abort(403, 'Solo la persona que recibe puede aprobar o rechazar este traslado.');
        }
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

    private function prepareTransferData(Request $request, $user, ?Transfer $existing = null): array
    {
        $validated = $this->validateTransfer($request);
        $details = $validated['details'];
        unset($validated['details']);

        if (!$this->canManageTransfers($user)) {
            $employeeId = $this->currentEmployeeId($user);
            if (!$employeeId) {
                throw ValidationException::withMessages([
                    'person_delivers_id' => 'No se encontró la persona asociada a tu usuario.',
                ]);
            }

            $validated['person_delivers_id'] = $employeeId;
        }

        if ((int) $validated['person_delivers_id'] === (int) $validated['person_receives_id']) {
            throw ValidationException::withMessages([
                'person_receives_id' => 'La persona que recibe debe ser diferente a la que entrega.',
            ]);
        }

        $excludeTransferId = $existing?->id ?? ($request->route('id') ? (int) $request->route('id') : null);
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
