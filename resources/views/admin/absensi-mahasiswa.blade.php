@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Data Absen Mahasiswa</h1>
        <div class="text-gray-600 text-sm">Data absen mahasiswa per waktu dan tanggal</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="w-auto bg-white border-2 rounded-lg p-3">
            <div class="pb-2">Filter Ruangan</div>
            <div class="flex flex-col md:flex-row items-center md:items-end gap-2">
                <div class="items-center w-full md:w-auto">
                    <select id="blok_ruangan"
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
            </div>
        </div>

        <div class="bg-white rounded-lg p-3 mt-5 border-2">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="">
                    <div class="text-gray-900 text-lg font-medium">Absen Mahasiswa</div>
                    <div class="text-gray-600 text-sm mt-1">Daftar absen keluar asrama hari ini</div>
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
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
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                Nama
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Keterangan
                            </th>
                            <th scope="col" class="px-6 py-3 min-w-[140px]">
                                Jam Keluar Asrama
                            </th>
                            <th scope="col" class="px-6 py-3 min-w-[140px]">
                                Jam Kembali Asrama
                            </th>
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                Tanggal
                            </th>
                        </tr>
                    </thead>
                    <tbody id="result">
                        @foreach ($mergedData as $item)
                            <tr class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $loop->iteration + $mergedData->firstItem() - 1 }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ $item->nim }}
                                </td>
                                <td class="px-6 py-4 min-w-[200px]">
                                    {{ $item->name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->status == 'didalam')
                                        <div class="bg-green-600 text-white rounded p-1 text-center">Di dalam Asrama</div>
                                    @elseif ($item->status == 'diluar')
                                        <div class="bg-red-600 text-white rounded p-1 text-center">Di luar Asrama</div>
                                    @elseif ($item->status == 'telat')
                                        <div class="bg-orange-600 text-white rounded p-1 text-center">Telat Masuk Asrama
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 ">
                                    @php
                                        $presenceKeluarArray = explode(',', $item->presence_keluar);
                                    @endphp
                                    @if (count($presenceKeluarArray) > 0)
                                        @foreach ($presenceKeluarArray as $presence)
                                            <div class="py-1">
                                                {{ $presence }}
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $presenceMasukArray = explode(',', $item->presence_masuk);
                                    @endphp
                                    @if (count($presenceMasukArray) > 0)
                                        @foreach ($presenceMasukArray as $presence)
                                            <div class="py-1">
                                                {{ $presence }}
                                            </div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::today()->format('d F Y') }}<br>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
            <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                    Showing <span class="font-semibold text-gray-900">{{ $mergedData->firstItem() }}</span>
                    to <span class="font-semibold text-gray-900">{{ $mergedData->lastItem() }}</span>
                    of <span class="font-semibold text-gray-900">{{ $mergedData->total() }}</span>
                </span>
                <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                    <li>
                        @if ($mergedData->onFirstPage())
                            <span
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                        @else
                            <a href="{{ $mergedData->previousPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                        @endif
                    </li>
                    @foreach ($mergedData->getUrlRange($mergedData->currentPage() - 1, $mergedData->currentPage() + 1) as $num => $url)
                        <li>
                            <a href="{{ $url }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $mergedData->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                        </li>
                    @endforeach
                    <li>
                        @if ($mergedData->hasMorePages())
                            <a href="{{ $mergedData->nextPageUrl() }}"
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

    <div id="data-mahasiswa" data-get-nomor-ruangan="{{ route('admin.getNomorRuangan') }}">
    </div>

    <script src="{{ asset('js/library/jquery.min.js') }}" type="text/javascript"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/absensiMahasiswa.js') }}"></script>
@endsection
