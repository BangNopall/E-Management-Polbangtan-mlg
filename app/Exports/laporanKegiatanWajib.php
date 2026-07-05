<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Exporter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class laporanKegiatanWajib implements FromView, WithColumnWidths, WithStyles
{
    public $dataExcel;
    public $dateRangeText;
    public $tanggalkegiatan;
    public $blokName;
    public $requestKegiatanUser;

    public function __construct($dataExcel = null, $dateRangeText = null, $tanggalkegiatan = null, $blokName = null, $requestKegiatanUser = null)
    {
        $this->dataExcel = $dataExcel;
        $this->dateRangeText = $dateRangeText;
        $this->tanggalkegiatan = $tanggalkegiatan;
        $this->blokName = $blokName;
        $this->requestKegiatanUser = $requestKegiatanUser;

    }
    public function view(): View
    {
        $getPresensiAllData = $this->dataExcel;
        $dateRangeText = $this->dateRangeText;
        $tanggalkegiatan = $this->tanggalkegiatan;
        $blokName = $this->blokName;
        $requestKegiatan = $this->requestKegiatanUser;

        // dd($getPresensiAllData, $dateRangeText, $tanggalkegiatan, $blokName, $requestKegiatan);

        return view('admin.generate.excel-kegiatanwajib', compact('getPresensiAllData', 'dateRangeText', 'tanggalkegiatan', 'blokName', 'requestKegiatan'));
    }

    public function columnWidths(): array
    {
        return [
            'A' => '4',
            'B' => '43',
            'C' => '10',
            'D' => '3',
            'E' => '3',
            'F' => '3',
            'G' => '3',
            'H' => '3',
            'I' => '3',
            'J' => '3',
            'K' => '3',
            'L' => '3',
            'M' => '3',
            'N' => '3',
            'O' => '3',
            'P' => '3',
            'Q' => '3',
            'R' => '3',
            'S' => '3',
            'T' => '3',
            'U' => '3',
            'V' => '3',
            'W' => '3',
            'X' => '3',
            'Y' => '3',
            'Z' => '3',
            'AA' => '3',
            'AB' => '3',
            'AC' => '3',
            'AD' => '3',
            'AE' => '3',
            'AF' => '3',
            'AG' => '3',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:AG1');
        $sheet->mergeCells('A2:AG2');
        $sheet->mergeCells('A3:AG3');
        $sheet->mergeCells('A4:AG4');
        $sheet->mergeCells('D5:AG5');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('A5:A6');

        $sheet->getStyle('A1:AG1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2:AG2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A3:AG3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:AG4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A5:AG5')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle('A:AG')->getAlignment()->setHorizontal('center')->setVertical('center');

        return [
            5    => ['font' => ['bold' => true]],
        ];
    }
}
