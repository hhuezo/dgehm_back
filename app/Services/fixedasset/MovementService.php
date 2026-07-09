<?php

namespace App\Services\fixedasset;

use App\Models\Employee;
use App\Models\fixedasset\Assignment;
use App\Models\fixedasset\Transfer;
use Illuminate\Support\Collection;

class MovementService
{
    public function listMovementsForAsset(int $fixedAssetId): Collection
    {
        $movements = collect();

        $assignments = Assignment::query()
            ->select('fa_assignments.id', 'fa_assignments.date', 'fa_assignments.person_id')
            ->whereHas('details', fn ($query) => $query->where('fa_fixed_asset_id', $fixedAssetId))
            ->with('person:id,name,lastname,email')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        foreach ($assignments as $assignment) {
            $personName = $this->formatEmployeeName($assignment->person);
            $movements->push([
                'id' => (int) $assignment->id,
                'movement_type' => 'asignacion',
                'date' => $assignment->date?->format('Y-m-d'),
                'description' => "Se asignó a {$personName}",
            ]);
        }

        $transfers = Transfer::query()
            ->select(
                'fa_transfers.id',
                'fa_transfers.date',
                'fa_transfers.person_delivers_id',
                'fa_transfers.person_receives_id',
                'fa_transfers.status'
            )
            ->where('status', Transfer::STATUS_FINALIZED)
            ->whereHas('details', fn ($query) => $query->where('fa_fixed_asset_id', $fixedAssetId))
            ->with([
                'personDelivers:id,name,lastname,email',
                'personReceives:id,name,lastname,email',
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        foreach ($transfers as $transfer) {
            $delivererName = $this->formatEmployeeName($transfer->personDelivers);
            $receiverName = $this->formatEmployeeName($transfer->personReceives);
            $movements->push([
                'id' => (int) $transfer->id,
                'movement_type' => 'traslado',
                'date' => $transfer->date?->format('Y-m-d'),
                'description' => "Se reasignó de \"{$delivererName}\" a \"{$receiverName}\"",
            ]);
        }

        return $movements
            ->sort(function (array $a, array $b) {
                $dateCompare = strcmp($b['date'] ?? '', $a['date'] ?? '');
                if ($dateCompare !== 0) {
                    return $dateCompare;
                }

                return $b['id'] <=> $a['id'];
            })
            ->values();
    }

    private function formatEmployeeName(?Employee $employee): string
    {
        if (!$employee) {
            return '—';
        }

        $fullName = trim(($employee->name ?? '') . ' ' . ($employee->lastname ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        return $employee->email ?? "ID {$employee->id}";
    }
}
