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
            @if (in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']))
                <a href="{{ route('home.izin.pdf', $pengajuan->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold rounded-lg shadow-2xs transition">
                    <i class="ri-file-pdf-line mr-1"></i> Cetak / Download PDF
                </a>
            @endif
            @if (in_array($pengajuan->status, ['draft', 'diajukan', 'menunggu']))
                <form action="{{ route('home.izin.batal', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan izin ini?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-sm font-medium rounded-lg transition">
                        <i class="ri-close-circle-line mr-1"></i> Batalkan Pengajuan
                    </button>
                </form>
            @endif
            @if ($pengajuan->status === 'berjalan' && optional($pengajuan->jenisIzin)->butuh_konfirmasi_tiba)
                <button type="button" data-modal-target="modal-konfirmasi" data-modal-toggle="modal-konfirmasi" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    <i class="ri-map-pin-user-line mr-1"></i> Konfirmasi Kedatangan
                </button>
            @endif
        </div>
    </div>

    @include('partials.alert')

    @if ($pengajuan->status === 'berjalan' && optional($pengajuan->jenisIzin)->butuh_konfirmasi_tiba)
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start">
            <i class="ri-information-line text-blue-600 text-xl mr-3 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-blue-900 mb-1">Menunggu Konfirmasi Kedatangan</h4>
                <p class="text-sm text-blue-800 mb-2">Anda sedang dalam masa izin (berjalan). Mohon konfirmasi kedatangan segera setelah tiba di asrama dengan melampirkan foto bukti.</p>
                <button type="button" data-modal-target="modal-konfirmasi" data-modal-toggle="modal-konfirmasi" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded font-medium hover:bg-blue-700">Konfirmasi Sekarang</button>
            </div>
        </div>
    @endif

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
                
                @if ($pengajuan->tiba_at)
                    <div class="mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <h4 class="font-bold text-gray-900 mb-2 flex items-center"><i class="ri-map-pin-user-line text-blue-600 mr-2"></i> Detail Konfirmasi Kedatangan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-gray-500 block">Waktu Tiba Aktual</span>
                                <span class="font-bold text-gray-800 block">{{ \Carbon\Carbon::parse($pengajuan->tiba_at)->format('d M Y H:i') }}</span>
                            </div>
                            @if ($pengajuan->tiba_bukti_path)
                                <div>
                                    <span class="text-xs text-gray-500 block mb-1">Bukti Kedatangan</span>
                                    <a href="{{ Storage::url($pengajuan->tiba_bukti_path) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:underline">
                                        <i class="ri-image-line mr-1"></i> Lihat Foto Bukti
                                    </a>
                                </div>
                            @endif
                        </div>
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
                        <span class="font-bold text-gray-800">{{ $pengajuan->no_kamar_snapshot ?? '-' }}</span>
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

{{-- Modal Konfirmasi Kedatangan --}}
@if ($pengajuan->status === 'berjalan' && optional($pengajuan->jenisIzin)->butuh_konfirmasi_tiba)
    <div id="modal-konfirmasi" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Konfirmasi Kedatangan
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="modal-konfirmasi">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </button>
                </div>
                <form action="{{ route('home.izin.konfirmasi-tiba', $pengajuan->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-4 md:p-5">
                        <p class="text-sm text-gray-500 mb-4">Mohon unggah foto bukti kedatangan Anda di asrama (misal: foto selfie di depan asrama atau bersama petugas).</p>
                        
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900" for="foto_bukti">Upload Foto Bukti <span class="text-red-600">*</span></label>
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" id="foto_bukti" name="foto_bukti" type="file" accept="image/*" required capture="environment">
                            <p class="mt-1 text-xs text-gray-500">Maksimal ukuran file 2MB. Format: JPG, PNG.</p>
                        </div>
                        
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Kirim Bukti Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
