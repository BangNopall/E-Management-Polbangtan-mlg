<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Imports\DosenPaImport;
use Maatwebsite\Excel\Facades\Excel;

class DosenPaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $dosenPas = User::where('role_id', User::DOSEN_PA_ROLE_ID)
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(20)->withQueryString()
            ->withQueryString();

        return view('admin.dosen_pa.index', compact('dosenPas', 'search'));
    }

    public function show($id, Request $request)
    {
        $search = $request->input('search');
        $dosenPa = User::where('role_id', User::DOSEN_PA_ROLE_ID)->findOrFail($id);
        
        $mahasiswaList = $dosenPa->mahasiswaBimbingan()
            ->with(['kelas', 'prodi', 'blok'])
            ->when($search, function ($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%");
                });
            })
            ->paginate(20)->withQueryString()
            ->withQueryString();
        
        return view('admin.dosen_pa.show', compact('dosenPa', 'mahasiswaList', 'search'));
    }

    public function destroy($id)
    {
        $dosenPa = User::where('role_id', User::DOSEN_PA_ROLE_ID)->findOrFail($id);
        User::where('dosen_pa_id', $dosenPa->id)->update(['dosen_pa_id' => null]);
        $dosenPa->delete();
        
        return redirect()->route('admin.dosen_pa.index')->with('success', 'Dosen PA berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv'
        ]);

        try {
            Excel::import(new DosenPaImport, $request->file('file'));
            return redirect()->route('admin.dosen_pa.index')->with('success', 'Import file excel berhasil.');
        } catch (\Exception $e) {
            return redirect()->route('admin.dosen_pa.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
