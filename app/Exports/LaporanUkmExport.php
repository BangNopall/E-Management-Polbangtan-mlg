<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanUkmExport implements FromView
{
    protected $presensis;
    protected $ukmNama;

    public function __construct($presensis, $ukmNama)
    {
        $this->presensis = $presensis;
        $this->ukmNama = $ukmNama;
    }

    public function view(): View
    {
        return view('admin.generate.generate-ukm-excel', [
            'presensis' => $this->presensis,
            'ukmNama' => $this->ukmNama,
        ]);
    }
}
