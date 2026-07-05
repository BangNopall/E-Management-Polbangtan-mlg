    @extends('layouts.main')
    @section('container')
        <div class="px-3 py-6 md:p-6 mb-5">
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
            <h1 class="font-semibold text-2xl md:text-3xl mb-3">Detail Absen Kegiatan</h1>
            <div class="text-gray-600 text-sm">Detail riwayat absen kegiatan mahasiswa Asrama Polbangtan-mlg</div>
            <div class="border-b border-gray-300 my-5"></div>
            <div class="w-full">
                <div class="bg-white rounded-lg p-3 border-2">
                    <div class="text-lg font-medium">Profil Siswa</div>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="w-[150px] h-[190px] rounded mx-auto sm:mx-0">
                            @if ($user->image)
                                <img src="{{ asset('storage/images/' . $user->image) }}"
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
                                        <td>{{ $user->nim }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Nama</td>
                                        <td>:</td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Program Studi</td>
                                        <td>:</td>
                                        <td>
                                            @if ($user->prodi_id == null)
                                                -
                                            @else
                                                {{ $user->prodi->prodi }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="150">Blok Ruangan</td>
                                        <td>:</td>
                                        <td>
                                            @if ($user->blok_ruangan_id == null)
                                                -
                                            @else
                                                {{ $user->blok->name }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="150">Nomor Ruangan</td>
                                        <td>:</td>
                                        <td>{{ $user->no_kamar }}</td>
                                    </tr>
                                    <tr>
                                        <td width="150">Kelas</td>
                                        <td>:</td>
                                        <td>
                                            @if ($user->kelas_id == null)
                                                -
                                            @else
                                                {{ $user->kelas->nama_kelas }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="150">Asal Daerah</td>
                                        <td>:</td>
                                        <td>{{ $user->asal_daerah }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-3 mt-5 border-2">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="text-lg font-medium">Kehadiran Siswa</div>
                        <div class="flex items-center space-x-2 w-full md:w-auto">
                            <div>
                                <button type="button" onclick="toggleEditMode()"
                                    class="text-sm p-1 whitespace-nowrap rounded bg-orange-300 text-white" id="btnedit">Edit
                                    Mode</button>
                            </div>
                            <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-mediumtext-gray-900 focus:outline-none bg-white rounded border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-2 focus:ring-gray-200"
                                type="button">
                                <i class="ri-filter-fill mr-2 text-gray-400"></i>
                                Filter
                            </button>
                            <div id="filterDropdown" class="z-10 hidden w-36 p-3 bg-white rounded-lg shadow">
                                <h6 class="mb-3 text-sm font-medium text-gray-900">Filter</h6>
                                <ul class="space-y-2 text-sm" aria-labelledby="filterDropdownButton">
                                    <li class="flex items-center">
                                        <input id="alphafilter" type="checkbox" value="Alpha" name="status_kehadiran"
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600focus:ring-primary-500">
                                        <label for="alphafilter" class="ml-2 text-sm font-medium text-gray-900">Alpha</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="hadirfilter" type="checkbox" value="Hadir" name="status_kehadiran"
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600focus:ring-primary-500">
                                        <label for="hadirfilter" class="ml-2 text-sm font-medium text-gray-900">Hadir</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="izinfilter" type="checkbox" value="Izin" name="status_kehadiran"
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600focus:ring-primary-500">
                                        <label for="izinfilter" class="ml-2 text-sm font-medium text-gray-900">Izin</label>
                                    </li>
                                    <input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan"
                                        class="p-1 rounded border border-gray-200 hover:bg-gray-100 text-xs mb-1 w-full">
                                    <input type="text" name="user_id" id="user_id" value="{{ $user->id }}" hidden>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="border-b border-gray-300 my-2"></div>
                    <ol class="relative text-gray-500 mt-2">
                        <li class="mb-5">
                            <div class="flex flex-col gap-3">
                                <form action="{{ route('admin.editDataKegiatanWajib', $user->id) }}" method="post" class="flex flex-col gap-3">
                                    @csrf
                                    <div class="flex items-center justify-between w-full">
                                        <div class="items-center flex">
                                            <i
                                                class="ri-flag-line flex items-center justify-center w-8 h-8 bg-red-500 rounded-full ring-4ring-red-200 text-white"></i>
                                            <div class="font-medium text-red-400 ml-3">Upacara</div>
                                        </div>
                                        <button type="submit" class="bg-utama p-1 text-sm rounded text-white hidden"
                                            id="simpan1">Simpan</button>
                                    </div>
                                    @if ($dataKegiatanUpacara->isNotEmpty())
                                        <div class="relative overflow-x-auto border border-gray-200 rounded w-full">
                                            <table class="w-full text-sm text-center">
                                                <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                                    <tr class="whitespace-nowrap">
                                                        <th scope="col" class="px-3 py-2">
                                                            NO
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Hari/Tanggal
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam Mulai
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam Selesai
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Status Kegiatan
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam kehadiran
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tableUpacara">
                                                    @include('admin.partials.data_kegiatan_upacara_table', [
                                                        'dataKegiatanUpacara' => $dataKegiatanUpacara,
                                                    ])
                                                </tbody>
                                            </table>
                                            <div id="tableUpacaraNull"></div>
                                    @endif
                                </form>
                                @if ($dataKegiatanUpacara->isEmpty())
                                    <div class="relative overflow-x-auto border border-gray-200 bg-orange-50 rounded w-full">
                                        <table class="w-full text-sm text-center">
                                            <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                                <tr class="whitespace-nowrap">
                                                    <th scope="col" class="px-3 py-2">
                                                        NO
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Hari/Tanggal
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam Mulai
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam Selesai
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Status Kegiatan
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam kehadiran
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                        <div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>
                                    </div>
                                @endif
                            </div>
                        </li>
                        <li class="mb-5">
                            <div class="flex flex-col gap-3">
                                <form action="{{ route('admin.editDataKegiatanWajib', $user->id) }}" method="post" class="flex flex-col gap-3">
                                    @csrf
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center">
                                            <i
                                                class="ri-team-line flex items-center justify-center w-8 h-8 bg-yellow-500 rounded-full ring-4 ring-yellow-200 text-white"></i>
                                            <div class="font-medium text-yellow-500 ml-3">Apel</div>
                                        </div>
                                        <button type="submit" class="bg-utama p-1 text-sm rounded text-white hidden"
                                            id="simpan2">Simpan</button>
                                    </div>
                                    @if ($dataKegiatanApel->isNotEmpty())
                                        <div class="relative overflow-x-auto border border-gray-200 rounded w-full">
                                            <table class="w-full text-sm text-center">
                                                <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                                    <tr class="whitespace-nowrap">
                                                        <th scope="col" class="px-3 py-2">
                                                            NO
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Hari/Tanggal
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam Mulai
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam Selesai
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Status Kegiatan
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam kehadiran
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tableApel">
                                                    {{-- data_kegiatan_apel_table --}}
                                                    @include('admin.partials.data_kegiatan_apel_table', [
                                                        'dataKegiatanApel' => $dataKegiatanApel,
                                                    ])
                                                </tbody>
                                            </table>
                                            <div id="tableApelNull"></div>
                                        </div>
                                    @endif
                                </form>
                                @if ($dataKegiatanApel->isEmpty())
                                    <div class="relative overflow-x-auto border border-gray-200 bg-orange-50 rounded w-full">
                                        <table class="w-full text-sm text-center">
                                            <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                                <tr class="whitespace-nowrap">
                                                    <th scope="col" class="px-3 py-2">
                                                        NO
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Hari/Tanggal
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam Mulai
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam Selesai
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Status Kegiatan
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam kehadiran
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                        <div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>
                                    </div>
                                @endif
                            </div>
                        </li>
                        <li class="">
                            <div class="flex flex-col gap-3">
                                <form action="{{ route('admin.editDataKegiatanWajib', $user->id) }}" method="post" class="flex flex-col gap-3">
                                    @csrf
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center">
                                            <i
                                                class="ri-user-smile-fill flex items-center justify-center w-8 h-8 bg-green-400 rounded-full ring-4 ring-green-200 text-white"></i>
                                            <div class="font-medium text-green-400 ml-3">Senam</div>
                                        </div>
                                        <button type="submit" class="bg-utama p-1 text-sm rounded text-white hidden"
                                            id="simpan3">Simpan</button>
                                    </div>
                                    @if ($dataKegiatanSenam->isNotEmpty())
                                        <div class="relative overflow-x-auto border border-gray-200 rounded w-full">
                                            <table class="w-full text-sm text-center">
                                                <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                                    <tr class="whitespace-nowrap">
                                                        <th scope="col" class="px-3 py-2">
                                                            NO
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Hari/Tanggal
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam Mulai
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam Selesai
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Status Kegiatan
                                                        </th>
                                                        <th scope="col" class="px-3 py-2">
                                                            Jam kehadiran
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tableSenam">
                                                    {{-- data_kegiatan_senam_table --}}
                                                    @include('admin.partials.data_kegiatan_senam_table', [
                                                        'dataKegiatanSenam' => $dataKegiatanSenam,
                                                    ])
                                                </tbody>
                                            </table>
                                            <div id="tableSenamNull"></div>
                                        </div>
                                    @endif
                                </form>
                                @if ($dataKegiatanSenam->isEmpty())
                                    <div class="relative overflow-x-auto border border-gray-200 bg-orange-50 rounded w-full">
                                        <table class="w-full text-sm text-center">
                                            <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                                <tr class="whitespace-nowrap">
                                                    <th scope="col" class="px-3 py-2">
                                                        NO
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Hari/Tanggal
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam Mulai
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam Selesai
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Status Kegiatan
                                                    </th>
                                                    <th scope="col" class="px-3 py-2">
                                                        Jam kehadiran
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                        <div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>
                                    </div>
                                @endif
                            </div>
                        </li>
                    </ol>
                    @if ($keyPagination == 'dataKegiatanUpacara')
                        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                            aria-label="Table navigation">
                            <span
                                class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing
                                <span class="font-semibold text-gray-900">{{ $dataKegiatanUpacara->firstItem() }}</span> to
                                <span class="font-semibold text-gray-900">{{ $dataKegiatanUpacara->lastItem() }}</span> of
                                <span
                                    class="font-semibold text-gray-900">{{ $dataKegiatanSenam->total() + $dataKegiatanApel->total() + $dataKegiatanUpacara->total() }}</span>
                            </span>
                            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                                <li>
                                    <a href="{{ $dataKegiatanUpacara->previousPageUrl() }}"
                                        class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                                </li>
                                @foreach ($dataKegiatanUpacara->getUrlRange($dataKegiatanUpacara->currentPage() - 1, $dataKegiatanUpacara->currentPage() + 1) as $num => $url)
                                    <li>
                                        <a href="{{ $url }}"
                                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $dataKegiatanUpacara->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                                    </li>
                                @endforeach
                                <li>
                                    <a href="{{ $dataKegiatanUpacara->nextPageUrl() }}"
                                        class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                                </li>
                            </ul>
                        </nav>
                    @elseif ($keyPagination == 'dataKegiatanApel')
                        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                            aria-label="Table navigation">
                            <span
                                class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing
                                <span class="font-semibold text-gray-900">{{ $dataKegiatanApel->firstItem() }}</span> to
                                <span class="font-semibold text-gray-900">{{ $dataKegiatanApel->lastItem() }}</span> of <span
                                    class="font-semibold text-gray-900">{{ $dataKegiatanSenam->total() + $dataKegiatanApel->total() + $dataKegiatanUpacara->total() }}</span>
                            </span>
                            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                                <li>
                                    <a href="{{ $dataKegiatanApel->previousPageUrl() }}"
                                        class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                                </li>
                                @foreach ($dataKegiatanApel->getUrlRange($dataKegiatanApel->currentPage() - 1, $dataKegiatanApel->currentPage() + 1) as $num => $url)
                                    <li>
                                        <a href="{{ $url }}"
                                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $dataKegiatanApel->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                                    </li>
                                @endforeach
                                <li>
                                    <a href="{{ $dataKegiatanApel->nextPageUrl() }}"
                                        class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                                </li>
                            </ul>
                        </nav>
                    @elseif ($keyPagination == 'dataKegiatanSenam')
                        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                            aria-label="Table navigation">
                            <span
                                class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing
                                <span class="font-semibold text-gray-900">{{ $dataKegiatanSenam->firstItem() }}</span> to
                                <span class="font-semibold text-gray-900">{{ $dataKegiatanSenam->lastItem() }}</span> of <span
                                    class="font-semibold text-gray-900">{{ $dataKegiatanSenam->total() + $dataKegiatanApel->total() + $dataKegiatanUpacara->total() }}</span>
                            </span>
                            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                                <li>
                                    <a href="{{ $dataKegiatanSenam->previousPageUrl() }}"
                                        class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                                </li>
                                @foreach ($dataKegiatanSenam->getUrlRange($dataKegiatanSenam->currentPage() - 1, $dataKegiatanSenam->currentPage() + 1) as $num => $url)
                                    <li>
                                        <a href="{{ $url }}"
                                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $dataKegiatanSenam->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                                    </li>
                                @endforeach
                                <li>
                                    <a href="{{ $dataKegiatanSenam->nextPageUrl() }}"
                                        class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                                </li>
                            </ul>
                        </nav>
                    @endif
                </div>

                <div class="bg-white rounded-lg p-3 border-2 mt-3">
                    <div class="text-lg font-medium">Jumlah Kehadiran</div>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="relative overflow-x-auto rounded mt-2">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-orange-100">
                                <tr>
                                    <th scope="col" class="px-3 py-2">
                                        JENIS KEGIATAN
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-center">
                                        HADIR
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-center">
                                        ALPHA
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-center">
                                        IZIN
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rekapKegiatan as $jenisKegiatan => $dataKegiatan)
                                    <tr class="bg-orange-50 border-b">
                                        <td class="px-3 py-2 min-w-[580px]">
                                            {{ ucfirst($jenisKegiatan) }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            {{ $dataKegiatan['Hadir'] }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            {{ $dataKegiatan['Alpha'] }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            {{ $dataKegiatan['Izin'] }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-orange-50 border-t border-gray-800 text-gray-600 font-medium">
                                    <td class="px-3 py-2 min-w-[580px]">
                                        Total Seluruh Jenis Kegiatan
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        {{ array_sum(array_column($rekapKegiatan, 'Hadir')) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        {{ array_sum(array_column($rekapKegiatan, 'Alpha')) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        {{ array_sum(array_column($rekapKegiatan, 'Izin')) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <button type="button" onclick="window.location.href='/data-kegiatan-wajib'"
                    class="mt-2 focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-2 focus:ring-red-300 font-medium rounded-lg text-sm px-2.5 py-1.5 me-2">Kembali</button>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="{{ asset('js/detailKegiatan-user.js') }}"></script>
    @endsection
