@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="flex items-center gap-2 mb-3">
            <a href="{{ route('admin.ukm.index') }}" class="text-teal-700 hover:underline flex items-center text-sm">
                <i class="ri-arrow-left-line"></i> Kembali ke Manajemen UKM
            </a>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-3">
            <div>
                <h1 class="font-semibold text-2xl md:text-3xl">{{ $ukm->nama }}</h1>
                <div class="text-gray-600 text-sm mt-1">{{ $ukm->deskripsi ?? 'Tidak ada deskripsi' }}</div>
            </div>
            <div>
                @if ($ukm->is_active)
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded">Status: Aktif</span>
                @else
                    <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded">Status: Nonaktif</span>
                @endif
            </div>
        </div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        {{-- Tabs --}}
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="ukmTab" data-tabs-toggle="#ukmTabContent" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="anggota-tab" data-tabs-target="#anggota" type="button" role="tab" aria-controls="anggota" aria-selected="true">
                        <i class="ri-user-line mr-1"></i> Anggota ({{ $ukm->members->count() }})
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="jadwal-tab" data-tabs-target="#jadwal" type="button" role="tab" aria-controls="jadwal" aria-selected="false">
                        <i class="ri-calendar-event-line mr-1"></i> Jadwal ({{ $ukm->jadwals->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <div id="ukmTabContent">
            {{-- Tab 1: Anggota --}}
            <div class="p-4 rounded-lg bg-white border-2" id="anggota" role="tabpanel" aria-labelledby="anggota-tab">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold text-lg text-gray-800">Daftar Anggota UKM</h2>
                    <button data-modal-target="modal-tambah-anggota" data-modal-toggle="modal-tambah-anggota"
                        class="bg-utama text-white text-xs px-3 py-2 rounded hover:bg-teal-700 font-medium flex items-center gap-1">
                        <i class="ri-user-add-line"></i> Tambah Anggota / Staf
                    </button>
                </div>

                @include('admin.ukm.partials.anggota_table')
            </div>

            {{-- Tab 2: Jadwal --}}
            <div class="hidden p-4 rounded-lg bg-white border-2" id="jadwal" role="tabpanel" aria-labelledby="jadwal-tab">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold text-lg text-gray-800">Daftar Jadwal Kegiatan UKM</h2>
                </div>

                @if ($ukm->jadwals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">No</th>
                                    <th scope="col" class="px-4 py-3">Judul</th>
                                    <th scope="col" class="px-4 py-3">Jenis</th>
                                    <th scope="col" class="px-4 py-3">Tanggal</th>
                                    <th scope="col" class="px-4 py-3">Waktu</th>
                                    <th scope="col" class="px-4 py-3">Status Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ukm->jadwals as $j)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $j->judul }}</td>
                                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $j->jenis) }}</td>
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($j->tanggal)->format('d M Y') }}</td>
                                        <td class="px-4 py-3">{{ $j->mulai_acara }} - {{ $j->selesai_acara }}</td>
                                        <td class="px-4 py-3">
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded capitalize">
                                                {{ $j->status_verifikasi }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-gray-500">
                        Belum ada jadwal kegiatan yang dibuat untuk UKM ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('partials.modals.ukm-tambah-anggota')
@endsection
