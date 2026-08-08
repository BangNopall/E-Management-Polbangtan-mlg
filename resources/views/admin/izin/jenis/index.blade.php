@extends('layouts.main')

@section('container')
<div class="px-4 pt-6">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="ri-settings-4-line text-teal-700 mr-2"></i> Kelola Jenis Izin & Alur Persetujuan
            </h1>
            <p class="text-sm text-gray-500">Konfigurasi jenis perizinan, batas waktu, dan susun alur penandatanganan (workflow steps).</p>
        </div>
        <div>
            <a href="{{ route('admin.jenis.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-lg shadow-sm transition">
                <i class="ri-add-line mr-1 text-base"></i> Tambah Jenis Izin Baru
            </a>
        </div>
    </div>

    @include('partials.alert')

    <div class="bg-white border-2 border-gray-100 rounded-xl shadow-xs overflow-hidden">
        <table class="w-full text-left text-xs text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-[11px] border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3">Kode & Nama Jenis Izin</th>
                    <th class="px-4 py-3">Syarat & Aturan</th>
                    <th class="px-4 py-3">Lead Time & Durasi</th>
                    <th class="px-4 py-3 text-center">Jumlah Langkah</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($jenisIzins as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200 block w-fit mb-1">{{ $item->kode }}</span>
                            <span class="font-bold text-gray-900 text-sm block">{{ $item->nama }}</span>
                        </td>
                        <td class="px-4 py-3 space-y-1">
                            @if ($item->butuh_ukm)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-purple-50 text-purple-700 border border-purple-200 block w-fit">Syarat UKM</span>
                            @endif
                            @if ($item->butuh_bermalam)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-50 text-amber-700 border border-amber-200 block w-fit">Izin Bermalam</span>
                            @endif
                            @if ($item->butuh_konfirmasi_tiba)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-sky-50 text-sky-700 border border-sky-200 block w-fit">Konfirmasi Tiba</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="block">Min Lead: <strong>{{ $item->min_ajukan_jam }} Jam</strong></span>
                            <span class="block">Maks Durasi: <strong>{{ $item->maks_durasi_jam }} Jam</strong></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 font-bold text-xs bg-gray-100 text-gray-800 rounded-full">
                                {{ $item->steps_count }} Langkah
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->is_active)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">AKTIF</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 text-gray-600 border border-gray-300">NONAKTIF</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-1">
                            <a href="{{ route('admin.jenis.edit', $item->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold rounded-lg transition">
                                <i class="ri-edit-line mr-1"></i> Edit & Alur
                            </a>
                            <form action="{{ route('admin.jenis.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis izin ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-xs font-semibold rounded-lg transition">
                                    <i class="ri-delete-bin-line mr-1"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-400">Belum ada data jenis izin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
