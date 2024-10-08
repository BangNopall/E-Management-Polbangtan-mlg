@extends('layouts.main')
@section('container')
    <div class="p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Data Absensi Pribadi</h1>
        <div class="text-gray-600 text-sm">Rekap histori absensi Anda</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="overflow-x-auto bg-white border-2 border-gray-300 p-2 rounded-md">
            <div class="sm:rounded-lg">
                <div class="flex items-center justify-between pb-4">
                    <div>
                        <form action="{{ url('/dashboard/riwayat-absen') }}" method="get">
                            <select id="filterSelect" name="filter" class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-3 py-1.5">
                                <option value="all" {{ $filter === 'all' ? 'selected' : '' }} class="w-full ml-2 text-sm font-medium text-gray-900 rounded">Semua</option>
                                <option value="7-days" {{ $filter === '7-days' ? 'selected' : '' }} class="w-full ml-2 text-sm font-medium text-gray-900 rounded">7 Hari terakhir</option>
                                <option value="14-days" {{ $filter === '14-days' ? 'selected' : '' }} class="w-full ml-2 text-sm font-medium text-gray-900 rounded">14 Hari terakhir</option>
                                <option value="30-days" {{ $filter === '30-days' ? 'selected' : '' }} class="w-full ml-2 text-sm font-medium text-gray-900 rounded">30 Hari terakhir</option>
                                <option value="6-month" {{ $filter === '6-month' ? 'selected' : '' }} class="w-full ml-2 text-sm font-medium text-gray-900 rounded">6 Bulan terakhir</option>
                            </select>
                        </form>
                    </div>
                </div>
                <table class="w-full text-sm text-left text-gray-500 rounded">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                No
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Hari/Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                Jam Keluar Asrama
                            </th>
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                Jam Masuk Asrama
                            </th>
                        </tr>
                    </thead>
                    <tbody id="result">
                        @foreach ($riwayat as $rwy)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <th scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                {{ $loop->iteration }}
                            </th>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ date('l, d-m-Y', strtotime($rwy->presence_date)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($rwy->presence_keluar == null)
                                    -
                                @else
                                {{ $rwy->presence_keluar }} WIB
                                @endif
                            </td>   
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($rwy->presence_masuk == null)
                                    -
                                @else
                                {{ $rwy->presence_masuk }} WIB
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/library/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataRiwayat.js') }}"></script>
@endsection
