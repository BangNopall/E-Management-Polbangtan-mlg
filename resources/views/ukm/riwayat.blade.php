@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-1">Riwayat Presensi UKM</h1>
        <div class="text-gray-600 text-sm">Catatan kehadiran Anda dalam kegiatan Unit Kegiatan Mahasiswa</div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        <div class="w-full bg-white border-2 rounded-lg p-4">
            @if ($presensis->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3">No</th>
                                <th scope="col" class="px-4 py-3">UKM</th>
                                <th scope="col" class="px-4 py-3">Judul Kegiatan</th>
                                <th scope="col" class="px-4 py-3">Tanggal & Waktu</th>
                                <th scope="col" class="px-4 py-3">Jam Scan</th>
                                <th scope="col" class="px-4 py-3">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($presensis as $presensi)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $presensi->jadwal?->ukm?->nama ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>{{ $presensi->jadwal?->judul ?? '-' }}</div>
                                        <div class="text-xs text-gray-400 capitalize">{{ $presensi->jadwal ? str_replace('_', ' ', $presensi->jadwal->jenis) : '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div>{{ $presensi->jadwal?->tanggal ? \Carbon\Carbon::parse($presensi->jadwal->tanggal)->format('d M Y') : '-' }}</div>
                                        <div class="text-xs text-gray-400">{{ $presensi->jadwal?->mulai_acara ?? '-' }} - {{ $presensi->jadwal?->selesai_acara ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $presensi->jam_kehadiran ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($presensi->status_kehadiran === 'Hadir')
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Hadir</span>
                                        @elseif ($presensi->status_kehadiran === 'Izin')
                                            <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded">Izin</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Alpha</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $presensis->links() }}
                </div>
            @else
                <div class="py-6 text-center text-gray-500">
                    Belum ada data riwayat presensi UKM.
                </div>
            @endif
        </div>
    </div>
@endsection
