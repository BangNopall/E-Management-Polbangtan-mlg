<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanUkmExport implements FromView
{
    protected $presensis;
    protected $ukmNama;
    protected $startDateStr;
    protected $endDateStr;

    public function __construct($presensis, $ukmNama, $startDateStr, $endDateStr)
    {
        $this->presensis = $presensis;
        $this->ukmNama = $ukmNama;
        $this->startDateStr = $startDateStr;
        $this->endDateStr = $endDateStr;
    }

    public function view(): View
    {
        return view('admin.generate.generate-ukm-excel', [
            'presensis' => $this->presensis,
            'ukmNama' => $this->ukmNama,
            'startDateStr' => $this->startDateStr,
            'endDateStr' => $this->endDateStr,
        ]);
    }
}
