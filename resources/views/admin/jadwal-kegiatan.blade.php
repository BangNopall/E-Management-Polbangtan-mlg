@extends('layouts.main')
@section('container')
    {{-- end alert --}}
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Jadwal Kegiatan Wajib</h1>
        <div class="text-gray-600 text-sm">Daftar Jadwal Kegiatan Wajib Mahasiswa Polbangtan-mlg</div>
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
        <div class="w-full bg-white border-2 rounded-lg p-3">
            <div id="accordion-flush" data-accordion="collapse" data-active-classes="bg-white text-gray-900"
                data-inactive-classes="text-gray-500">
                <h2 id="accordion-flush-heading-1">
                    <button type="button"
                        class="flex items-center font-normal justify-between gap-5 py-2 text-gray-500 border-b border-gray-200"
                        data-accordion-target="#accordion-flush-body-1" aria-expanded="false"
                        aria-controls="accordion-flush-body-1">
                        <span>Buat Jadwal Kegiatan</span>
                        <i data-accordion-icon aria-hidden="true" class="ri-add-box-line text-md shrink-0"></i>
                    </button>
                </h2>
                <div id="accordion-flush-body-1" class="hidden" aria-labelledby="accordion-flush-heading-1">
                    <div class="mt-2">
                        <form action="{{ route('admin.createJadwalKegiatanStore') }}" method="post">
                            @csrf
                            <div class="mb-1 font-medium text-gray-700 text-md">Pilih Kegiatan Siswa</div>
                            <ul
                                class="items-center w-full text-sm text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                                @foreach ($kegiatanAsrama as $kegiatan)
                                    <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                                        <div class="flex items-center ps-3">
                                            <input type="checkbox" value="{{ $kegiatan }}"
                                                id="kegiatan_{{ $kegiatan }}" name="kegiatan[]"
                                                onclick="toggleTimeInputs('{{ $kegiatan }}')"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 peer rounded focus:ring-blue-500">
                                            <label for="kegiatan_{{ $kegiatan }}"
                                                class="w-full py-3 ms-2 text-sm text-gray-400 peer-checked:text-gray-900 peer-checked:font-medium">{{ $kegiatan }}</label>
                                            <div class="flex flex-col p-1 gap-1 ms-auto" id="waktu_{{ $kegiatan }}"
                                                style="display: none;">
                                                <div class="text-green-600 text-xs">Mulai :</div>
                                                <input type="time" name="mulai_acara_{{ $kegiatan }}"
                                                    id="mulai_acara_{{ $kegiatan }}"
                                                    class="text-sm p-1 rounded border-1 border-green-700 focus:border-green-700 focus:ring-1 focus:ring-green-700"
                                                    value="06:00">
                                                <div class="text-red-600 text-xs">Selesai :</div>
                                                <input type="time" name="selesai_acara_{{ $kegiatan }}"
                                                    id="selesai_acara_{{ $kegiatan }}"class="text-sm p-1 rounded border-1 border-red-700 focus:border-red-700 focus:ring-1 focus:ring-red-700"
                                                    value="08:00">
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-2 mb-1 font-medium text-gray-700 text-md">Pilih Blok Ruangan</div>
                            <ul
                                class="items-center w-full text-sm text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex">
                                @foreach ($blokRuangan as $ruangan)
                                    <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r">
                                        <div class="flex items-center ps-3">
                                            <input id="{{ $ruangan->id }}" type="checkbox" name="blok[]"
                                                value="{{ $ruangan->id }}"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 peer rounded focus:ring-blue-500">
                                            <label for="{{ $ruangan->id }}"
                                                class="w-full py-3 ms-2 text-sm text-gray-400 peer-checked:text-gray-900 peer-checked:font-medium">{{ $ruangan->name }}</label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-2 mb-1 font-medium text-gray-700 text-md">Pilih Tanggal</div>
                            <div class="flex gap-2">
                                <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan"
                                    class="p-1 rounded border-2 border-utama focus:border-utama focus:ring-1 focus:ring-utama text-sm"
                                    required>
                                <button class="bg-utama rounded text-sm py-1 px-3 text-white" type="submit">Buat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full bg-white border-2 rounded-lg mt-5 p-3">
            <h1 class="font-semibold text-lg">Daftar Jadwal Kegiatan</h1>
            <div class="border-b border-gray-300 my-2"></div>
            <!-- Start coding here -->
            <div class="bg-white border rounded-lg overflow-hidden">
                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <label for="simple-search" class="sr-only">Search</label>
                        <div class="relative w-full flex gap-1">
                            <input type="date" name="date" id="date"
                                class="filter-date bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2">
                            {{-- <button class="bg-utama rounded text-sm py-1 px-3 text-white" type="submit">Filter</button> --}}
                        </div>
                    </div>
                    <div
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-2 focus:ring-gray-200"
                                type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                    class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                Blok Ruangan
                                <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </button>
                            <div id="filterDropdown" class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow">
                                <h6 class="mb-3 text-sm font-medium text-gray-900">Pilih Blok
                                </h6>
                                <ul class="space-y-2 text-sm" aria-labelledby="filterDropdownButton">
                                    @foreach ($blokRuangan as $ruangan)
                                        <li class="flex items-center">
                                            <input id="filterBlok" name="blok[]" type="checkbox"
                                                value="{{ $ruangan->id }}"
                                                class="filter-checkbox w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500">
                                            <label for="filterBlok"
                                                class="ml-2 text-sm font-medium text-gray-900">{{ $ruangan->name }}</label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <button id="tombolfilterkegiatan" data-dropdown-toggle="filterkegiatan"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-2 focus:ring-gray-200"
                                type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                    class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                Kegiatan
                                <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </button>
                            <div id="filterkegiatan" class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow">
                                <h6 class="mb-3 text-sm font-medium text-gray-900">Pilih Kegiatan
                                </h6>
                                <ul class="space-y-2 text-sm" aria-labelledby="tombolfilterkegiatan">
                                    @foreach ($kegiatanAsrama as $kegiatan)
                                        <li class="flex items-center">
                                            <input id="upacarafilter" type="checkbox" id="kegiatan_{{ $kegiatan }}"
                                                value="{{ $kegiatan }}" name="kegiatan[]"
                                                class="filter-checkbox w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500">
                                            <label for="upacarafilter"
                                                class="ml-2 text-sm font-medium text-gray-900">{{ $kegiatan }}</label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 tableDataKegiatanAsrama">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">No</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Jenis Pelanggaran</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                <th scope="col" class="px-4 py-3">Blok</th>
                                <th scope="col" class="px-4 py-3">Mulai</th>
                                <th scope="col" class="px-4 py-3">Selesai</th>
                                <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('admin.partials.jadwal_kegiatan_table', [
                                'daftarJadwalKegiatanAsrama' => $daftarJadwalKegiatanAsrama,
                            ])
                        </tbody>
                    </table>
                </div>
                <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500">
                        Showing <span
                            class="font-semibold text-gray-900">{{ $daftarJadwalKegiatanAsrama->firstItem() }}-{{ $daftarJadwalKegiatanAsrama->lastItem() }}</span>
                        of <span class="font-semibold text-gray-900">{{ $daftarJadwalKegiatanAsrama->total() }}</span>
                    </span>
                    <ul class="inline-flex items-stretch -space-x-px">
                        <li>
                            <a href="{{ $daftarJadwalKegiatanAsrama->previousPageUrl() }}"
                                class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700">
                                <span class="sr-only">Previous</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                        @foreach ($daftarJadwalKegiatanAsrama->getUrlRange($daftarJadwalKegiatanAsrama->currentPage() - 1, $daftarJadwalKegiatanAsrama->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="{{ $num == $daftarJadwalKegiatanAsrama->currentPage() ? 'active' : '' }} flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            <a href="{{ $daftarJadwalKegiatanAsrama->nextPageUrl() }}"
                                class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700">
                                <span class="sr-only">Next</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="w-full bg-white border-2 rounded-lg p-3 mt-5">
            <div id="accordion-flush" data-accordion="collapse" data-active-classes="bg-white text-gray-900"
                data-inactive-classes="text-gray-500">
                <h2 id="accordion-flush-heading-2">
                    <button type="button"
                        class="flex items-center font-normal justify-between gap-5 py-2 text-gray-500 border-b border-gray-200"
                        data-accordion-target="#accordion-flush-body-2" aria-expanded="false"
                        aria-controls="accordion-flush-body-2">
                        <span>Status Absen Blok</span>
                        <i data-accordion-icon aria-hidden="true" class="ri-add-box-line text-md shrink-0"></i>
                    </button>
                </h2>
                <div id="accordion-flush-body-2" class="hidden" aria-labelledby="accordion-flush-heading-2">
                    <form action="{{ route('admin.editJadwalKegiatanByBlok') }}" method="post"
                        id="form-dataFilterKegiatanByBlok">
                        @csrf
                        <div class="flex gap-2 justify-between flex-col sm:flex-row">
                            <div class="mt-2 w-full sm:w-[400px]">
                                <div class="">
                                    <div class="w-full">
                                        <label for="tanggal_kegiatan" class="text-gray-600">Tanggal :</label>
                                    </div>
                                    <div class="w-full">
                                        <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan_filter"
                                            class="text-sm border w-full text-center rounded p-1" required>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <div class="w-full">
                                        <label for="blok_id" class="text-gray-600">Blok Ruangan :</label>
                                    </div>
                                    <select id="blok_id" name="blok_id"
                                        class="text-sm border rounded p-1 w-full text-center" required>
                                        <option value="" hidden>-- Pilih --</option>
                                        @foreach ($blokRuangan as $ruangan)
                                            <option value="{{ $ruangan->id }}">Blok {{ $ruangan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-1">
                                    <div class="w-full">
                                        <label for="jenis_kegiatan" class="text-gray-600">Jenis Kegiatan :</label>
                                    </div>
                                    <select id="jenis_kegiatan" name="jenis_kegiatan"
                                        class="text-sm border rounded p-1 w-full text-center" required>
                                        <option value="" hidden>-- Pilih --</option>
                                        @foreach ($kegiatanAsrama as $kegiatan)
                                            <option value="{{ $kegiatan }}">{{ $kegiatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 mt-1">
                                    <div class="w-full">
                                        <label for="status_kehadiran" class="text-gray-600">Status Kegiatan :</label>
                                    </div>
                                    <select id="status_kehadiran" name="status_kehadiran"
                                        class="text-sm border rounded p-1 w-full text-center" required>
                                        <option value="" hidden>-- Pilih --</option>
                                        @foreach ($statusKehadiran as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit"
                                    class="bg-teal-900 hover:bg-teal-800 text-white p-2 rounded flex items-center w-full justify-center mb-2 text-md text-center">
                                    Simpan
                                </button>
                            </div>
                            <div class="relative overflow-x-auto w-full rounded h-[300px]">
                                <table class="w-full text-sm text-left text-gray-500 rounded" id="tabeldatakegiatan">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="text-center">
                                            <th scope="col" class="px-4 py-3 whitespace-nowrap">No</th>
                                            <th scope="col" class="px-4 py-3">Mulai</th>
                                            <th scope="col" class="px-4 py-3">Selesai</th>
                                            <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="overflow-y-auto" id="filteringJadwalKegiatanByBlok">
                                        @if (isset($dataFilterKegiatanByBlok) && !$dataFilterKegiatanByBlok->isEmpty())
                                            @include('admin.partials.table_filter_data_kegiatan_by_blok', [
                                                'dataFilterKegiatanByBlok' => $dataFilterKegiatanByBlok,
                                            ])
                                        @else
                                            <tr class="border-b text-center">
                                                <td class="px-4 py-3" colspan="4">Filter Kosong. Lengkapi data terlebih
                                                    dahulu.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/library/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/jadwalKegiatan.js') }}" type="text/javascript"></script>
@endsection
