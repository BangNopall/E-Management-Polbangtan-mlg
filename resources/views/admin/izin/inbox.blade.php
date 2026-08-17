@extends('layouts.main')

@section('container')
<div class="px-3 pt-4 sm:px-6 sm:pt-6">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                <i class="ri-inbox-archive-line text-teal-700 mr-2"></i> Inbox Persetujuan Perizinan
            </h1>
            <p class="text-xs sm:text-sm text-gray-500">Daftar pengajuan izin mahasiswa yang memerlukan tindakan keputusan Anda.</p>
        </div>
    </div>

    @include('partials.alert')

    @if ($approvals->isEmpty())
        <div class="bg-white border-2 border-gray-100 rounded-xl p-8 text-center shadow-xs">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-teal-50 text-teal-600 mb-3">
                <i class="ri-checkbox-circle-line text-2xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900">Semua Berkas Selesai!</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">Saat ini tidak ada pengajuan izin yang menunggu persetujuan Anda. Semua berkas telah diproses.</p>
        </div>
    @else
        <!-- Mobile View (Single Column / 360px Card Stack) -->
        <div class="space-y-3 md:hidden">
            @foreach ($approvals as $item)
                @php
                    $p = $item->pengajuan;
                @endphp
                <div class="bg-white border-2 border-gray-100 rounded-xl p-4 shadow-xs">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100">
                        <span class="text-[11px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                            {{ optional($p->jenisIzin)->nama ?? 'Izin' }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium">
                            Langkah {{ $item->urutan }} dari {{ $p->approvals->count() }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <h4 class="font-bold text-gray-900 text-sm flex items-center">
                            <i class="ri-user-3-line text-gray-400 mr-1.5 text-xs"></i> {{ $p->nama_snapshot }}
                        </h4>
                        <p class="text-xs text-gray-500 ml-5">{{ $p->prodi_snapshot }} ({{ $p->kelas_snapshot }})</p>
                    </div>

                    <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100 mb-3 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 text-[11px]">Tujuan:</span>
                            <span class="font-semibold text-gray-800">{{ $p->tujuan_lokasi }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 text-[11px]">Berangkat:</span>
                            <span class="font-bold text-teal-800">{{ optional($p->waktu_berangkat)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 text-[11px]">Kembali:</span>
                            <span class="font-bold text-amber-800">{{ optional($p->waktu_kembali)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.izin.persetujuan.review', $p->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                        <i class="ri-search-eye-line mr-1.5"></i> Review & Putuskan
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Desktop View (Structured Table) -->
        <div class="hidden md:block bg-white border-2 border-gray-100 rounded-xl shadow-xs overflow-hidden">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-[11px] border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Jenis Izin</th>
                        <th class="px-4 py-3">Tujuan & Keperluan</th>
                        <th class="px-4 py-3">Jadwal Perizinan</th>
                        <th class="px-4 py-3">Langkah</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($approvals as $item)
                        @php
                            $p = $item->pengajuan;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-gray-900 block text-sm">{{ $p->nama_snapshot }}</span>
                                <span class="text-gray-500 text-[11px]">{{ $p->nirm_snapshot }} • {{ $p->kelas_snapshot }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-teal-800 bg-teal-50 px-2 py-1 rounded border border-teal-200">
                                    {{ optional($p->jenisIzin)->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <span class="font-semibold text-gray-900 block truncate">{{ $p->tujuan_lokasi }}</span>
                                <span class="text-gray-500 truncate block text-[11px]">{{ $p->keperluan }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-teal-800 font-bold block">{{ optional($p->waktu_berangkat)->format('d M Y H:i') }}</span>
                                <span class="text-amber-800 font-semibold block text-[11px]">s/d {{ optional($p->waktu_kembali)->format('d M Y H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                    Langkah {{ $item->urutan }} dari {{ $p->approvals->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.izin.persetujuan.review', $p->id) }}" class="inline-flex items-center px-3 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-semibold text-xs rounded-lg transition shadow-2xs">
                                    <i class="ri-check-double-line mr-1"></i> Review
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $approvals->links() }}
        </div>
    @endif
</div>
@endsection
