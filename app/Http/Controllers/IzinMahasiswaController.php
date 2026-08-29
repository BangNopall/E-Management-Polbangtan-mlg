<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengajuanIzinRequest;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\UkmMember;
use App\Services\Izin\ApproverResolver;
use App\Services\Izin\PengajuanIzinService;
use Illuminate\Http\Request;

class IzinMahasiswaController extends Controller
{
    protected PengajuanIzinService $pengajuanService;
    protected ApproverResolver $resolver;

    public function __construct(PengajuanIzinService $pengajuanService, ApproverResolver $resolver)
    {
        $this->pengajuanService = $pengajuanService;
        $this->resolver = $resolver;
    }

    public function index()
    {
        $pengajuanList = PengajuanIzin::where('user_id', auth()->id())
            ->with(['jenisIzin', 'ukm'])
            ->latest()
            ->paginate(20)->withQueryString();

        return view('izin.index', compact('pengajuanList'));
    }

    public function create()
    {
        $user = auth()->user();
        $user->loadMissing('kelas');

        $profileIncomplete = !$user->prodi_id ||
            !$user->kelas_id ||
            !$user->blok_ruangan_id;
            // || !optional($user->kelas)->dosen_pa_id;

        $jenisIzins = JenisIzin::where('is_active', true)
            ->with(['steps' => fn ($q) => $q->orderBy('urutan', 'asc')])
            ->get();

        $myUkms = UkmMember::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->with('ukm')
            ->get()
            ->pluck('ukm')
            ->filter();

        return view('izin.create', compact('jenisIzins', 'myUkms', 'profileIncomplete'));
    }

    public function pratinjauAlur(Request $request)
    {
        $request->validate([
            'jenis_izin_id' => 'required|integer|exists:jenis_izins,id',
            'ukm_id' => 'nullable|integer|exists:ukms,id',
            'waktu_berangkat' => 'nullable|date',
        ]);

        $jenisIzin = JenisIzin::with(['steps' => fn ($q) => $q->orderBy('urutan', 'asc')])
            ->findOrFail($request->jenis_izin_id);

        $user = auth()->user();
        $context = [
            'user' => $user,
            'ukm_id' => $request->ukm_id,
            'waktu_berangkat' => $request->waktu_berangkat ?? now()->toDateTimeString(),
        ];

        $steps = [];
        foreach ($jenisIzin->steps as $step) {
            $resolution = $this->resolver->resolve($step, $context);
            $candidatesFormatted = $resolution['candidates']->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                ];
            })->values()->toArray();

            $steps[] = [
                'urutan' => $step->urutan,
                'label' => $step->label,
                'mode' => $step->mode,
                'resolve_saat' => $step->resolve_saat,
                'candidates' => $candidatesFormatted,
                'is_fallback' => $resolution['is_fallback'],
                'resolver_used' => $resolution['resolver_used'],
            ];
        }

        return response()->json([
            'success' => true,
            'steps' => $steps,
        ]);
    }

    public function store(StorePengajuanIzinRequest $request)
    {
        $pengajuan = $this->pengajuanService->ajukan(auth()->user(), $request->validated());


        return redirect()
            ->route('home.izin.show', $pengajuan->id)
            ->with('success', 'Pengajuan izin berhasil dibuat.');
    }

    public function show(PengajuanIzin $pengajuan)
    {
        abort_unless($pengajuan->user_id === auth()->id(), 403);

        $pengajuan->load(['jenisIzin', 'ukm', 'approvals.approver', 'approvals.aktor']);

        return view('izin.show', compact('pengajuan'));
    }

    public function downloadPdf(PengajuanIzin $pengajuan)
    {
        abort_unless($pengajuan->user_id === auth()->id(), 403);
        abort_unless(in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']), 403, 'Surat izin belum disetujui.');

        $fileName = 'Surat_Izin_'.str_replace('/', '_', $pengajuan->nomor_surat ?? 'DRAFT').'.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('izins/'.$fileName)) {
            return response()->file(storage_path('app/public/izins/'.$fileName));
        }

        return back()->with('info', 'File Surat Izin sedang diproses oleh sistem (Background Job). Silakan tunggu beberapa saat dan coba lagi.');
    }

    public function batal(PengajuanIzin $pengajuan)
    {
        abort_unless($pengajuan->user_id === auth()->id(), 403);

        try {
            $this->pengajuanService->batalkan($pengajuan, auth()->user());
            return redirect()
                ->route('home.izin.show', $pengajuan->id)
                ->with('success', 'Pengajuan izin berhasil dibatalkan.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function konfirmasiTiba(Request $request, PengajuanIzin $pengajuan)
    {
        abort_unless($pengajuan->user_id === auth()->id(), 403);
        abort_unless($pengajuan->status === 'berjalan', 403, 'Izin tidak dalam status berjalan.');
        abort_unless(optional($pengajuan->jenisIzin)->butuh_konfirmasi_tiba, 403, 'Izin ini tidak memerlukan konfirmasi tiba.');

        $request->validate([
            'foto_bukti' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('foto_bukti')->store('bukti_tiba', 'public');

        $pengajuan->update([
            'tiba_at' => now(),
            'tiba_bukti_path' => $path,
            'tiba_dikonfirmasi_oleh' => auth()->id(),
            'status' => 'selesai'
        ]);

        return redirect()->back()->with('success', 'Konfirmasi kedatangan berhasil disimpan. Izin telah selesai.');
    }
}
