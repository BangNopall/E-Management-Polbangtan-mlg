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
            'file' => 'required|file|mimes:xls,xlsx,csv|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $import = new LulusanImport();
            Excel::import($import, $request->file('file'));
            
            DB::commit();
            return redirect()->route('admin.sistem-admin')
                ->with('success', "Berhasil menghapus {$import->deletedCount} mahasiswa. Data tidak ditemukan: {$import->notFoundCount}");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Import error on lulusan: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return redirect()->route('admin.sistem-admin')
                ->with('error', 'Terjadi kesalahan saat memproses data impor kelulusan. Silakan periksa format file Anda.');
        }
    }
}
