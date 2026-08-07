@extends('layouts.main')

@section('container')
<div class="px-4 pt-6">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Izin</h1>
            <p class="text-sm text-gray-500">Nomor Surat: <span class="font-bold text-teal-800">{{ $pengajuan->nomor_surat ?? 'DRAFT' }}</span></p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('home.izin.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                <i class="ri-arrow-left-line mr-1"></i> Kembali
            </a>
            @if (in_array($pengajuan->status, ['draft', 'diajukan', 'menunggu']))
                <form action="{{ route('home.izin.batal', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan izin ini?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-sm font-medium rounded-lg transition">
                        <i class="ri-close-circle-line mr-1"></i> Batalkan Pengajuan
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('partials.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Main Detail -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Summary Card -->
            <div class="bg-white border-2 border-gray-100 rounded-lg p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Jenis Izin</span>
                        <h3 class="text-lg font-bold text-gray-900">{{ optional($pengajuan->jenisIzin)->nama ?? '-' }}</h3>
                    </div>
                    @php
                        $badgeClasses = [
                            'draft' => 'bg-gray-100 text-gray-800 border-gray-300',
                            'diajukan' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'menunggu' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'ditolak' => 'bg-rose-100 text-rose-800 border-rose-300',
                            'berjalan' => 'bg-sky-100 text-sky-800 border-sky-300',
                            'selesai' => 'bg-blue-100 text-blue-800 border-blue-300',
                            'dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-300',
                        ];
                        $class = $badgeClasses[$pengajuan->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $class }}">
                        {{ strtoupper($pengajuan->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-xs text-gray-500 block">Waktu Keberangkatan</span>
                        <span class="font-bold text-teal-800 flex items-center mt-0.5">
                            <i class="ri-flight-takeoff-line mr-1.5 text-teal-600"></i>
                            {{ optional($pengajuan->waktu_berangkat)->format('d M Y H:i') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Waktu Perkiraan Kembali</span>
                        <span class="font-bold text-amber-800 flex items-center mt-0.5">
                            <i class="ri-flight-land-line mr-1.5 text-amber-600"></i>
                            {{ optional($pengajuan->waktu_kembali)->format('d M Y H:i') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Tujuan Lokasi</span>
                        <span class="font-semibold text-gray-900 mt-0.5 block">{{ $pengajuan->tujuan_lokasi }}</span>
                        @if ($pengajuan->alamat_tujuan)
                            <span class="text-xs text-gray-500 block mt-0.5">{{ $pengajuan->alamat_tujuan }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">UKM / Ormawa</span>
                        <span class="font-semibold text-gray-900 mt-0.5 block">{{ optional($pengajuan->ukm)->nama ?? '-' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 text-sm">
                    <span class="text-xs text-gray-500 block mb-1">Keperluan</span>
                    <p class="text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100">{{ $pengajuan->keperluan }}</p>
                </div>

                @if ($pengajuan->alasan_penolakan)
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-lg text-sm text-rose-800">
                        <span class="font-bold block mb-0.5">⚠️ Alasan Penolakan:</span>
                        <p>{{ $pengajuan->alasan_penolakan }}</p>
                    </div>
                @endif
            </div>

            <!-- Identity Snapshot -->
            <div class="bg-white border-2 border-gray-100 rounded-lg p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 text-base mb-3 pb-2 border-b border-gray-100 flex items-center">
                    <i class="ri-user-vcard-line text-teal-600 mr-2"></i> Identitas Pemohon (Snapshot)
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
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
                        <span class="font-bold text-gray-800">{{ $pengajuan->blok_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">No. HP / WA</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->no_hp_snapshot ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracker Sidebar -->
        <div class="bg-white border-2 border-gray-100 rounded-lg p-5 shadow-sm">
            <h3 class="font-bold text-gray-900 text-base mb-2 flex items-center">
                <i class="ri-route-line text-teal-600 mr-2"></i> Progress Persetujuan
            </h3>
            <p class="text-xs text-gray-500 mb-4">Lacak proses persetujuan oleh pejabat berwenang.</p>

            @include('izin.partials.tracker', ['approvals' => $pengajuan->approvals])
        </div>
    </div>
</div>
@endsection
