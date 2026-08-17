<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanIzinExport implements FromView
{
    public function __construct(
        public $izins,
        public $tanggalMulai = null,
        public $tanggalSelesai = null
    ) {}

    public function view(): View
    {
        return view('admin.generate.generate-excel-izin', [
            'izins' => $this->izins,
            'tanggalMulai' => $this->tanggalMulai,
            'tanggalSelesai' => $this->tanggalSelesai,
        ]);
    }
}
