@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="flex items-center gap-2 mb-3">
            <a href="{{ route('admin.ukm.show', $ukm->id) }}" class="text-teal-700 hover:underline flex items-center text-sm">
                <i class="ri-arrow-left-line"></i> Kembali ke Detail UKM {{ $ukm->nama }}
            </a>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-3">
            <div>
                <h1 class="font-semibold text-2xl md:text-3xl">Verifikasi Kegiatan UKM — {{ $ukm->nama }}</h1>
                <div class="text-gray-600 text-sm mt-1">Daftar jadwal kegiatan yang membutuhkan verifikasi & persetujuan Pembina</div>
            </div>
            <div>
                <form action="{{ route('admin.ukm.laporan.pdf', $ukm->id) }}" method="post" target="_blank" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-2 rounded font-medium flex items-center gap-1">
                        <i class="ri-file-pdf-line"></i> Cetak Laporan PDF
                    </button>
                </form>
            </div>
        </div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        <div class="w-full bg-white border-2 rounded-lg p-4">
            <h2 class="font-semibold text-lg text-gray-800 mb-4">Daftar Jadwal & Status Verifikasi</h2>

            @if ($jadwals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3">No</th>
                                <th scope="col" class="px-4 py-3">Judul Kegiatan</th>
                                <th scope="col" class="px-4 py-3">Tanggal & Waktu</th>
                                <th scope="col" class="px-4 py-3">Lokasi</th>
                                <th scope="col" class="px-4 py-3">Status Saat Ini</th>
                                <th scope="col" class="px-4 py-3">Verifikator</th>
                                <th scope="col" class="px-4 py-3">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jadwals as $jadwal)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        <div>{{ $jadwal->judul }}</div>
                                        <div class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $jadwal->jenis) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div>{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-400">{{ $jadwal->mulai_acara }} - {{ $jadwal->selesai_acara }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $jadwal->lokasi ?? '-' }}</td>
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
                                    <td class="px-4 py-3">
                                        @if ($jadwal->verifier)
                                            <div>{{ $jadwal->verifier->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $jadwal->verified_at ? \Carbon\Carbon::parse($jadwal->verified_at)->format('d M Y H:i') : '-' }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Belum diverifikasi</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('admin.ukm.verifikasi.update', $jadwal->id) }}" method="post" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="catatan_pembina" placeholder="Catatan pembina..."
                                                value="{{ $jadwal->catatan_pembina }}"
                                                class="w-full text-xs p-1.5 border border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                            <div class="flex items-center gap-1">
                                                <button type="submit" name="status_verifikasi" value="disetujui"
                                                    class="bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-2.5 py-1 rounded flex items-center gap-0.5">
                                                    <i class="ri-check-line"></i> Setujui
                                                </button>
                                                <button type="submit" name="status_verifikasi" value="ditolak"
                                                    class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-2.5 py-1 rounded flex items-center gap-0.5">
                                                    <i class="ri-close-line"></i> Tolak
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-6 text-center text-gray-500">
                    Belum ada kegiatan yang perlu diverifikasi untuk UKM ini.
                </div>
            @endif
        </div>
    </div>
@endsection
