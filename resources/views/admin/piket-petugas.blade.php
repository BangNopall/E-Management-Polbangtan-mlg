@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Piket Petugas</h1>
        <div class="text-gray-600 text-sm">Jadwal piket petugas Management Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        {{-- alert --}}
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
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="">
                    <div class="text-gray-900 text-lg font-medium">Jadwal Absen Keluar</div>
                    <div class="text-gray-600 text-sm mt-1 pr-2">Berikut pengaturan jadwal piket absen keluar Asrama
                        Polbangtan-mlg
                    </div>
                </div>
                <div class="w-full md:w-[250px] mt-2">
                    <form action="{{ route('admin.piketPetugas') }}" method="get" class="flex w-full">
                        @csrf
                        <input type="date" name="search"
                            class="bg-gray-50 border-2 border-gray-300 text-gray-900 text-sm rounded focus:ring-utama focus:border-utama w-full p-2 mr-1">
                        <button type="submit"
                            class="bg-utama hover:bg-teal-800 text-white rounded px-3 py-1.5 text-sm">Cari</button>
                    </form>
                </div>
            </div>
            <div class="flex flex-col md:flex-row mb-3 gap-1 md:gap-2 mt-1 md:mt-1">
                @if (Auth::check() && Auth::user()->role_id == 1)
                    <a href="{{ route('admin.piketPetugasGenerateJadwalMingguan') }}"
                        class="bg-utama hover:bg-teal-800 text-white rounded py-2 px-3 text-center text-sm items-center justify-center flex">
                        <i class="ri-add-line text-md mr-1"></i>Buat Jadwal Mingguan</a>
                @endif
                @if (Auth::check() && (Auth::user()->role_id == 1 || Auth::user()->role_id == 2))
                    <button id="createJadwalButton"
                        class="bg-utama hover:bg-teal-800 text-white rounded py-2 px-3 text-sm text-center flex justify-center items-center">
                        <i class="ri-add-line text-md mr-1"></i>Buat Jadwal Harian
                    </button>
                @endif
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase  bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Petugas
                            </th>
                            @if (Auth::check() && Auth::user()->role_id == 1)
                                <th scope="col" class="px-6 py-3">
                                    Aksi
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if ($petugas->count() == 0)
                            <tr class="bg-white border-b">
                                <td colspan="4" class="px-6 py-4 text-center">Tidak ada data</td>
                            </tr>
                        @else
                            @foreach ($petugas as $item)
                                <tr class="bg-white border-b">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $loop->iteration }}
                                    </th>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($item->date)->format('d F Y') }}
                                    </td>
                                    <td class="px-6 py-4 min-w-[200px]">
                                        <span
                                            class="my-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2">
                                            {{ $item->petugas1 ? $item->petugas1->name : '-' }}
                                        </span>
                                        <span
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2">
                                            {{ $item->petugas2 ? $item->petugas2->name : '-' }}
                                        </span>

                                    </td>
                                    @if (Auth::check() && Auth::user()->role_id == 1)
                                        <td class="px-6 py-4">
                                            <div class="flex gap-1">
                                                <a href="{{ route('admin.showPiketPetugasSingle', ['id' => $item->id]) }}"
                                                    class="bg-orange-500 hover:bg-orange-800 text-white rounded p-2 edit-button">Edit</a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                    Showing <span class="font-semibold text-gray-900">{{ $petugas->firstItem() }}</span>
                    to <span class="font-semibold text-gray-900">{{ $petugas->lastItem() }}</span>
                    of <span class="font-semibold text-gray-900">{{ $petugas->total() }}</span>
                </span>
                <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                    <li>
                        @if ($petugas->onFirstPage())
                            <span
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                        @else
                            <a href="{{ $petugas->previousPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                        @endif
                    </li>
                    @foreach ($petugas->getUrlRange($petugas->currentPage() - 1, $petugas->currentPage() + 1) as $num => $url)
                        <li>
                            <a href="{{ $url }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $petugas->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                        </li>
                    @endforeach
                    <li>
                        @if ($petugas->hasMorePages())
                            <a href="{{ $petugas->nextPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                        @else
                            <span
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-300 bg-white border border-gray-300 rounded-e-lg">Next</span>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    @include('admin.jadwal-custom')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createJadwalButton = document.getElementById('createJadwalButton');
            const closeJadwalButton = document.getElementById('closeJadwalButton');
            const createJadwalModal = document.getElementById('createJadwalModal');

            createJadwalButton.addEventListener('click', function() {
                createJadwalModal.classList.remove('hidden');
            });

            closeJadwalButton.addEventListener('click', function() {
                createJadwalModal.classList.add('hidden');
            });
        });
    </script>
@endsection
