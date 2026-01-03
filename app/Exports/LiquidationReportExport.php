<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LiquidationReportExport implements FromView, ShouldAutoSize
{
    protected $accounts;
    protected $products;
    protected $startDate;
    protected $endDate;

    public function __construct($accounts, $products, $startDate, $endDate)
    {
        $this->accounts  = $accounts;
        $this->products  = $products;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function view(): View
    {
        return view('reports.liquidation_excel', [
            'accounts'  => $this->accounts,
            'products'  => $this->products,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }
}
