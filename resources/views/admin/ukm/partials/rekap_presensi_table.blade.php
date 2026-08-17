<div class="overflow-x-auto">
    <div class="mb-4">
        <h3 class="font-semibold text-gray-800 text-md">Rekap Kehadiran Anggota per Jadwal Kegiatan</h3>
        <p class="text-xs text-gray-500">Daftar rekapitulasi status presensi (Hadir/Alpha) seluruh anggota untuk jadwal yang telah disetujui.</p>
    </div>

    @forelse ($rekapJadwals as $jadwal)
        <div class="mb-6 border rounded-lg overflow-hidden bg-white shadow-sm">
            <div class="bg-gray-50 p-4 border-b flex flex-col md:flex-row justify-between md:items-center gap-2">
                <div>
                    <h4 class="font-semibold text-gray-900 text-base">{{ $jadwal->judul }}</h4>
                    <div class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-3">
                        <span><i class="ri-calendar-line"></i> {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}</span>
                        <span><i class="ri-time-line"></i> {{ $jadwal->mulai_acara }} - {{ $jadwal->selesai_acara }}</span>
                        <span><i class="ri-map-pin-line"></i> {{ $jadwal->lokasi ?? '-' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        Hadir: {{ $jadwal->presensis->where('status_kehadiran', 'Hadir')->count() }}
                    </span>
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        Alpha: {{ $jadwal->presensis->where('status_kehadiran', 'Alpha')->count() }}
                    </span>
                </div>
            </div>

            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                    <tr>
                        <th scope="col" class="px-4 py-2">No</th>
                        <th scope="col" class="px-4 py-2">Nama Mahasiswa</th>
                        <th scope="col" class="px-4 py-2">NIM</th>
                        <th scope="col" class="px-4 py-2">Status Presensi</th>
                        <th scope="col" class="px-4 py-2">Jam Scan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwal->presensis as $presensi)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $presensi->user->name ?? 'User Tidak Ditemukan' }}</td>
                            <td class="px-4 py-2">{{ $presensi->user->nim ?? '-' }}</td>
                            <td class="px-4 py-2">
                                @if ($presensi->status_kehadiran === 'Hadir')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded">Hadir</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded">Alpha</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $presensi->jam_kehadiran ? \Carbon\Carbon::parse($presensi->jam_kehadiran)->format('H:i:s') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500 text-xs">
                                Belum ada data presensi anggota untuk jadwal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <div class="p-6 text-center text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed">
            Belum ada jadwal berstatus disetujui untuk rekap presensi.
        </div>
    @endforelse

    <div class="mt-4">
        {{ $rekapJadwals->links() }}
    </div>
</div>
