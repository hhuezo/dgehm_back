<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function liquidationReport(Request $request)
    {
        // Implementation for liquidation report

        return response()->json(['message' => 'Liquidation report generated']);
    }


    public function stockReport(Request $request)
    {
        // Implementation for stock report

        return response()->json(['message' => 'Stock report generated']);
    }
}
