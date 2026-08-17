@extends('layouts.publik')

@section('title', 'Verifikasi Keabsahan Surat Perizinan Asrama')

@section('container')
<div class="space-y-4">
    <!-- Validity Banner Status -->
    @if (in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']))
        <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-5 text-center shadow-xs">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full mb-3 shadow-inner">
                <i class="ri-checkbox-circle-fill text-3xl"></i>
            </div>
            <h2 class="text-lg font-extrabold text-emerald-900 uppercase tracking-wide">Surat Izin Resmi & Valid</h2>
            <p class="text-xs text-emerald-700 font-medium mt-1">Dokumen perizinan ini terdaftar secara sah di Sistem Manajemen Asrama Polbangtan Malang.</p>
            <div class="mt-3 inline-block bg-emerald-700 text-white font-mono text-xs px-3 py-1 rounded-full font-bold">
                {{ $pengajuan->nomor_surat }}
            </div>
        </div>
    @else
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 text-center shadow-xs">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-rose-100 text-rose-600 rounded-full mb-3 shadow-inner">
                <i class="ri-close-circle-fill text-3xl"></i>
            </div>
            <h2 class="text-lg font-extrabold text-rose-900 uppercase tracking-wide">Surat Izin Tidak Aktif / Batal</h2>
            <p class="text-xs text-rose-700 font-medium mt-1">
                Dokumen ini berstatus: <strong class="uppercase font-bold">{{ $pengajuan->status }}</strong>. Surat tidak dapat digunakan sebagai bukti izin yang sah.
            </p>
        </div>
    @endif

    <!-- Data Mahasiswa & Perizinan (Privasi Terjaga: Tanpa No HP / Kamar / Keperluan) -->
    <div class="bg-white border-2 border-gray-100 rounded-2xl p-5 shadow-xs space-y-4 text-xs">
        <h3 class="font-bold text-gray-900 text-sm pb-2 border-b border-gray-100 flex items-center">
            <i class="ri-user-vcard-line text-teal-700 mr-2 text-base"></i> Identitas Mahasiswa Pemohon
        </h3>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <span class="text-gray-400 block text-[11px]">Nama Mahasiswa</span>
                <span class="font-bold text-gray-900 text-sm block mt-0.5">{{ $pengajuan->nama_snapshot }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-[11px]">NIRM</span>
                <span class="font-bold text-gray-900 block mt-0.5">{{ $pengajuan->nirm_snapshot ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-[11px]">Program Studi</span>
                <span class="font-semibold text-gray-800 block mt-0.5">{{ $pengajuan->prodi_snapshot }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-[11px]">Kelas</span>
                <span class="font-semibold text-gray-800 block mt-0.5">{{ $pengajuan->kelas_snapshot }}</span>
            </div>
        </div>

        <h3 class="font-bold text-gray-900 text-sm pt-2 pb-2 border-b border-gray-100 flex items-center">
            <i class="ri-calendar-event-line text-teal-700 mr-2 text-base"></i> Masa Berlaku & Detail Perizinan
        </h3>

        <div class="space-y-2">
            <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-gray-500 font-medium">Jenis Izin:</span>
                <span class="font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                    {{ optional($pengajuan->jenisIzin)->nama ?? '-' }}
                </span>
            </div>

            <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-gray-500 font-medium">Tujuan Lokasi:</span>
                <span class="font-bold text-gray-800">{{ $pengajuan->tujuan_lokasi }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="p-2.5 bg-teal-50/50 rounded-xl border border-teal-100">
                    <span class="text-[10px] text-teal-700 font-bold block uppercase">Waktu Berangkat</span>
                    <span class="font-extrabold text-teal-900 text-xs block mt-0.5">
                        {{ optional($pengajuan->waktu_berangkat)->format('d M Y H:i') }} WIB
                    </span>
                </div>
                <div class="p-2.5 bg-amber-50/50 rounded-xl border border-amber-100">
                    <span class="text-[10px] text-amber-700 font-bold block uppercase">Waktu Perkiraan Kembali</span>
                    <span class="font-extrabold text-amber-900 text-xs block mt-0.5">
                        {{ optional($pengajuan->waktu_kembali)->format('d M Y H:i') }} WIB
                    </span>
                </div>
            </div>
        </div>

        <h3 class="font-bold text-gray-900 text-sm pt-2 pb-2 border-b border-gray-100 flex items-center">
            <i class="ri-shield-user-line text-teal-700 mr-2 text-base"></i> Riwayat Pengesahan Pejabat
        </h3>

        <div class="space-y-2">
            @foreach ($pengajuan->approvals as $app)
                <div class="flex items-center justify-between p-2 text-xs bg-white rounded-lg border border-gray-100">
                    <div>
                        <span class="font-bold text-gray-800 block">{{ $app->approver_nama_snapshot ?? '-' }}</span>
                        <span class="text-[10px] text-gray-400 block">{{ $app->label_snapshot }}</span>
                    </div>
                    <div>
                        @if ($app->status === 'disetujui')
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                DISETUJUI {{ $app->acted_at ? '(' . $app->acted_at->format('d/m H:i') . ')' : '' }}
                            </span>
                        @elseif ($app->status === 'dilewati')
                            <span class="text-[10px] font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded">DILEWATI</span>
                        @else
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">{{ strtoupper($app->status) }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
