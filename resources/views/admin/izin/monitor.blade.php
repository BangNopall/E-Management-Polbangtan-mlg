@extends('layouts.main')

@section('container')
<div class="px-3 pt-4 sm:px-6 sm:pt-6">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                <i class="ri-dashboard-3-line text-teal-700 mr-2"></i> Monitor Asrama & Keberadaan Mahasiswa
            </h1>
            <p class="text-xs sm:text-sm text-gray-500">
                Pengawasan real-time mahasiswa yang sedang berizin di luar asrama. (Auto-refresh setiap 60 detik: <span id="last-updated-time" class="font-mono font-bold text-teal-800">{{ date('H:i:s') }}</span>)
            </p>
        </div>
    </div>

    @include('partials.alert')

    <!-- 3 Kartu Statistik Ringkas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Kartu 1: Sedang Berjalan / Di Luar -->
        <div class="bg-white border-2 border-sky-100 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-sky-700 uppercase tracking-wider block">Di Luar / Berjalan</span>
                    <h3 id="stat-berjalan" class="text-2xl font-extrabold text-sky-900 mt-1">{{ $stats['sedang_berjalan'] }}</h3>
                </div>
                <div class="w-11 h-11 bg-sky-50 text-sky-600 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="ri-walk-line"></i>
                </div>
            </div>
            <p class="text-[11px] text-gray-500 mt-2">Mahasiswa yang saat ini berada di luar asrama dengan izin sah.</p>
        </div>

        <!-- Kartu 2: Terlambat Kembali -->
        <div class="bg-white border-2 border-rose-100 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wider block">Terlambat Kembali</span>
                    <h3 id="stat-terlambat" class="text-2xl font-extrabold text-rose-900 mt-1">{{ $stats['terlambat'] }}</h3>
                </div>
                <div class="w-11 h-11 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="ri-time-line"></i>
                </div>
            </div>
            <p class="text-[11px] text-gray-500 mt-2">Mahasiswa yang melewati tenggat kembali & telah tercatat pelanggaran.</p>
        </div>

        <!-- Kartu 3: Izin Disetujui Mendatang -->
        <div class="bg-white border-2 border-emerald-100 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block">Izin Mendatang</span>
                    <h3 id="stat-mendatang" class="text-2xl font-extrabold text-emerald-900 mt-1">{{ $stats['mendatang'] }}</h3>
                </div>
                <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-xl shadow-inner">
                    <i class="ri-calendar-check-line"></i>
                </div>
            </div>
            <p class="text-[11px] text-gray-500 mt-2">Izin resmi yang telah disetujui untuk jadwal akan datang.</p>
        </div>
    </div>

    <!-- Tabel Daftar Pengawasan Mahasiswa -->
    <div class="bg-white border-2 border-gray-100 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="font-bold text-gray-900 text-sm flex items-center">
                <i class="ri-user-search-line text-teal-700 mr-2"></i> Daftar Pengajuan Aktif
            </h3>

            <div class="flex items-center space-x-2">
                <form action="{{ route('admin.izin.monitor') }}" method="GET" class="flex items-center space-x-2">
                    <select name="status" class="text-xs bg-gray-50 border border-gray-300 rounded-lg p-2" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="berjalan" {{ request('status') == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                        <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/tujuan..." class="text-xs bg-gray-50 border border-gray-300 rounded-lg p-2">
                    <button type="submit" class="px-3 py-2 bg-teal-700 text-white rounded-lg text-xs font-semibold">Cari</button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-[11px] border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-4 py-3">Jenis Izin</th>
                        <th class="px-4 py-3">Tujuan</th>
                        <th class="px-4 py-3">Waktu Berangkat</th>
                        <th class="px-4 py-3">Perkiraan Kembali</th>
                        <th class="px-4 py-3">Konfirmasi Tiba</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="monitor-table-body" class="divide-y divide-gray-100">
                    @foreach ($izins as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-gray-900 block text-sm">{{ $item->nama_snapshot }}</span>
                                <span class="text-gray-500 text-[11px]">{{ $item->nirm_snapshot ?? '-' }} • {{ $item->kelas_snapshot }}</span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-teal-800">
                                {{ optional($item->jenisIzin)->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $item->tujuan_lokasi }}
                            </td>
                            <td class="px-4 py-3 text-teal-800 font-semibold">
                                {{ optional($item->waktu_berangkat)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-amber-800 font-semibold">
                                {{ optional($item->waktu_kembali)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->tiba_at)
                                    <span class="text-emerald-700 font-bold block">✓ {{ $item->tiba_at->format('d/m H:i') }}</span>
                                    <span class="text-gray-400 text-[10px] block">Oleh: {{ $item->tiba_dikonfirmasi_oleh }}</span>
                                @else
                                    <span class="text-gray-400 text-[11px] italic">Belum tiba</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $badgeStyle = [
                                        'diajukan' => 'bg-purple-100 text-purple-800 border-purple-300',
                                        'berjalan' => 'bg-sky-100 text-sky-800 border-sky-300',
                                        'terlambat' => 'bg-rose-100 text-rose-800 border-rose-300',
                                        'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    ];
                                    $style = $badgeStyle[$item->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border {{ $style }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.izin.data.show', $item->id) }}" class="inline-flex items-center px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-[11px] rounded-lg transition">
                                    <i class="ri-search-eye-line mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $izins->links() }}
        </div>
    </div>
</div>

<!-- Script Auto-Refresh 60 Detik via Fetch Biasa (NO Livewire / WebSocket) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function refreshMonitorData() {
            fetch('{{ route("admin.izin.monitor.data") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update Statistik
                    document.getElementById('stat-berjalan').innerText = data.stats.sedang_berjalan;
                    document.getElementById('stat-terlambat').innerText = data.stats.terlambat;
                    document.getElementById('stat-mendatang').innerText = data.stats.mendatang;
                    document.getElementById('last-updated-time').innerText = data.last_updated;
                }
            })
            .catch(err => console.error('Gagal memperbarui data monitor:', err));
        }

        // Auto-refresh setiap 60,000 md (60 detik)
        setInterval(refreshMonitorData, 60000);
    });
</script>
@endsection
