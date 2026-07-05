@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Verifikasi Laporan</h1>
        <div class="text-gray-600 text-sm">Verifikasi laporan pelanggaran mahasiswa Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        @if (session()->has('success'))
            <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200" role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ml-3 text-sm font-medium">
                    {{ session('success') }}
                </div>
                <button type="button"
                    class="ml-auto -mx-1.5 -my-1.5 bg-green-200 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8"
                    data-dismiss-target="#alert-3" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif
        @if (session()->has('error'))
            <div id="alert-2" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-200" role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ml-3 text-sm font-medium">
                    {{ session('error') }}
                </div>
                <button type="button"
                    class="ml-auto -mx-1.5 -my-1.5 bg-red-200 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8"
                    data-dismiss-target="#alert-2" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif
        <div class="flex flex-col xl:flex-row gap-7 sm:gap-10">
            <div class="w-full">
                <div class="bg-white rounded-lg p-3 border-2">
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-medium">Profil Siswa</div>
                        @if ($data->statusPelanggaran == 'progressing')
                            <div class="text-sm font-medium px-2 md:px-3 py-1 text-center bg-yellow-500 rounded text-white">
                                Progressing
                            </div>
                        @elseif($data->statusPelanggaran == 'submitted')
                            <div
                                class="text-sm font-medium px-2 md:px-3 py-1 text-center bg-gray-300 rounded text-gray-700">
                                Submitted
                            </div>
                        @elseif($data->statusPelanggaran == 'rejected')
                            <div class="text-sm font-medium px-2 md:px-3 py-1 text-center bg-red-700 rounded text-white">
                                Rejected
                            </div>
                        @endif
                        {{-- <div class="text-sm font-medium p-1 w-full text-center bg-green-700 rounded text-white">
                                Selesai
                            </div> --}}
                    </div>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="w-[150px] h-[190px] rounded mx-auto sm:mx-0">
                            @if ($data->user->image)
                                <img src="{{ asset('storage/images/' . $data->user->image) }}"
                                    class="h-full w-full object-cover rounded" id="fotoProfil" alt="Foto Profil">
                            @else
                                <img src="https://placehold.co/8000x8000" class="h-full w-full object-cover rounded"
                                    id="fotoProfil" alt="Foto Profil">
                            @endif
                        </div>
                        <div class="text-gray-800">
                            <table>
                                <tbody>
                                    <tr>
                                        <td width="150">NIM</td>
                                        <td>:</td>
                                        <td>{{ $data->user->nim }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Nama</td>
                                        <td>:</td>
                                        <td>{{ $data->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Program Studi</td>
                                        <td>:</td>
                                        <td>{{ $data->user->prodi->prodi }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Blok Ruangan</td>
                                        <td>:</td>
                                        <td>{{ $data->blok }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Nomor Ruangan</td>
                                        <td>:</td>
                                        <td>{{ $data->user->no_kamar }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Kelas</td>
                                        <td>:</td>
                                        <td>{{ $data->user->kelas->nama_kelas }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Asal Daerah</td>
                                        <td>:</td>
                                        <td>{{ $data->user->asal_daerah }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-3 border-2 mt-3">
                    <div class="text-lg font-medium">Pelanggaran</div>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="text-gray-900 text-sm font-medium mt-2">{{ $data->AZ }}.
                        {{ $data->kategori_pelanggaran }}</div>
                    <div class="relative overflow-x-auto rounded mt-2">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-orange-100">
                                <tr>
                                    <th scope="col" class="px-3 py-2">
                                        Bentuk Pelanggaran
                                    </th>
                                    <th scope="col" class="px-3 py-2">
                                        Poin
                                    </th>
                                    <th scope="col" class="px-3 py-2">
                                        Kategori
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-orange-50 border-b">
                                    <td class="px-3 py-2 min-w-[580px]">
                                        {{ $data->jenis_pelanggaran }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $data->poin }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $data->sub_kategori }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <form action="{{ route('admin.laporanPelanggaranConfirm', $data->id) }}" method="post">
                    @csrf
                    <div class="bg-white rounded-lg p-3 border-2 mt-3">
                        <div class="text-lg font-medium">Hukuman / Sanksi</div>
                        <div class="border-b border-gray-300 my-1"></div>
                        @if ($data->statusPelanggaran == 'rejected')
                            <div
                                class="block px-2.5 pt-2.5 pb-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 mt-2 cursor-not-allowed">
                                Tidak ada</div>
                        @elseif($data->statusPelanggaran == 'progressing')
                            <div
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 mt-2 cursor-not-allowed">
                                {{ $data->Hukuman }}</div>
                        @else
                            <textarea id="hukuman" name="hukuman" rows="4"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-utama focus:border-utama mt-2"
                                placeholder="Tuliskan hukuman siswa di sini...">{{ $data->Hukuman }}</textarea>
                        @endif
                    </div>
                    <button type="button" onclick="window.location.href='/laporan-pelanggaran'"
                        class="mt-2 focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-2 focus:ring-red-300 font-medium rounded-lg text-sm px-2.5 py-1.5 me-2">Kembali</button>
            </div>
            <div class="rounded w-full sm:w-[320px]">

                @if ($data->statusPelanggaran == 'progressing')
                    @include('partials.modals.selesai')
                    <button type="button" id="deleteButton" data-modal-target="selesaiModal"
                        data-modal-toggle="selesaiModal"
                        class="bg-green-400 rounded px-3 py-3 text-black w-full hover:bg-green-500 text-sm">Selesai</button>
                    <p class="ms-auto text-xs text-gray-500 mt-4">Pastikan telah memeriksa ulang dan menetapkan keputusan
                        yang sesuai.</p>
                @endif
                @if ($data->statusPelanggaran == 'submitted')
                    @include('partials.modals.hukum')
                    <button type="button" id="deleteButton" data-modal-target="hukumModal"
                        data-modal-toggle="hukumModal"
                        class="bg-yellow-300 rounded px-3 py-3 text-yellow-950 w-full hover:bg-yellow-500 text-sm">Hukum</button>
                    </form>
                    <form id="rejectionForm" action="{{ route('admin.laporanPelanggaranRejected', $data->id) }}"
                        method="post">
                        @csrf
                        <div class="w-full mb-4 border border-gray-200 rounded-lg bg-gray-50 mt-2">
                            <div class="p-2 bg-white rounded-t-lg">
                                <label for="rejected_message" class="sr-only text-xs">Keterangan Tolak</label>
                                <textarea id="rejected_message" name="rejected_message" rows="4"
                                    class="w-full px-0 text-gray-900 text-xs bg-white border-0 focus:ring-0" placeholder="Keterangan penolakan"></textarea>
                            </div>
                            @include('partials.modals.tolak')
                            <div class="flex items-center justify-between border-t">
                                <button type="button" id="deleteButton" data-modal-target="tolakModal"
                                    data-modal-toggle="tolakModal"
                                    class="inline-flex items-center justify-center py-2.5 px-4 text-xs font-medium text-center text-white hover:bg-red-500 bg-red-400 w-full">
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="ms-auto text-xs text-gray-500">Pastikan telah memeriksa ulang dan menetapkan keputusan
                        yang sesuai.</p>
                @endif
                @if ($data->statusPelanggaran == 'rejected')
                    <div class="w-full border border-gray-200 rounded-lg bg-gray-50 mt-2">
                        <div class="p-2 bg-white rounded-t-lg">
                            <label for="rejected_message" class="sr-only text-xs">Keterangan tolak</label>
                            <div class="w-full px-0 text-gray-900 text-xs bg-white border-0 focus:ring-0">
                                <div class="text-sm underline mb-1">Keterangan Tolak</div>
                                {{ $data->rejected_message }}
                            </div>
                        </div>
                    </div>
                    @include('partials.modals.hapus')
                    <button type="button" id="deleteButton" data-modal-target="hapusModal"
                        data-modal-toggle="hapusModal"
                        class="border-2 border-red-400 rounded px-3 py-3 text-gray-400 w-full hover:bg-red-400 mt-2 text-sm hover:text-white">Hapus</button>
                @endif
            </div>
        </div>
    </div>
@endsection
