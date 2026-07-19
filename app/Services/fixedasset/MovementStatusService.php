<?php

namespace App\Services\fixedasset;

use App\Models\fixedasset\Assignment;
use App\Models\fixedasset\MovementStatus;
use App\Models\fixedasset\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class MovementStatusService
{
    public function approve(Assignment|Transfer $movement): Model
    {
        return $this->transition(
            $movement,
            MovementStatus::PENDING_APPROVAL,
            MovementStatus::APPROVED,
            'Solo se pueden aprobar movimientos pendientes de aprobación.'
        );
    }

    public function reject(Assignment|Transfer $movement): Model
    {
        return $this->transition(
            $movement,
            MovementStatus::PENDING_APPROVAL,
            MovementStatus::REJECTED,
            'Solo se pueden rechazar movimientos pendientes de aprobación.'
        );
    }

    public function annul(Assignment $assignment, string $reason): Assignment
    {
        if ((int) $assignment->status_id !== MovementStatus::APPROVED) {
            throw ValidationException::withMessages([
                'status_id' => 'Solo se pueden anular asignaciones aprobadas.',
            ]);
        }

        $assignment->annulment_reason = $reason;
        $assignment->status_id = MovementStatus::ANNULLED;
        $assignment->save();

        return $assignment->fresh();
    }

    private function transition(
        Assignment|Transfer $movement,
        int $fromStatus,
        int $toStatus,
        string $errorMessage
    ): Model {
        if ((int) $movement->status_id !== $fromStatus) {
            throw ValidationException::withMessages([
                'status_id' => $errorMessage,
            ]);
        }

        $movement->status_id = $toStatus;
        $movement->save();

        return $movement->fresh();
    }
}
