<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3">No</th>
                <th scope="col" class="px-4 py-3">Judul Kegiatan</th>
                <th scope="col" class="px-4 py-3">Jenis</th>
                <th scope="col" class="px-4 py-3">Tanggal</th>
                <th scope="col" class="px-4 py-3">Waktu</th>
                <th scope="col" class="px-4 py-3">Lokasi</th>
                <th scope="col" class="px-4 py-3">Status Verifikasi</th>
                <th scope="col" class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ukm->jadwals as $index => $jadwal)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $jadwal->judul }}
                    </td>
                    <td class="px-4 py-3 capitalize">
                        @if ($jadwal->jenis === 'kegiatan_wajib')
                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">Kegiatan Wajib</span>
                        @else
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Latihan</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $jadwal->mulai_acara }} - {{ $jadwal->selesai_acara }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $jadwal->lokasi ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($jadwal->status_verifikasi === 'disetujui')
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Disetujui</span>
                        @elseif ($jadwal->status_verifikasi === 'menunggu')
                            <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded">Menunggu</span>
                        @elseif ($jadwal->status_verifikasi === 'ditolak')
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Ditolak</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 flex items-center gap-2">
                        <a href="{{ route('admin.ukm.scan.show', $jadwal->id) }}"
                            class="text-white bg-teal-600 hover:bg-teal-700 font-medium rounded text-xs px-3 py-1.5 flex items-center gap-1">
                            <i class="ri-qr-scan-2-line"></i> Scanner
                        </a>
                        @if ($jadwal->status_verifikasi === 'draft')
                            <form action="{{ route('admin.ukm.jadwal.ajukanVerifikasi', $jadwal->id) }}" method="post" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="text-white bg-amber-600 hover:bg-amber-700 font-medium rounded text-xs px-3 py-1.5 flex items-center gap-1">
                                    <i class="ri-send-plane-line"></i> Ajukan Verifikasi
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                        Belum ada jadwal kegiatan yang dibuat untuk UKM ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
