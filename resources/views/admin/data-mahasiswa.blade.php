@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Daftar Mahasiswa</h1>
        <div class="text-gray-600 text-sm">Data mahasiswa Angkatan 2022/2023 Asrama Polbangtan Malang</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="w-full bg-white border-2 rounded-lg p-3">
            <div class="flex flex-col md:flex-row items-center gap-2">
                <div class="items-center w-full md:w-auto">
                    <select id="blok_ruangan" name="blok_ruangan"
                        class="bg-gray-50 border-2 border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5">
                        <option selected hidden>Pilih Blok Ruangan</option>
                        @foreach ($blokRuangan as $blok)
                            <option value="{{ $blok->id }}">{{ $blok->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-auto">
                    <select id="nomor_ruangan" name="nomor_ruangan"
                        class="bg-gray-50 border-2 border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5">
                        <option selected hidden>-</option>
                    </select>
                </div>
                <div class="w-full md:w-[70%]">
                    {{-- <form id="search-form" method="post">
                        @csrf --}}
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="search" id="search-input"
                                class="block w-full p-2.5 pl-10 text-sm text-gray-900 border-2 border-gray-300 rounded-lg bg-gray-50 focus:ring-utama focus:border-utama"
                                placeholder="Cari berdasarkan Nama, No HP, Email, dan NIM" required>
                        </div>
                </div>
                <button type="button" id="search-button"
                    class="text-white bg-utama hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5">Cari</button>
                {{-- </form> --}}
            </div>
        </div>
        <div class="bg-white rounded-lg p-3 mt-5 border-2">
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
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase  bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-[10px]">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3 w-[10px]">
                                NIM
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Nama
                            </th>
                            <th scope="col" class="px-6 py-3">
                                kelas
                            </th>
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                Blok dan Ruang
                            </th>
                            <th scope="col" class="px-6 py-3">
                                No HP
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody id="result">
                        @include('admin.partials.dataMahasiswaTablePartials', ['mahasiswa' => $mahasiswa])
                    </tbody>
                </table>
            </div>
            <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                    Showing <span class="font-semibold text-gray-900">{{ $mahasiswa->firstItem() }}</span>
                    to <span class="font-semibold text-gray-900">{{ $mahasiswa->lastItem() }}</span>
                    of <span class="font-semibold text-gray-900">{{ $mahasiswa->total() }}</span>
                </span>
                <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                    <li>
                        @if ($mahasiswa->onFirstPage())
                            <span
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                        @else
                            <a href="{{ $mahasiswa->previousPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                        @endif
                    </li>
                    @foreach ($mahasiswa->getUrlRange($mahasiswa->currentPage() - 1, $mahasiswa->currentPage() + 1) as $num => $url)
                        <li>
                            <a href="{{ $url }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $mahasiswa->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                        </li>
                    @endforeach
                    <li>
                        @if ($mahasiswa->hasMorePages())
                            <a href="{{ $mahasiswa->nextPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                        @else
                            <span
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-300 bg-white border border-gray-300 rounded-e-lg">Next</span>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
        <div id="data-mahasiswa" data-get-nomor-ruangan="{{ route('admin.getNomorRuangan') }}"
            data-search-mahasiswa-by-blok-ruangan="{{ route('admin.searchMahasiswaByBlokRuangan') }}"
            data-search-mahasiswa="{{ route('admin.searchMahasiswa') }}">
        </div>
        {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    </div>
    <script src="{{ asset('js/library/jquery.min.js') }}" type="text/javascript"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/dataMahasiswa.js') }}"></script>
@endsection
