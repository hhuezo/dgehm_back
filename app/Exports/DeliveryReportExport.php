<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DeliveryReportExport implements FromView
{
    protected $products;
    protected $organizationalUnits;
    protected $startDate;
    protected $endDate;

    public function __construct($products, $organizationalUnits, $startDate, $endDate)
    {
        $this->products  = $products;
        $this->organizationalUnits = $organizationalUnits;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function view(): View
    {
        return view('reports.delivery_excel', [
            'products'  => $this->products,
            'organizationalUnits' => $this->organizationalUnits,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
