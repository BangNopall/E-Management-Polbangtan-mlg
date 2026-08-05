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
                <th scope="col" class="px-4 py-3">Catatan Pembina</th>
                <th scope="col" class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $index => $jadwal)
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
                    {{-- Isu #5: catatan_pembina selama ini tersimpan tapi tidak pernah
                         dirender — alasan penolakan jadi write-only. Ditampilkan di sini
                         supaya Pelatih/Admin tahu apa yang perlu diperbaiki. --}}
                    <td class="px-4 py-3 max-w-xs">
                        @if ($jadwal->catatan_pembina)
                            <span class="text-gray-700">{{ $jadwal->catatan_pembina }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 flex items-center gap-2">
                        {{-- Isu #4c: tombol Scanner hanya muncul untuk jadwal yang sudah
                             disetujui. Ini pelengkap, bukan pengganti — guard otoritatif
                             ada di UkmScanController karena URL bisa diketik langsung. --}}
                        @if ($jadwal->status_verifikasi === 'disetujui')
                            <a href="{{ route('admin.ukm.scan.show', $jadwal->id) }}"
                                class="text-white bg-teal-600 hover:bg-teal-700 font-medium rounded text-xs px-3 py-1.5 flex items-center gap-1">
                                <i class="ri-qr-scan-2-line"></i> Scanner
                            </a>
                        @endif
                        @if ($jadwal->status_verifikasi === 'draft')
                            <form action="{{ route('admin.ukm.jadwal.ajukanVerifikasi', $jadwal->id) }}" method="post" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="text-white bg-amber-600 hover:bg-amber-700 font-medium rounded text-xs px-3 py-1.5 flex items-center gap-1">
                                    <i class="ri-send-plane-line"></i> Ajukan Verifikasi
                                </button>
                            </form>
                            {{-- Isu #7: hapus jadwal hanya saat masih draft — jadwal yang
                                 sudah diajukan/diverifikasi dipertahankan sebagai jejak audit. --}}
                            <form action="{{ route('admin.ukm.jadwal.destroy', $jadwal->id) }}" method="post" class="inline"
                                onsubmit="return confirm('Hapus jadwal {{ $jadwal->judul }}? Seluruh data presensi terkait akan ikut terhapus dan TIDAK BISA dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-white bg-red-600 hover:bg-red-700 font-medium rounded text-xs px-3 py-1.5 flex items-center gap-1">
                                    <i class="ri-delete-bin-line"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                        Belum ada jadwal kegiatan yang dibuat untuk UKM ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $jadwals->links() }}
    </div>
</div>
