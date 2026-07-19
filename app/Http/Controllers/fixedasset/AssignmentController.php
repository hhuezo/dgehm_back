<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\fixedasset\Assignment;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\MovementStatus;
use App\Services\fixedasset\AssignmentExecutionService;
use App\Services\fixedasset\FixedAssetAttachmentService;
use App\Services\fixedasset\MovementStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    public const PERMISSION_MANAGE = 'administrar asignacion';

    public function index(Request $request): JsonResponse
    {
        $query = Assignment::query()
            ->select(
                'id',
                'date',
                'organizational_unit_id',
                'person_id',
                'observation',
                'reception_act_file',
                'annulment_reason',
                'status_id',
                'created_at',
                'updated_at'
            )
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'person:id,name,lastname',
                'status:id,name',
            ])
            ->withCount('details');

        $this->scopeVisibleAssignments($query, $this->authUser($request));

        $assignments = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    public function assignablePersons(Request $request): JsonResponse
    {
        if (!$this->canManageAssignments($this->authUser($request))) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

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

    public function show(Request $request, string $id): JsonResponse
    {
        $assignment = Assignment::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'person:id,name,lastname,email',
                'details' => fn ($query) => $query
                    ->select('id', 'fa_assignment_id', 'fa_fixed_asset_id', 'observation')
                    ->with([
                        'fixedAsset:id,code,correlative,description,brand,model,serial_number,fa_category_id',
                        'fixedAsset.category:id,name,code',
                    ]),
            ])
            ->withCount('details')
            ->findOrFail($id);

        $this->assertCanViewAssignment($this->authUser($request), $assignment);

        return response()->json([
            'success' => true,
            'data' => $assignment,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->assertCanManageAssignments($this->authUser($request));
        $this->normalizeMultipartPayload($request);
        [$validated, $details] = $this->prepareAssignmentData($request);

        $assignment = DB::transaction(function () use ($validated, $details) {
            $validated['status_id'] = MovementStatus::PENDING_APPROVAL;
            $assignment = Assignment::create($validated);
            $this->syncDetails($assignment, $details);

            return $assignment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Asignación creada correctamente',
            'data' => $this->loadAssignmentResponse($assignment),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->assertCanManageAssignments($this->authUser($request));
        $assignment = Assignment::findOrFail($id);

        if ((int) $assignment->status_id !== MovementStatus::PENDING_APPROVAL) {
            throw ValidationException::withMessages([
                'status_id' => 'Solo se puede modificar una asignación pendiente de aprobación.',
            ]);
        }

        $this->normalizeMultipartPayload($request);
        [$validated, $details] = $this->prepareAssignmentData($request);

        DB::transaction(function () use ($assignment, $validated, $details) {
            $assignment->update($validated);
            $this->syncDetails($assignment, $details);
        });

        return response()->json([
            'success' => true,
            'message' => 'Asignación actualizada correctamente',
            'data' => $this->loadAssignmentResponse($assignment->fresh()),
        ]);
    }

    public function downloadReceptionActFile(Request $request, string $id, FixedAssetAttachmentService $attachments)
    {
        $assignment = Assignment::find($id);

        if (!$assignment || !$assignment->reception_act_file) {
            return response()->json([
                'success' => false,
                'message' => 'Acta de recepción no encontrada.',
            ], 404);
        }

        $this->assertCanViewAssignment($this->authUser($request), $assignment);

        return $attachments->download(
            FixedAssetAttachmentService::DIRECTORY_ASSIGNMENT_RECEPTION_ACTS,
            $assignment->reception_act_file
        );
    }

    public function approve(Request $request, string $id, MovementStatusService $statusService): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $this->assertIsAssignedPerson($this->authUser($request), $assignment);
        $assignment = $statusService->approve($assignment);

        return response()->json([
            'success' => true,
            'message' => 'Asignación aprobada correctamente',
            'data' => $this->loadAssignmentResponse($assignment),
        ]);
    }

    public function reject(Request $request, string $id, MovementStatusService $statusService): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $this->assertIsAssignedPerson($this->authUser($request), $assignment);
        $assignment = $statusService->reject($assignment);

        return response()->json([
            'success' => true,
            'message' => 'Asignación rechazada correctamente',
            'data' => $this->loadAssignmentResponse($assignment),
        ]);
    }

    public function annul(Request $request, string $id, MovementStatusService $statusService): JsonResponse
    {
        $this->assertCanManageAssignments($this->authUser($request));

        $validated = $request->validate([
            'annulment_reason' => 'required|string|min:5|max:2000',
        ], [
            'annulment_reason.required' => 'El motivo de anulación es obligatorio.',
            'annulment_reason.min' => 'El motivo de anulación debe tener al menos 5 caracteres.',
            'annulment_reason.max' => 'El motivo de anulación no puede superar 2000 caracteres.',
        ]);

        $assignment = Assignment::findOrFail($id);
        $assignment = $statusService->annul($assignment, trim($validated['annulment_reason']));

        return response()->json([
            'success' => true,
            'message' => 'Asignación anulada correctamente',
            'data' => $this->loadAssignmentResponse($assignment),
        ]);
    }

    public function execute(
        Request $request,
        string $id,
        AssignmentExecutionService $executionService
    ): JsonResponse {
        $this->assertCanManageAssignments($this->authUser($request));
        $assignment = Assignment::findOrFail($id);
        $assignment = $executionService->execute($assignment, $request);

        return response()->json([
            'success' => true,
            'message' => 'Asignación ejecutada correctamente',
            'data' => $this->loadAssignmentResponse($assignment),
        ]);
    }

    public function report(Request $request, string $id): Response
    {
        $assignment = Assignment::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'person:id,name,lastname,fa_organizational_unit_id',
                'person.organizationalUnit:id,name,abbreviation',
                'details' => fn ($query) => $query
                    ->select('id', 'fa_assignment_id', 'fa_fixed_asset_id', 'observation')
                    ->with([
                        'fixedAsset:id,code,correlative,description,brand,model,fa_category_id',
                        'fixedAsset.category:id,name,code',
                    ]),
            ])
            ->findOrFail($id);

        $this->assertCanViewAssignment($this->authUser($request), $assignment);

        $person = $assignment->person;
        $unitName = $person?->organizationalUnit?->name
            ?? $assignment->organizationalUnit?->name
            ?? '';

        if ($unitName === '' && $person) {
            $resolvedUnitId = $person->resolveFaOrganizationalUnitId();
            if ($resolvedUnitId) {
                $unitName = \App\Models\fixedasset\OrganizationalUnit::query()
                    ->whereKey($resolvedUnitId)
                    ->value('name') ?? '';
            }
        }

        $pdf = Pdf::loadView('reports.assignment', [
            'assignment' => $assignment,
            'person' => $person,
            'unitName' => $unitName,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("Ficha_Asignacion_Activo_Fijo_{$id}.pdf");
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

    private function canManageAssignments($user): bool
    {
        return $user && $user->can(self::PERMISSION_MANAGE);
    }

    private function currentEmployeeId($user): ?int
    {
        if (!$user) {
            return null;
        }

        $employeeId = Employee::query()->where('user_id', $user->id)->value('id');

        return $employeeId ? (int) $employeeId : null;
    }

    private function scopeVisibleAssignments($query, $user): void
    {
        if ($this->canManageAssignments($user)) {
            return;
        }

        $employeeId = $this->currentEmployeeId($user);
        if (!$employeeId) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('person_id', $employeeId);
    }

    private function assertCanManageAssignments($user): void
    {
        if (!$this->canManageAssignments($user)) {
            abort(403, 'No tienes permiso para administrar asignaciones.');
        }
    }

    private function assertCanViewAssignment($user, Assignment $assignment): void
    {
        if ($this->canManageAssignments($user)) {
            return;
        }

        $employeeId = $this->currentEmployeeId($user);
        if ($employeeId && (int) $assignment->person_id === $employeeId) {
            return;
        }

        abort(403, 'No tienes permiso para ver esta asignación.');
    }

    private function assertIsAssignedPerson($user, Assignment $assignment): void
    {
        $employeeId = $this->currentEmployeeId($user);
        if (!$employeeId || (int) $assignment->person_id !== $employeeId) {
            abort(403, 'Solo la persona asignada puede aprobar o rechazar esta asignación.');
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

    private function prepareAssignmentData(Request $request): array
    {
        $validated = $this->validateAssignment($request);
        $details = $validated['details'];
        unset($validated['details']);

        $employee = Employee::query()
            ->with(['fixedAssetCategories:id', 'user'])
            ->findOrFail($validated['person_id']);

        if (!$employee->user || !$employee->user->hasRole('activo-fijo-encargado-categoria')) {
            throw ValidationException::withMessages([
                'person_id' => 'La persona debe tener el rol activo-fijo-encargado-categoria.',
            ]);
        }

        $organizationalUnitId = $employee->resolveFaOrganizationalUnitId();

        if (!$organizationalUnitId) {
            throw ValidationException::withMessages([
                'person_id' => 'No se pudo determinar la unidad organizativa de la persona seleccionada.',
            ]);
        }

        $this->assertPersonOwnsAssetCategories($employee, $details);

        $validated['organizational_unit_id'] = $organizationalUnitId;

        return [$validated, $details];
    }

    private function validateAssignment(Request $request): array
    {
        return $request->validate([
            'date' => 'required|date',
            'person_id' => [
                'required',
                'integer',
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
        ]);
    }

    /**
     * La persona debe ser encargada de la categoría de cada activo a asignar.
     */
    private function assertPersonOwnsAssetCategories(Employee $employee, array $details): void
    {
        $allowedCategoryIds = $employee->fixedAssetCategories
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($allowedCategoryIds === []) {
            throw ValidationException::withMessages([
                'person_id' => 'La persona seleccionada no es encargada de ninguna categoría de activo fijo.',
            ]);
        }

        $assetIds = collect($details)
            ->pluck('fa_fixed_asset_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $assets = FixedAsset::query()
            ->whereIn('id', $assetIds)
            ->get(['id', 'fa_category_id', 'code', 'correlative']);

        foreach ($assets as $asset) {
            if (!in_array((int) $asset->fa_category_id, $allowedCategoryIds, true)) {
                $label = trim(($asset->code ?? '') . '-' . ($asset->correlative ?? ''), '-');
                throw ValidationException::withMessages([
                    'person_id' => $label !== ''
                        ? "La persona no es encargada de la categoría del activo {$label}."
                        : 'La persona no es encargada de la categoría de uno o más activos.',
                ]);
            }
        }
    }

    private function syncDetails(Assignment $assignment, array $details): void
    {
        $assignment->details()->delete();

        foreach ($details as $detail) {
            $assignment->details()->create([
                'fa_fixed_asset_id' => $detail['fa_fixed_asset_id'],
                'observation' => $detail['observation'] ?? null,
            ]);
        }
    }

    private function loadAssignmentResponse(Assignment $assignment): Assignment
    {
        return $assignment->load([
            'organizationalUnit:id,name,abbreviation',
            'person:id,name,lastname,email',
            'status:id,name',
            'details.fixedAsset:id,code,correlative,description,brand,model,serial_number,fa_category_id',
            'details.fixedAsset.category:id,name,code',
        ])->loadCount('details');
    }
}
