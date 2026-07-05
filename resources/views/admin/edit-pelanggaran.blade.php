@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Edit Pelanggaran</h1>
        <div class="text-gray-600 text-sm">Daftar seluruh data pelanggaran Asrama Polbangtan-mlg editable</div>
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
        <div class="bg-white rounded-lg p-3 mt-5 border-2">
            <div class="text-gray-500 text-sm">Editable</div>
            <form action="{{ route('admin.editKategoriStore') }}" method="post">
                @csrf
                <div class="flex flex-col md:flex-row justify-center items-center">
                    <h1 class="font-semibold text-center md:text-left my-2">Kategori Pelanggaran</h1>
                    @include('partials.modals.simpanedit1')
                    <div class="ml-0 md:ml-auto w-full md:w-auto md:mt-0">
                        <button type="button" id="deleteButton" data-modal-target="simpan1Modal"
                            data-modal-toggle="simpan1Modal"
                            class="text-white w-auto hidden md:block bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-3 py-2 mr-0 md:mr-3">Simpan</button>
                    </div>
                </div>
                <ul class="flex flex-col gap-2 mt-2">
                    @foreach ($kategoriPelanggaran as $kategori)
                        @include('partials.modals.hapusdata')
                        <div class="flex gap-1">
                            <input type="text" id="{{ $kategori->id }}" name="{{ $kategori->id }}"
                                value="{{ $kategori->name }}"
                                class="block w-full p-2 text-gray-900 border border-l-4 border-utama rounded-sm bg-gray-50 text-sm focus:ring-red-400 focus:border-red-400">
                            <button type="button"
                                class="text-white text-xs w-auto bg-red-400 hover:bg-red-800 focus:ring-2 focus:outline-none focus:ring-red-300 font-medium rounded px-3 py-2 mr-0 md:mr-3"
                                id="deleteButton" data-modal-target="hapusdataModal{{ $kategori->id }}"
                                data-modal-toggle="hapusdataModal{{ $kategori->id }}"><i
                                    class="ri-delete-bin-line"></i></button>
                        </div>
                    @endforeach
                </ul>
            </form>
            <button type="button" id="deleteButton" data-modal-target="simpan1Modal" data-modal-toggle="simpan1Modal"
                class="text-white mt-2 w-auto block md:hidden bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-3 py-2 mr-0 md:mr-3">Simpan</button>
            <div id="accordion-flush" class="mt-3" data-accordion="collapse" data-active-classes="bg-white text-gray-900"
                data-inactive-classes="text-gray-500">
                <h2 id="accordion-flush-heading-1">
                    <button type="button"
                        class="flex items-center font-normal justify-between gap-5 py-2 text-gray-500 border-b border-gray-200"
                        data-accordion-target="#accordion-flush-body-1" aria-expanded="false"
                        aria-controls="accordion-flush-body-1">
                        <span>Kategori Baru?</span>
                        <i data-accordion-icon aria-hidden="true" class="ri-add-box-line text-md shrink-0"></i>
                    </button>
                </h2>
                <div id="accordion-flush-body-1" class="hidden" aria-labelledby="accordion-flush-heading-1">
                    <div class="mt-2">
                        <form action="{{ route('admin.createKategori') }}" method="post">
                            @csrf
                            <label for="nameKategori" class="block mb-2 text-sm font-medium text-gray-900">Kategori
                                Pelanggaran</label>
                            <div class="flex flex-col sm:flex-row gap-1">
                                <input type="text" name="nameKategori" id="nameKategori"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama w-full sm:w-[300px] block p-2"
                                    placeholder="Tulis kategori pelanggaran baru" @required(true)>
                                <button type="submit"
                                    class="text-white bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 rounded-lg text-sm px-4 py-2">Buat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-5 block">
            <button id="dropdownDividerButton" data-dropdown-toggle="dropdownDivider"
                class="text-white bg-utama hover:bg-teal-800 focus:bg-teal-800 font-medium rounded-lg text-sm px-3 py-2 text-center inline-flex items-center w-full sm:w-auto justify-center"
                type="button">Kategori Pelanggaran <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 4 4 4-4" />
                </svg>
            </button>
            <!-- Dropdown menu -->
            <div id="dropdownDivider" class="z-10 hidden bg-utama divide-gray-100 rounded-lg shadow w-[95%] sm:w-[230px]">
                <ul class="rounded-lg text-sm text-white text-center" aria-labelledby="dropdownDividerButton">
                    @foreach ($kategoriPelanggaran as $kategori)
                        <li>
                            <a href="{{ route('admin.editPelanggaranIdKategori', $kategori->id) }}"
                                class="block py-2 px-2 rounded-lg hover:bg-teal-800">{{ $kategori->AZ }}.
                                {{ $kategori->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="bg-white rounded-lg p-3 mt-2 border-2">
            <div class="text-gray-500 text-sm">Editable</div>
            <h1 class="font-semibold text-center md:text-left my-2">Keterangan Pelanggaran</h1>
            <form action="{{ route('admin.editPelanggaranStore', $id_kategori) }}" method="POST">
                @csrf
                <div class="flex flex-col md:flex-row justify-center item-center">
                    <div class="text-gray-900 text-md text-center md:text-left font-medium my-auto">{{ $name_form->name }}</div>
                    @include('partials.modals.simpanedit2')
                    <div class="ml-0 md:ml-auto w-full md:w-auto md:mt-0">
                        <button type="button" id="deleteButton" data-modal-target="simpan2Modal"
                            data-modal-toggle="simpan2Modal"
                            class="text-white w-auto hidden md:block bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-3 py-2 mr-0 md:mr-3">Simpan</button>
                    </div>
                </div>
                <div class="relative overflow-x-auto rounded mt-2">
                    <table class="w-full text-sm text-left text-gray-500">
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
                                <th scope="col" class="px-3 py-2">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jenisPelanggaran as $pelanggaran)
                                <tr class="bg-orange-50 border-b">
                                    <td class="px-3 py-2 w-full">
                                        <textarea name="jenis_pelanggaran_{{ $pelanggaran->id }}" id="jenis_pelanggaran_{{ $pelanggaran->id }}"
                                            cols="10" rows="2"
                                            class="block w-full min-w-[580px] p-2 text-gray-900 border border-l-4 border-utama rounded-sm bg-gray-50 text-sm focus:ring-red-400 focus:border-red-400"
                                            required>{{ $pelanggaran->jenis_pelanggaran }}</textarea>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" id="poin_{{ $pelanggaran->id }}"
                                            name="poin_{{ $pelanggaran->id }}" value="{{ $pelanggaran->poin }}"
                                            class="block w-12 p-2 text-gray-900 border border-l-4 border-utama rounded-sm bg-gray-50 text-sm focus:ring-red-400 focus:border-red-400"
                                            required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" id="sub_kategori_{{ $pelanggaran->id }}"
                                            name="sub_kategori_{{ $pelanggaran->id }}"
                                            value="{{ $pelanggaran->sub_kategori }}"
                                            class="block p-2 w-20 text-gray-900 border border-l-4 border-utama rounded-sm bg-gray-50 text-sm focus:ring-red-400 focus:border-red-400"
                                            required>
                                    </td>
                                    <td class="px-3 py-2">
                                        @include('partials.modals.hapusjenis')
                                        <button type="button"
                                            class="text-white text-xs w-auto bg-red-400 hover:bg-red-800 focus:ring-2 focus:outline-none focus:ring-red-300 font-medium rounded px-3 py-2 mr-0 md:mr-3"
                                            id="deleteButton" data-modal-target="hapusjenisModal{{ $pelanggaran->id }}"
                                            data-modal-toggle="hapusjenisModal{{ $pelanggaran->id }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" id="deleteButton" data-modal-target="simpan2Modal"
                    data-modal-toggle="simpan2Modal"
                    class="text-white mt-4 w-auto block md:hidden bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-3 py-2 mr-0 md:mr-3">Simpan</button>
            </form>
            <div id="accordion-flush" class="mt-3" data-accordion="collapse"
                data-active-classes="bg-white text-gray-900" data-inactive-classes="text-gray-500">
                <h2 id="accordion-flush-heading-2">
                    <button type="button"
                        class="flex items-cente font-normal justify-between gap-5 py-2 text-gray-500 border-b border-gray-200"
                        data-accordion-target="#accordion-flush-body-2" aria-expanded="false"
                        aria-controls="accordion-flush-body-2">
                        <span>Peraturan Baru?</span>
                        <i data-accordion-icon aria-hidden="true" class="ri-add-box-line text-md shrink-0"></i>
                    </button>
                </h2>
                <div id="accordion-flush-body-2" class="hidden" aria-labelledby="accordion-flush-heading-2">
                    <div class="mt-2">
                        <form action="{{ route('admin.createJenisPelanggaran', $id_kategori) }}" method="post">
                            @csrf
                            <label for="jenis_pelanggaran" class="block mb-2 text-sm font-medium text-gray-900">Bentuk
                                Pelanggaran</label>
                            <div class="flex flex-col sm:flex-row gap-1">
                                <input type="text" name="jenis_pelanggaran" id="jenis_pelanggaran"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama w-full sm:w-[300px] block p-2"
                                    placeholder="Tulis bentuk pelanggaran baru" @required(true)>
                                <button type="submit"
                                    class="text-white bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 rounded-lg text-sm px-4 py-2">Buat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
