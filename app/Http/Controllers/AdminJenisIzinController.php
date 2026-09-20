<?php

namespace App\Http\Controllers;

use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminJenisIzinController extends Controller
{
    public function index()
    {
        $jenisIzins = JenisIzin::withCount('steps')->latest()->get();
        return view('admin.izin.jenis.index', compact('jenisIzins'));
    }

    public function create()
    {
        return view('admin.izin.jenis.form', [
            'jenisIzin' => new JenisIzin(),
            'steps' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInput($request);

        DB::transaction(function () use ($validated, $request) {
            $jenisIzin = JenisIzin::create([
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'butuh_bermalam' => $request->boolean('butuh_bermalam'),
                'butuh_ukm' => $request->boolean('butuh_ukm'),
                'butuh_konfirmasi_tiba' => $request->boolean('butuh_konfirmasi_tiba'),
                'min_ajukan_jam' => $validated['min_ajukan_jam'],
                'maks_durasi_jam' => $validated['maks_durasi_jam'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncSteps($jenisIzin, $validated['steps']);
        });

        return redirect()->route('admin.jenis.index')
            ->with('success', 'Jenis izin baru dan alur persetujuan berhasil dibuat.');
    }

    public function edit(JenisIzin $jeni)
    {
        $jenisIzin = $jeni->load('steps');
        $steps = $jenisIzin->steps->toArray();

        return view('admin.izin.jenis.form', compact('jenisIzin', 'steps'));
    }

    public function update(Request $request, JenisIzin $jeni)
    {
        $jenisIzin = $jeni;
        $validated = $this->validateInput($request, $jenisIzin->id);

        DB::transaction(function () use ($jenisIzin, $validated, $request) {
            $jenisIzin->update([
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'butuh_bermalam' => $request->boolean('butuh_bermalam'),
                'butuh_ukm' => $request->boolean('butuh_ukm'),
                'butuh_konfirmasi_tiba' => $request->boolean('butuh_konfirmasi_tiba'),
                'min_ajukan_jam' => $validated['min_ajukan_jam'],
                'maks_durasi_jam' => $validated['maks_durasi_jam'],
                'is_active' => $request->boolean('is_active'),
            ]);

            // Keterisolasian Pengajuan Berjalan: Hanya memperbarui template IzinWorkflowStep.
            // Pengajuan yang sedang berjalan tidak tersentuh karena sudah dibekukan di izin_approvals.
            $jenisIzin->steps()->delete();
            $this->syncSteps($jenisIzin, $validated['steps']);
        });

        return redirect()->route('admin.jenis.index')
            ->with('success', 'Jenis izin dan alur persetujuan berhasil diperbarui.');
    }

    public function destroy(JenisIzin $jeni)
    {
        $jenisIzin = $jeni;

        // Cegah hapus jika sudah dipakai di pengajuan
        if ($jenisIzin->pengajuan()->exists()) {
            return redirect()->back()->with('error', 'Jenis izin tidak dapat dihapus karena sudah memiliki riwayat pengajuan. Silakan nonaktifkan saja.');
        }

        $jenisIzin->delete();

        return redirect()->route('admin.jenis.index')
            ->with('success', 'Jenis izin berhasil dihapus.');
    }

    /**
     * Validasi input Form & Editor Alur Persetujuan.
     */
    protected function validateInput(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:jenis_izins,kode,' . $ignoreId],
            'nama' => ['required', 'string', 'max:255'],
            'min_ajukan_jam' => ['required', 'integer', 'min:0'],
            'maks_durasi_jam' => ['required', 'integer', 'min:1'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.urutan' => ['required', 'integer', 'min:1'],
            'steps.*.label' => ['required', 'string', 'max:255'],
            'steps.*.resolver' => ['required', 'string', 'in:dosen_pa,pembina_ukm,petugas_jaga,pejabat'],
            'steps.*.jabatan' => ['nullable', 'array'],
            'steps.*.mode' => ['required', 'string', 'in:any,all'],
        ], [
            'steps.required' => 'Wajib menyusun minimal 1 langkah persetujuan.',
            'steps.min' => 'Wajib menyusun minimal 1 langkah persetujuan.',
        ]);

        $steps = $validated['steps'];

        // 1. Validasi Urutan: Wajib berurutan 1, 2, 3... tanpa bolong atau duplikat
        $urutanList = collect($steps)->pluck('urutan')->map(fn($v) => (int)$v)->sort()->values()->toArray();
        $expectedRange = range(1, count($steps));

        if ($urutanList !== $expectedRange) {
            throw ValidationException::withMessages([
                'steps' => 'Urutan langkah persetujuan tidak valid (harus berurutan 1, 2, 3... tanpa bolong atau duplikat).',
            ]);
        }

        // 2. Validasi Resolver Pejabat: Wajib mengisi array jabatan
        foreach ($steps as $index => $step) {
            if ($step['resolver'] === 'pejabat') {
                $jabatan = array_filter($step['jabatan'] ?? []);
                if (empty($jabatan)) {
                    throw ValidationException::withMessages([
                        "steps.{$index}.jabatan" => "Langkah {$step['urutan']}: Resolver 'pejabat' wajib memilih minimal satu jabatan.",
                    ]);
                }
            }
        }

        return $validated;
    }

    /**
     * Simpan langkah persetujuan ke database.
     */
    protected function syncSteps(JenisIzin $jenisIzin, array $steps): void
    {
        foreach ($steps as $step) {
            IzinWorkflowStep::create([
                'jenis_izin_id' => $jenisIzin->id,
                'urutan' => $step['urutan'],
                'label' => $step['label'],
                'resolver' => $step['resolver'],
                'jabatan' => $step['resolver'] === 'pejabat' ? array_values(array_filter($step['jabatan'] ?? [])) : null,
                'mode' => $step['mode'],
                'resolve_saat' => $step['resolver'] === 'petugas_jaga' ? 'langkah_aktif' : 'submit',
            ]);
        }
    }
}
