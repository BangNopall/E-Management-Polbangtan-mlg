@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Riwayat Pelanggaran</h1>
        <div class="text-gray-600 text-sm">Daftar seluruh data riwayat pelanggaran anda.</div>
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
        @if ($dataRejected->isNotEmpty())
            <div class="w-full bg-white border-2 rounded-lg p-3 mt-5">
                <div class="p-4 mb-4 text-sm text-red-800 border border-red-800 rounded-lg bg-red-50" role="alert">
                    <span class="font-medium">Perhatian!</span> laporan anda <span class="font-medium">Ditolak</span>. Harap
                    perbaiki laporan anda.
                </div>
                <div class="relative overflow-x-auto border-2 border-red-200 rounded mt-2">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-red-100">
                            <tr class="">
                                <th scope="col" class="px-2 py-3">
                                    NO
                                </th>
                                <th scope="col" class="px-3 py-3 w-[20%]">
                                    TANGGAL
                                </th>
                                <th scope="col" class="px-6 py-3 w-full">
                                    JENIS PELANGGARAN
                                </th>
                                <th scope="col" class="px-3 py-3">
                                    STATUS
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @foreach ($dataRejected as $dataRejecteds)
                                <tr class="bg-red-50 border-b">
                                    <td class="px-3 py-3">
                                        {{ $i++ }}
                                    </td>
                                    <td class="px-3 py-3 min-w-[160px]">
                                        {{ $dataRejecteds->formatted_date }}
                                    </td>
                                    <td class="px-6 py-3 min-w-[580px]">
                                        {{ $dataRejecteds->jenis_pelanggaran }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div
                                            class="text-sm font-medium p-1 w-full text-center bg-red-400 rounded text-white">
                                            Rejected
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <form action="{{ route('home.formHukumShow', 1) }}" method="get">
                                            @include('partials.modals.perbaiki')
                                            <button type="button" class="text-red-900" id="deleteButton" data-modal-target="perbaikiModal{{ $dataRejecteds->id }}"
                                            data-modal-toggle="perbaikiModal{{ $dataRejecteds->id }}">Perbaiki</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <div class="w-full bg-white border-2 rounded-lg p-3 mt-5">
            @if ($data->isEmpty())
                <div class="p-4 mb-4 text-sm text-green-800 border border-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Excellent!</span> Anda sangat disiplin. Pertahankan kedisiplinan anda.
                </div>
            @elseif ($data != null && $data->where('statusPelanggaran', 'progressing')->isNotEmpty())
                <div id="alert-4"
                    class="flex items-center p-4 mb-4 text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-300"
                    role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div class="ms-3 text-sm">
                        <span class="font-medium">Perhatian!</span> Buka laporan dengan status progressing untuk
                        mengetahui
                        hukuman
                    </div>
                    <button type="button"
                        class="ms-auto -mx-1.5 -my-1.5 bg-yellow-50 text-yellow-500 rounded-lg focus:ring-2 focus:ring-yellow-400 p-1.5 hover:bg-yellow-200 inline-flex items-center justify-center h-8 w-8"
                        data-dismiss-target="#alert-4" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>
            @endif
            <div class="relative overflow-x-auto border-2 border-gray-200 sm:rounded-lg mt-2">
                <table class="w-full text-sm text-left rtl:text-right">
                    @if ($data->isNotEmpty())
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr class="">
                                <th scope="col" class="px-2 py-3">
                                    NO
                                </th>
                                <th scope="col" class="px-3 py-3 w-[20%]">
                                    TANGGAL
                                </th>
                                <th scope="col" class="px-6 py-3 w-full">
                                    JENIS PELANGGARAN
                                </th>
                                <th scope="col" class="px-3 py-3">
                                    STATUS
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                    @endif
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($data as $itemData)
                            <tr class="bg-gray-50 border-b">
                                <td class="px-3 py-3">
                                    {{ $i++ }}
                                </td>
                                <td class="px-3 py-3 min-w-[160px]">
                                    {{ $itemData->formatted_date }}
                                </td>
                                <td class="px-6 py-3 min-w-[580px]">
                                    {{ $itemData->jenis_pelanggaran }}
                                </td>
                                @if ($itemData->statusPelanggaran == 'submitted')
                                    <td class="px-3 py-3">
                                        <div
                                            class="text-sm font-medium p-1 w-full text-center bg-gray-300 rounded text-gray-700">
                                            Submitted
                                        </div>
                                    </td>
                                @elseif ($itemData->statusPelanggaran == 'progressing')
                                    <td class="px-3 py-3">
                                        <div
                                            class="text-sm font-medium p-1 w-full text-center bg-yellow-500 rounded text-white">
                                            Progressing
                                        </div>
                                    </td>
                                @elseif ($itemData->statusPelanggaran == 'Done')
                                    <td class="px-3 py-3">
                                        <div
                                            class="text-sm font-medium p-1 bg-green-700 w-full text-center rounded text-white">
                                            Done
                                        </div>
                                    </td>
                                @endif

                                <td class="px-6 py-3">
                                    <form action="{{ route('home.riwayatPelanggaranDetail', $itemData->id) }}"
                                        method="get">
                                        @if ($itemData->statusPelanggaran == 'submitted')
                                            <span class="text-gray-600 cursor-not-allowed">Buka</span>
                                        @elseif ($itemData->statusPelanggaran != 'submitted' || $itemData->statusPelanggaran != 'rejected')
                                            <button class="text-blue-600">Buka</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                    Showing <span class="font-semibold text-gray-900">{{ $data->firstItem() }}</span>
                    to <span class="font-semibold text-gray-900">{{ $data->lastItem() }}</span>
                    of <span class="font-semibold text-gray-900">{{ $data->total() + $dataRejected->total() }}</span>
                </span>
                <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                    <li>
                        @if ($data->onFirstPage())
                            <span
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                        @else
                            <a href="{{ $data->previousPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                        @endif
                    </li>
                    @foreach ($data->getUrlRange($data->currentPage() - 1, $data->currentPage() + 1) as $num => $url)
                        <li>
                            <a href="{{ $url }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $data->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                        </li>
                    @endforeach
                    <li>
                        @if ($data->hasMorePages())
                            <a href="{{ $data->nextPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                        @else
                            <span
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg">Next</span>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endsection
