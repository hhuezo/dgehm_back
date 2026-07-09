<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Services\fixedasset\MovementService;
use Illuminate\Http\JsonResponse;

class MovementController extends Controller
{
    public function indexForAsset(string $id, MovementService $movementService): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $movementService->listMovementsForAsset((int) $id),
        ]);
    }
}
