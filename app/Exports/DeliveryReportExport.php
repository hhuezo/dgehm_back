<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DeliveryReportExport implements FromView
{
    protected $products;
    protected $offices;
    protected $startDate;
    protected $endDate;

    public function __construct($products, $offices, $startDate, $endDate)
    {
        $this->products  = $products;
        $this->offices   = $offices;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function view(): View
    {
        return view('reports.delivery_excel', [
            'products'  => $this->products,
            'offices'   => $this->offices,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
