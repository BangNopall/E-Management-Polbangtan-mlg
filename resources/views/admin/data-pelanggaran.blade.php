@extends('layouts.main')
@section('container')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Data Pelanggaran</h1>
        <div class="text-gray-600 text-sm">Daftar seluruh data pelanggaran mahasiswa Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="bg-white rounded-lg p-3 mt-5 border-2">
            {{-- alert --}}
            @if (session()->has('success'))
                <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200" role="alert">
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
            @if ($data->isEmpty())
                <div class="flex flex-col sm:flex-row items-center sm:item-end gap-2">
                    <div class="w-full sm:w-[300px]">
                        {{-- <form action="{{ route('admin.searchDataPelanggaran') }}" method="post"> --}}
                        <form>
                            {{-- @csrf --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input type="search" id="searchInput" name="searchInput"
                                    class="block w-full p-2.5 pl-10 text-xs text-gray-900 border-2 border-gray-300 rounded-lg bg-gray-50 focus:ring-utama focus:border-utama"
                                    placeholder="Cari Nama, Kelas, NIM">
                            </div>
                    </div>
                    <button type="button" id="search-button"
                        class="text-white bg-utama hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm w-full sm:w-auto px-3 py-2">Cari</button>
                    </form>
                </div>
                <div class="relative overflow-x-auto border-2 border-gray-200 sm:rounded-lg mt-2">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr class="">
                                <th scope="col" class="px-3 py-3">
                                    NO
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    NIM
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    NAMA
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    KELAS
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    PELANGGARAN
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <div class="text-center my-5 text-xs w-full">Data pelanggaran siswa belum tersedia.</div>
                </div>
            @else
                <div class="flex flex-col sm:flex-row items-center sm:item-end gap-2">
                    <div class="w-full sm:w-[300px]">
                        {{-- <form action="{{ route('admin.searchDataPelanggaran') }}" method="post"> --}}
                        <form id="search-form" method="post">
                            {{-- @csrf --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input type="search" id="searchInput" name="searchInput"
                                    class="block w-full p-2.5 pl-10 text-xs text-gray-900 border-2 border-gray-300 rounded-lg bg-gray-50 focus:ring-utama focus:border-utama"
                                    placeholder="Cari Nama, Kelas, NIM">
                            </div>
                    </div>
                    <button type="submit" id="search-button"
                        class="text-white bg-utama hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm w-full sm:w-auto px-3 py-2">Cari</button>
                    </form>
                </div>
                <div class="relative overflow-x-auto border-2 border-gray-200 sm:rounded-lg mt-2">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr class="">
                                <th scope="col" class="px-3 py-3">
                                    NO
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    NIM
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    NAMA
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    KELAS
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    PELANGGARAN
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody id="result">
                            <?php $i = 1; ?>
                            @foreach ($data as $item)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-3 py-4">
                                        {{ $i++ }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $item[0]['user']['nim'] }}
                                    </td>
                                    <td class="px-6 py-4 min-w-[180px]">
                                        {{ $item[0]['user']['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $item[0]['user']['kelas']['nama_kelas'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $item[0]['total_pelanggaran'] }}x
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.dataPelanggaranDetail', $item[0]['user']['id']) }} }}"
                                            class="font-medium text-blue-600 hover:underline">Buka</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing
                        <span class="font-semibold text-gray-900">{{ $data->firstItem() }}</span> to <span
                            class="font-semibold text-gray-900">{{ $data->lastItem() }}</span> of <span
                            class="font-semibold text-gray-900">{{ $data->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            <a href="{{ $data->previousPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                        </li>
                        @foreach ($data->getUrlRange($data->currentPage() - 1, $data->currentPage() + 1) as $num => $url)
                            <li>
                                {{-- PAGINATATE ACTIVE URL  ( help ndak roh kelas e ) --}}
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $data->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            <a href="{{ $data->nextPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script type="text/javascript" src="{{ asset('js/searchDataPelanggaran.js') }}"></script>
@endsection
