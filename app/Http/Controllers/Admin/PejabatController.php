<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlokRuangan;
use App\Models\Pejabat;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;

class PejabatController extends Controller
{
    public function index()
    {
        $pejabats = Pejabat::with('user')->orderBy('created_at', 'desc')->get();
        $users = User::where('role_id', User::PEJABAT_ROLE_ID)
            ->select('id', 'name', 'email', 'role_id')
            ->orderBy('name', 'asc')
            ->get();
        $prodis = Prodi::orderBy('prodi', 'asc')->get();
        $bloks = BlokRuangan::orderBy('name', 'asc')->get();
        $title = "Data Pejabat";

        return view('admin.pejabat.index', compact('pejabats', 'users', 'prodis', 'bloks', 'title'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jabatan' => 'required|in:kaprodi,kepala_asrama,unit_kemahasiswaan',
            'lingkup' => 'required|in:global,prodi,blok',
            'lingkup_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        Pejabat::create($validated);

        return redirect()->route('admin.pejabat.index')->with('success', 'Data pejabat berhasil ditambahkan.');
    }

    public function update(Request $request, Pejabat $pejabat)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jabatan' => 'required|in:kaprodi,kepala_asrama,unit_kemahasiswaan',
            'lingkup' => 'required|in:global,prodi,blok',
            'lingkup_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;

        $pejabat->update($validated);

        return redirect()->route('admin.pejabat.index')->with('success', 'Data pejabat berhasil diperbarui.');
    }

    public function destroy(Pejabat $pejabat)
    {
        $pejabat->delete();

        return redirect()->route('admin.pejabat.index')->with('success', 'Data pejabat berhasil dihapus.');
    }
}
