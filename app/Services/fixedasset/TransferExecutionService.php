<?php

namespace App\Services\fixedasset;

use App\Models\Employee;
use App\Models\fixedasset\FixedAsset;
use App\Models\fixedasset\MovementStatus;
use App\Models\fixedasset\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferExecutionService
{
    public function __construct(
        private readonly AssetCustodyService $custodyService,
        private readonly FixedAssetAttachmentService $attachments
    ) {}

    public function execute(Transfer $transfer, Request $request): Transfer
    {
        if ((int) $transfer->status_id !== MovementStatus::APPROVED) {
            throw ValidationException::withMessages([
                'status_id' => 'Solo se pueden ejecutar traslados aprobados.',
            ]);
        }

        $request->validate([
            'file' => FixedAssetAttachmentService::REQUIRED_FILE_RULE,
        ], [
            'file.required' => 'El acta de traslado es obligatoria para ejecutar el traslado.',
            'file.file' => 'El acta de traslado debe ser un archivo válido.',
            'file.mimes' => 'El acta de traslado debe ser PDF o imagen.',
            'file.max' => 'El acta de traslado no puede superar los 10 MB.',
        ]);

        $transfer->load([
            'details:id,fa_transfer_id,fa_fixed_asset_id',
            'details.fixedAsset:id,current_responsible,organizational_unit_id',
            'personReceives:id,name,lastname,email',
        ]);

        if ($transfer->details->isEmpty()) {
            throw ValidationException::withMessages([
                'details' => 'El traslado no tiene activos para ejecutar.',
            ]);
        }

        $delivererId = (int) $transfer->person_delivers_id;

        foreach ($transfer->details as $detail) {
            $assetId = (int) $detail->fa_fixed_asset_id;
            if (!$this->custodyService->personOwnsAsset($delivererId, $assetId)) {
                throw ValidationException::withMessages([
                    'details' => 'Uno o más activos ya no están asignados a la persona que entrega.',
                ]);
            }
        }

        $receiverName = $this->formatEmployeeName($transfer->personReceives);
        $organizationalUnitId = (int) $transfer->organizational_unit_id;

        DB::transaction(function () use ($transfer, $receiverName, $organizationalUnitId, $request) {
            foreach ($transfer->details as $detail) {
                /** @var FixedAsset $asset */
                $asset = $detail->fixedAsset;
                $asset->current_responsible = $receiverName;
                $asset->organizational_unit_id = $organizationalUnitId;
                $asset->save();
            }

            $transfer->file = $this->attachments->store(
                $request->file('file'),
                FixedAssetAttachmentService::DIRECTORY_TRANSFERS,
                $transfer->id . '_acta',
                $transfer->file
            );
            $transfer->status_id = MovementStatus::FINALIZED;
            $transfer->save();
        });

        return $transfer->fresh();
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
