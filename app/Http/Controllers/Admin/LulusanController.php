<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\LulusanImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class LulusanController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        DB::beginTransaction();
        try {
            $import = new LulusanImport();
            Excel::import($import, $request->file('file'));
            
            DB::commit();
            return redirect()->route('admin.sistem-admin')
                ->with('success', "Berhasil menghapus {$import->deletedCount} mahasiswa. Data tidak ditemukan: {$import->notFoundCount}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.sistem-admin')
                ->with('error', 'Gagal memproses data: ' . $e->getMessage());
        }
    }
}
