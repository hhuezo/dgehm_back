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
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    public function index(): JsonResponse
    {
        $assignments = Assignment::query()
            ->select(
                'id',
                'is_assignment',
                'is_unassignment',
                'date',
                'is_permanent',
                'temporal_start_date',
                'temporal_end_date',
                'organizational_unit_id',
                'person_id',
                'collaborator_id',
                'observation',
                'created_at',
                'updated_at'
            )
            ->with([
                'organizationalUnit:id,name,abbreviation',
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

    public function show(string $id): JsonResponse
    {
        $assignment = Assignment::query()
            ->with([
                'organizationalUnit:id,name,abbreviation',
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
        $validated = $this->validateAssignment($request);
        $details = $validated['details'];
        unset($validated['details']);

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
        $validated = $this->validateAssignment($request);
        $details = $validated['details'];
        unset($validated['details']);

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
                'details' => fn ($query) => $query
                    ->select('id', 'fa_assignment_id', 'fa_fixed_asset_id', 'observation')
                    ->with('fixedAsset:id,code,correlative,description,brand,model'),
            ])
            ->findOrFail($id);

        $person = $assignment->person_id
            ? Employee::query()->select('id', 'name', 'lastname')->find($assignment->person_id)
            : null;

        $collaborator = $assignment->collaborator_id
            ? Employee::query()->select('id', 'name', 'lastname')->find($assignment->collaborator_id)
            : null;

        $pdf = Pdf::loadView('reports.assignment', [
            'assignment' => $assignment,
            'person' => $person,
            'collaborator' => $collaborator,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("Ficha_Asignacion_Activo_Fijo_{$id}.pdf");
    }

    private function validateAssignment(Request $request): array
    {
        $validated = $request->validate([
            'is_assignment' => 'required|boolean',
            'is_unassignment' => 'required|boolean',
            'date' => 'required|date',
            'is_permanent' => 'required|boolean',
            'temporal_start_date' => 'nullable|date|required_if:is_permanent,false',
            'temporal_end_date' => 'nullable|date|required_if:is_permanent,false|after_or_equal:temporal_start_date',
            'organizational_unit_id' => 'required|exists:fa_organizational_units,id',
            'person_id' => 'nullable|integer',
            'collaborator_id' => 'nullable|integer',
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

        if ($validated['is_permanent']) {
            $validated['temporal_start_date'] = null;
            $validated['temporal_end_date'] = null;
        }

        return $validated;
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
            'details.fixedAsset:id,code,correlative,description',
        ])->loadCount('details');
    }
}
