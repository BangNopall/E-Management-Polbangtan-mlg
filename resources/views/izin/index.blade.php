@extends('layouts.main')

@section('container')
<div class="px-4 pt-6">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Izin Saya</h1>
            <p class="text-sm text-gray-500">Daftar pengajuan izin keluar, izin bermalam, dan kegiatan yang Anda ajukan.</p>
        </div>
        <div>
            <a href="{{ route('home.izin.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-medium text-sm rounded-lg shadow-sm transition">
                <i class="ri-add-line mr-1.5 text-lg"></i> Buat Pengajuan Izin
            </a>
        </div>
    </div>

    @include('partials.alert')

    <div class="bg-white border-2 border-gray-100 rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3">Nomor Surat / Jenis</th>
                        <th scope="col" class="px-4 py-3">Keberangkatan & Kembali</th>
                        <th scope="col" class="px-4 py-3">Tujuan</th>
                        <th scope="col" class="px-4 py-3 text-center">Status</th>
                        <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pengajuanList as $pengajuan)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                <div class="font-bold text-teal-800">{{ $pengajuan->nomor_surat ?? 'DRAFT' }}</div>
                                <div class="text-xs text-gray-500">{{ optional($pengajuan->jenisIzin)->nama ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs font-semibold text-gray-700">
                                    <i class="ri-flight-takeoff-line mr-1 text-teal-600"></i> {{ optional($pengajuan->waktu_berangkat)->format('d M Y H:i') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    <i class="ri-flight-land-line mr-1 text-amber-600"></i> {{ optional($pengajuan->waktu_kembali)->format('d M Y H:i') }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $pengajuan->tujuan_lokasi }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ $pengajuan->keperluan }}</div>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
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
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full border {{ $class }}">
                                    {{ strtoupper($pengajuan->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('home.izin.show', $pengajuan->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition">
                                    <i class="ri-eye-line mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="ri-file-paper-2-line text-4xl block text-gray-300 mb-2"></i>
                                Belum ada pengajuan izin. Klik tombol <strong>Buat Pengajuan Izin</strong> untuk membuat baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pengajuanList->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $pengajuanList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
