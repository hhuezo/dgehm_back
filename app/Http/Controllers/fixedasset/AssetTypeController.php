<?php

namespace App\Http\Controllers\fixedasset;

use App\Http\Controllers\Controller;
use App\Models\fixedasset\AssetType;

class AssetTypeController extends Controller
{
    public function index()
    {
        $types = AssetType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
