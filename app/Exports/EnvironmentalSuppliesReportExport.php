<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EnvironmentalSuppliesReportExport implements FromView
{
    public function __construct(
        protected $rows,
        protected string $startDate,
        protected string $endDate
    ) {
    }

    public function view(): View
    {
        return view('reports.environmental_supplies_excel', [
            'rows'      => $this->rows,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
