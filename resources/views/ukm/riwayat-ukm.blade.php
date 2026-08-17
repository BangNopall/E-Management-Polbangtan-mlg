@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Riwayat Presensi UKM</h1>
        <div class="text-gray-600 text-sm">Daftar riwayat kehadiran kegiatan UKM Anda di Polbangtan Malang</div>
        <div class="border-b border-gray-300 my-5"></div>

        <div class="bg-white rounded-lg p-4 border-2">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3">No</th>
                            <th scope="col" class="px-4 py-3">Nama UKM</th>
                            <th scope="col" class="px-4 py-3">Judul Kegiatan</th>
                            <th scope="col" class="px-4 py-3">Tanggal</th>
                            <th scope="col" class="px-4 py-3">Jam Scan</th>
                            <th scope="col" class="px-4 py-3">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($presensis as $index => $presensi)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $presensi->jadwal->ukm->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $presensi->jadwal->judul ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $presensi->jadwal->tanggal ? \Carbon\Carbon::parse($presensi->jadwal->tanggal)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $presensi->jam_kehadiran ? \Carbon\Carbon::parse($presensi->jam_kehadiran)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($presensi->status_kehadiran === 'Hadir')
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Hadir</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada riwayat presensi UKM yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $presensis->links() }}
            </div>
        </div>
    </div>
@endsection
