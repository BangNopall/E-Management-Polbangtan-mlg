@extends('layouts.main')

@section('container')
<div class="px-4 pt-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="ri-shield-user-line text-teal-700 mr-2"></i> Detail Perizinan & Audit Trail
            </h1>
            <p class="text-sm text-gray-500">
                Nomor Surat: <span class="font-mono font-bold text-teal-800">{{ $pengajuan->nomor_surat ?? 'DRAFT / BELUM DISETUJUI' }}</span>
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.izin.data.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                <i class="ri-arrow-left-line mr-1"></i> Kembali ke Data
            </a>
            @if (in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']))
                <a href="{{ route('admin.izin.persetujuan.pdf', $pengajuan->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                    <i class="ri-file-pdf-line mr-1"></i> Download PDF
                </a>
            @endif
        </div>
    </div>

    @include('partials.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Status & Rincian Main Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border-2 border-gray-100 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Jenis Perizinan</span>
                        <h3 class="text-lg font-bold text-gray-900">{{ optional($pengajuan->jenisIzin)->nama ?? '-' }}</h3>
                    </div>
                    @php
                        $badgeStyle = [
                            'diajukan' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'berjalan' => 'bg-sky-100 text-sky-800 border-sky-300',
                            'selesai' => 'bg-blue-100 text-blue-800 border-blue-300',
                            'terlambat' => 'bg-rose-100 text-rose-800 border-rose-300',
                            'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-300',
                            'kadaluarsa' => 'bg-gray-100 text-gray-500 border-gray-300',
                        ];
                        $style = $badgeStyle[$pengajuan->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $style }}">
                        {{ strtoupper($pengajuan->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm mb-4">
                    <div>
                        <span class="text-xs text-gray-400 block">Waktu Keberangkatan</span>
                        <span class="font-bold text-teal-800 flex items-center mt-0.5">
                            <i class="ri-flight-takeoff-line mr-1.5 text-teal-600"></i>
                            {{ optional($pengajuan->waktu_berangkat)->format('d M Y H:i') }} WIB
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Waktu Perkiraan Kembali</span>
                        <span class="font-bold text-amber-800 flex items-center mt-0.5">
                            <i class="ri-flight-land-line mr-1.5 text-amber-600"></i>
                            {{ optional($pengajuan->waktu_kembali)->format('d M Y H:i') }} WIB
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Tujuan Lokasi</span>
                        <span class="font-semibold text-gray-900 block mt-0.5">{{ $pengajuan->tujuan_lokasi }}</span>
                        @if ($pengajuan->alamat_tujuan)
                            <span class="text-xs text-gray-500 block mt-0.5">{{ $pengajuan->alamat_tujuan }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">UKM / Ormawa</span>
                        <span class="font-semibold text-gray-900 block mt-0.5">{{ optional($pengajuan->ukm)->nama ?? '-' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 text-xs sm:text-sm">
                    <span class="text-xs text-gray-400 block mb-1">Keperluan</span>
                    <p class="text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 font-medium leading-relaxed">{{ $pengajuan->keperluan }}</p>
                </div>
            </div>

            <!-- Identitas Snapshot -->
            <div class="bg-white border-2 border-gray-100 rounded-xl p-5 shadow-xs">
                <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-3 pb-2 border-b border-gray-100 flex items-center">
                    <i class="ri-user-vcard-line text-teal-700 mr-2"></i> Snapshot Pemohon saat Pengajuan
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-400 block">Nama Mahasiswa</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->nama_snapshot }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">NIRM</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->nirm_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Program Studi</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->prodi_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Kelas</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->kelas_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Blok / Kamar</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->no_kamar_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">No. HP / WA</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->no_hp_snapshot ?? '-' }}</span>
                    </div>
                </div>
        </div>

        @if ($pengajuan->tiba_at || $pengajuan->tiba_bukti_path)
            <div class="bg-white border-2 border-emerald-100 rounded-xl p-5 shadow-xs mb-6">
                <h3 class="font-bold text-emerald-900 text-sm sm:text-base mb-3 pb-2 border-b border-emerald-100 flex items-center">
                    <i class="ri-map-pin-user-line text-emerald-600 mr-2"></i> Detail Konfirmasi Kedatangan Lokasi Tujuan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-400 block">Waktu Tiba Dilaporkan</span>
                        <span class="font-bold text-gray-800">{{ optional($pengajuan->tiba_at)->format('d M Y H:i:s') ?? '-' }} WIB</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Dikonfirmasi Oleh (ID)</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->tiba_dikonfirmasi_oleh ?? '-' }}</span>
                    </div>
                    @if ($pengajuan->tiba_bukti_path)
                        <div class="sm:col-span-2">
                            <span class="text-xs text-gray-400 block mb-2">Foto Bukti Tiba</span>
                            <a href="{{ Storage::url($pengajuan->tiba_bukti_path) }}" target="_blank" class="inline-block relative group">
                                <img src="{{ Storage::url($pengajuan->tiba_bukti_path) }}" class="h-32 w-auto object-cover rounded-lg border-2 border-emerald-200" alt="Bukti Kedatangan">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-lg">
                                    <i class="ri-search-eye-line text-white text-2xl"></i>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        </div>

        <!-- Audit Trail Sidebar -->
        <div class="bg-white border-2 border-gray-100 rounded-xl p-5 shadow-xs space-y-4">
            <h3 class="font-bold text-gray-900 text-base pb-2 border-b border-gray-100 flex items-center">
                <i class="ri-history-line text-teal-700 mr-2"></i> Riwayat Audit Trail
            </h3>

            <div class="space-y-3">
                @foreach ($pengajuan->approvals as $app)
                    <div class="p-3 border border-gray-100 rounded-lg text-xs space-y-1 bg-gray-50/50">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800" x-text="'Langkah {{ $app->urutan }}: {{ $app->label_snapshot }}'"></span>
                            @if ($app->status === 'disetujui')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800">DISETUJUI</span>
                            @elseif ($app->status === 'ditolak')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-800">DITOLAK</span>
                            @elseif ($app->status === 'dilewati')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-600">DILEWATI</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-800">MENUNGGU</span>
                            @endif
                        </div>

                        @if ($app->approver_nama_snapshot)
                            <div class="text-gray-600 mt-1">
                                <span class="font-medium text-gray-400 block text-[10px]">Approver Ditargetkan:</span>
                                <span class="font-semibold text-gray-900">{{ $app->approver_nama_snapshot }}</span>
                            </div>
                        @endif

                        @if ($app->acted_at)
                            <div class="pt-2 mt-1 border-t border-gray-200 text-[10px] space-y-0.5 text-gray-500 font-mono">
                                <div>Aktor: <strong class="text-gray-800">{{ optional($app->aktor)->name ?? 'Sistem' }}</strong></div>
                                <div>Waktu: <strong>{{ $app->acted_at->format('d/m/Y H:i:s') }}</strong></div>
                                <div>IP Address: <strong>{{ $app->acted_ip ?? '-' }}</strong></div>
                                <div class="truncate">User-Agent: {{ $app->acted_user_agent ?? '-' }}</div>
                            </div>
                        @endif

                        @if ($app->catatan)
                            <div class="mt-1 p-2 bg-white rounded border border-gray-200 italic text-[11px] text-gray-700">
                                "{{ $app->catatan }}"
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
