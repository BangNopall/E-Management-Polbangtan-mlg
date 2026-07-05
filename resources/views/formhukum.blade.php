@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Forum Laporan Pelanggaran</h1>
        <div class="text-gray-600 text-sm">Pembuatan Forum laporan Pelanggaran Mahasiswa Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="mt-2 block lg:hidden">
            <button id="dropdownDividerButton" data-dropdown-toggle="dropdownDivider"
                class="text-white bg-utama hover:bg-teal-800 focus:bg-teal-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center w-full justify-center"
                type="button">Kategori Pelanggaran <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 4 4 4-4" />
                </svg>
            </button>
            <!-- Dropdown menu -->
            <div id="dropdownDivider" class="z-10 hidden bg-utama divide-gray-100 rounded-lg shadow w-[95%] sm:w-64">
                <ul class="py-2 text-sm text-white" aria-labelledby="dropdownDividerButton">
                    @foreach ($kategoriPelanggaran as $kategori)
                        <li>
                            <a href="{{ route('home.formHukumShow', $kategori->id) }}"
                                class="block px-4 py-2 hover:bg-teal-800">{{ $kategori->AZ }}.
                                {{ $kategori->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="bg-white rounded-lg p-3 mt-2 border-2">
            {{-- alert --}}
            @if (session()->has('success'))
                <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200"
                    role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
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
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
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
            {{-- BATAS KEMATIAN FRONT END --}}
            <div class="flex">
                <!-- Content Area -->
                <div class="flex-1 bg-white p-0 md:p-2 w-full">
                    <!-- Your main content goes here -->
                    <form action="{{ route('home.formHukumKategoriSubmit', $user_id ) }}" method="POST">
                        @csrf
                        <div>
                            <h1 class="font-semibold text-lg mb-1">Pilih Pelanggaran</h1>
                            <div class="text-gray-600 text-sm">
                                Pilih keterangan pelanggaran yang sesuai dengan perbuatan yang dilakukan.
                            </div>
                        </div>
                        <div class="border-b border-gray-300 my-3">
                        </div>
                            @foreach ($oneKategoriPelanggaran as $kategori)
                                <section id="{{ $kategori->name }}">
                                    <div class="flex flex-col md:flex-row justify-center items-center">
                                        <div class="text-lg font-semibold my-2">{{ $kategori->name }}</div>
                                        <div class="ml-0 md:ml-auto w-full md:w-auto mt-2 md:mt-0">
                                            <button type="submit"
                                                class="text-white w-full md:w-auto bg-utama hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-0 md:mr-3">Simpan</button>
                                        </div>
                                    </div>
                                    <div class="overflow-y-auto h-[700px] mt-5">
                                        <ul class="flex flex-col gap-2 md:gap-3">
                                            @foreach ($jenisPelanggaranGrouped[$kategori->id] ?? [] as $jenis)
                                                <li>
                                                    <input type="checkbox" id="jenis_pelanggaran_{{ $jenis->id }}"
                                                        name="jenis_pelanggaran[]" value="{{ $jenis->id }}"
                                                        class="hidden peer">
                                                    <label for="jenis_pelanggaran_{{ $jenis->id }}"
                                                        class="inline-flex items-center justify-between w-full p-2 text-gray-800 bg-white border-2 border-gray-300 rounded-lg cursor-pointer peer-checked:border-utama peer-checked:bg-[#498f8e] hover:text-gray-600 peer-checked:text-white hover:bg-gray-50">
                                                        <div class="block">
                                                            <div class="w-full text-sm">{{ $jenis->jenis_pelanggaran }}
                                                            </div>
                                                        </div>
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>
                            @endforeach
                    </form>
                </div>

                <!-- Right Sidebar -->
                <div class="w-64 flex bg-gray-200 p-4 sticky overflow-y-auto hidden lg:block">
                    <!-- Sidebar Content -->
                    <div class="mb-4">
                        <div class="flex flex-col gap-3">
                            @foreach ($kategoriPelanggaran as $kategori)
                                <a href="{{ route('home.formHukumShow', $kategori->id) }}"
                                    class="text-teal-800 hover:text-teal-900 underline">{{ $kategori->AZ }}.
                                    {{ $kategori->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/formhukum.js') }}"></script>
@endsection
