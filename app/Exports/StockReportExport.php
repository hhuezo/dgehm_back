<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StockReportExport implements FromView
{
    protected $stock;
    protected $date;

    public function __construct($stock, $date)
    {
        $this->stock = $stock;
        $this->date  = $date;
    }

    public function view(): View
    {
        return view('reports.stock_excel', [
            'stock' => $this->stock,
            'date'  => $this->date,
        ]);
    }
}
