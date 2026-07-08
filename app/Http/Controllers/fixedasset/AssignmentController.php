<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\fixedasset\Assignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    public function index(): JsonResponse
    {
        $assignments = Assignment::query()
            ->select(
                'id',
                'date',
                'organizational_unit_id',
                'person_id',
                'observation',
                'created_at',
                'updated_at'
            )
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'person:id,name,lastname',
            ])
            ->withCount('details')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
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

    public function show(string $id): JsonResponse
    {
        $assignment = Assignment::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'person:id,name,lastname,email',
                'details' => fn ($query) => $query
                    ->select('id', 'fa_assignment_id', 'fa_fixed_asset_id', 'observation')
                    ->with('fixedAsset:id,code,correlative,description'),
            ])
            ->withCount('details')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $assignment,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        [$validated, $details] = $this->prepareAssignmentData($request);

        $assignment = DB::transaction(function () use ($validated, $details) {
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
        $assignment = Assignment::findOrFail($id);
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

    public function report(string $id): Response
    {
        $assignment = Assignment::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
                'person:id,name,lastname',
                'details' => fn ($query) => $query
                    ->select('id', 'fa_assignment_id', 'fa_fixed_asset_id', 'observation')
                    ->with('fixedAsset:id,code,correlative,description,brand,model'),
            ])
            ->findOrFail($id);

        $pdf = Pdf::loadView('reports.assignment', [
            'assignment' => $assignment,
            'person' => $assignment->person,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("Ficha_Asignacion_Activo_Fijo_{$id}.pdf");
    }

    private function prepareAssignmentData(Request $request): array
    {
        $validated = $this->validateAssignment($request);
        $details = $validated['details'];
        unset($validated['details']);

        $employee = Employee::query()->findOrFail($validated['person_id']);
        $organizationalUnitId = $employee->resolveFaOrganizationalUnitId();

        if (!$organizationalUnitId) {
            throw ValidationException::withMessages([
                'person_id' => 'No se pudo determinar la unidad organizativa de la persona seleccionada.',
            ]);
        }

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
            'details.fixedAsset:id,code,correlative,description',
        ])->loadCount('details');
    }
}
