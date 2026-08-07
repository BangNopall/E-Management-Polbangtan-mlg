@extends('layouts.main')

@section('container')
<div class="px-4 pt-6">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="ri-folder-user-line text-teal-700 mr-2"></i> Data Seluruh Perizinan Asrama
            </h1>
            <p class="text-sm text-gray-500">Rekapitulasi riwayat pengajuan izin seluruh mahasiswa beserta status pengesahan.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.izin.data.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold rounded-lg shadow-2xs transition">
                <i class="ri-file-pdf-line mr-1 text-base"></i> Export PDF
            </a>
            <a href="{{ route('admin.izin.data.excel', request()->query()) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-2xs transition">
                <i class="ri-file-excel-line mr-1 text-base"></i> Export Excel
            </a>
        </div>
    </div>

    @include('partials.alert')

    <!-- Filter Bar -->
    <div class="bg-white border-2 border-gray-100 rounded-xl p-4 mb-6 shadow-xs">
        <form action="{{ route('admin.izin.data.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block font-bold text-gray-700 mb-1">Status Perizinan</label>
                <select name="status" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2 text-xs">
                    <option value="">-- Semua Status --</option>
                    <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu Approver</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="berjalan" {{ request('status') == 'berjalan' ? 'selected' : '' }}>Berjalan / Di Luar</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="kadaluarsa" {{ request('status') == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Program Studi</label>
                <select name="prodi_id" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2 text-xs">
                    <option value="">-- Semua Prodi --</option>
                    @foreach ($prodis as $p)
                        <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->prodi }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Tanggal Berangkat Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2 text-xs">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Tanggal Berangkat Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2 text-xs">
            </div>

            <div class="flex items-end space-x-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/surat..." class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2 text-xs">
                <button type="submit" class="px-3 py-2 bg-teal-700 text-white rounded-lg font-bold text-xs">
                    <i class="ri-search-line"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border-2 border-gray-100 rounded-xl shadow-xs overflow-hidden">
        <table class="w-full text-left text-xs text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-[11px] border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3">Mahasiswa Pemohon</th>
                    <th class="px-4 py-3">Jenis Izin</th>
                    <th class="px-4 py-3">Nomor Surat / Tujuan</th>
                    <th class="px-4 py-3">Jadwal Perizinan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($izins as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-900 block text-sm">{{ $item->nama_snapshot }}</span>
                            <span class="text-gray-500 text-[11px]">{{ $item->nirm_snapshot ?? '-' }} • {{ $item->kelas_snapshot }}</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-teal-800">
                            {{ optional($item->jenisIzin)->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-gray-700 font-bold block">{{ $item->nomor_surat ?? '-' }}</span>
                            <span class="text-gray-500 text-[11px] block truncate max-w-xs">{{ $item->tujuan_lokasi }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-teal-800 font-bold block">{{ optional($item->waktu_berangkat)->format('d/m/Y H:i') }}</span>
                            <span class="text-amber-800 font-semibold block text-[11px]">s/d {{ optional($item->waktu_kembali)->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
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
                                $style = $badgeStyle[$item->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border {{ $style }}">
                                {{ strtoupper($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.izin.data.show', $item->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-semibold text-xs rounded-lg transition shadow-2xs">
                                <i class="ri-search-eye-line mr-1"></i> Detail & Audit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-400">Tidak ada data perizinan yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $izins->links() }}
    </div>
</div>
@endsection
