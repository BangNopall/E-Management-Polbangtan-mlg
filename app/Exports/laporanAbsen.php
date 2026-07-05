<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;

class laporanAbsen implements FromView
{
   
    public $data;
    public $startDate;
    public $endDate;

    public function __construct($data = null, $startDate = null, $endDate = null){
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View{
        $data = $this->data;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        return view('admin.generate.generate-excel', compact('data', 'startDate', 'endDate'));
    }
}
