<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUkmRequest;
use App\Http\Requests\UpdateUkmRequest;
use App\Models\Role;
use App\Models\Ukm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class UkmController extends Controller
{
    public function index(Request $request)
    {
        $query = Ukm::withCount('members')->with('pelatih.user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $ukms = $query->latest()->paginate(10);
        $stafPelatih = User::whereIn('role_id', [User::PELATIH_ROLE_ID, User::PEMBINA_ROLE_ID])->get();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('admin.ukm.partials.ukm_table', compact('ukms'))->render(),
            ]);
        }

        return view('admin.ukm.index', compact('ukms', 'stafPelatih'));
    }

    public function store(StoreUkmRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['slug'] = Str::slug($validated['nama']);
            $validated['created_by'] = auth()->id();
            $validated['is_active'] = true;

            $ukm = Ukm::create($validated);

            if ($request->filled('pelatih_id')) {
                $ukm->members()->create([
                    'user_id' => $request->pelatih_id,
                    'peran' => 'pelatih',
                    'status' => 'aktif',
                    'tanggal_bergabung' => now(),
                ]);
            }

            return redirect()->route('admin.ukm.index')->with('success', 'UKM ' . $ukm->nama . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            return redirect()->route('admin.ukm.index')->with('error', $th->getMessage());
        }
    }

    public function show($id)
    {
        $ukm = Ukm::with(['members.user', 'jadwals'])->findOrFail($id);
        $mahasiswas = User::where('role_id', User::USER_ROLE_ID)->get();
        $staf = User::whereIn('role_id', [User::PELATIH_ROLE_ID, User::PEMBINA_ROLE_ID])->get();

        return view('admin.ukm.show', compact('ukm', 'mahasiswas', 'staf'));
    }

    public function update(UpdateUkmRequest $request, $id)
    {
        try {
            $ukm = Ukm::findOrFail($id);
            $validated = $request->validated();
            if (isset($validated['nama'])) {
                $validated['slug'] = Str::slug($validated['nama']);
            }

            $ukm->update($validated);

            return redirect()->route('admin.ukm.index')->with('success', 'Data UKM ' . $ukm->nama . ' berhasil diperbarui.');
        } catch (\Throwable $th) {
            return redirect()->route('admin.ukm.index')->with('error', $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $ukm = Ukm::findOrFail($id);
            $nama = $ukm->nama;
            $ukm->delete();

            return redirect()->route('admin.ukm.index')->with('success', 'UKM ' . $nama . ' berhasil dihapus.');
        } catch (\Throwable $th) {
            return redirect()->route('admin.ukm.index')->with('error', $th->getMessage());
        }
    }
}
