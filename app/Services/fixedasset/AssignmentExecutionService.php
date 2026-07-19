<?php

namespace App\Services\fixedasset;

use App\Models\Employee;
use App\Models\fixedasset\Assignment;
use App\Models\fixedasset\DepreciationStatus;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\MovementStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentExecutionService
{
    public function __construct(
        private readonly FixedAssetAttachmentService $attachments
    ) {}

    public function execute(Assignment $assignment, Request $request): Assignment
    {
        if ((int) $assignment->status_id !== MovementStatus::APPROVED) {
            throw ValidationException::withMessages([
                'status_id' => 'Solo se pueden ejecutar asignaciones aprobadas.',
            ]);
        }

        $request->validate([
            'reception_act_file' => FixedAssetAttachmentService::REQUIRED_FILE_RULE,
        ], [
            'reception_act_file.required' => 'El acta de recepción es obligatoria para ejecutar la asignación.',
            'reception_act_file.file' => 'El acta de recepción debe ser un archivo válido.',
            'reception_act_file.mimes' => 'El acta de recepción debe ser PDF o imagen.',
            'reception_act_file.max' => 'El acta de recepción no puede superar los 10 MB.',
        ]);

        $assignment->load([
            'details:id,fa_assignment_id,fa_fixed_asset_id',
            'details.fixedAsset:id,current_responsible,organizational_unit_id,depreciation_status_id',
            'person:id,name,lastname,email',
        ]);

        if ($assignment->details->isEmpty()) {
            throw ValidationException::withMessages([
                'details' => 'La asignación no tiene activos para ejecutar.',
            ]);
        }

        $personName = $this->formatEmployeeName($assignment->person);
        $organizationalUnitId = (int) $assignment->organizational_unit_id;

        DB::transaction(function () use ($assignment, $personName, $organizationalUnitId, $request) {
            foreach ($assignment->details as $detail) {
                /** @var FixedAsset $asset */
                $asset = $detail->fixedAsset;
                if (!$asset) {
                    continue;
                }

                $asset->current_responsible = $personName;
                $asset->organizational_unit_id = $organizationalUnitId;
                $asset->depreciation_status_id = DepreciationStatus::ACTIVE;
                $asset->save();
            }

            $assignment->reception_act_file = $this->attachments->store(
                $request->file('reception_act_file'),
                FixedAssetAttachmentService::DIRECTORY_ASSIGNMENT_RECEPTION_ACTS,
                $assignment->id . '_acta',
                $assignment->reception_act_file
            );
            $assignment->status_id = MovementStatus::FINALIZED;
            $assignment->save();
        });

        return $assignment->fresh();
    }

    private function formatEmployeeName(?Employee $employee): string
    {
        if (!$employee) {
            return '';
        }

        $fullName = trim(($employee->name ?? '') . ' ' . ($employee->lastname ?? ''));

        return $fullName !== '' ? $fullName : ($employee->email ?? "ID {$employee->id}");
    }
}
