@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Laporan Pelanggaran</h1>
        <div class="text-gray-600 text-sm">Daftar laporan pelanggaran mahasiswa Asrama Polbangtan-mlg</div>
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
            <ol class="relative text-gray-500 border-s border-gray-200 ml-3 my-5">
                <li class="mb-8 lg:mb-10 ms-6">
                    <div class="flex flex-col lg:flex-row gap-3 lg:gap-5">
                        <div class="flex flex-col justify-center items-start lg:items-center w-[140px]">
                            <i
                                class="ri-survey-line absolute flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full -start-4 ring-4 ring-white"></i>
                            <div class="font-medium">Submitted</div>
                        </div>
                        @if (!$allData['submitted']->isEmpty())
                            {{-- Submitted --}}
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        @include('admin.partials.head-laporan')
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($allData['submitted'] as $data)
                                            @include('admin.partials.body-laporan', ['data' => $data])
                                            <?php $i++; ?>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        @include('admin.partials.head-laporan')
                                    </thead>
                                </table>
                                <div class="text-center my-5 text-xs w-full">Laporan belum tersedia.</div>
                            </div>
                        @endif
                    </div>

                </li>
                <li class="mb-8 lg:mb-10 ms-6">
                    <div class="flex flex-col lg:flex-row gap-3 lg:gap-5">
                        <div class="flex flex-col justify-center items-start lg:items-center w-[140px]">
                            <i
                                class="ri-book-open-line absolute flex items-center justify-center text-white w-8 h-8 bg-yellow-500 rounded-full -start-4 ring-2 ring-yellow-200"></i>
                            <h3 class="font-medium text-yellow-500">Progressing</h3>
                        </div>
                        @if (!$allData['progressing']->isEmpty())
                            {{-- progressing --}}
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        @include('admin.partials.head-laporan')
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($allData['progressing'] as $data)
                                            @include('admin.partials.body-laporan', ['data' => $data])
                                            <?php $i++; ?>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        @include('admin.partials.head-laporan')
                                    </thead>
                                </table>
                                <div class="text-center my-5 text-xs w-full">Laporan belum tersedia.</div>
                            </div>
                        @endif
                    </div>
                </li>
                <li class="ms-6">
                    <div class="flex flex-col lg:flex-row gap-3 lg:gap-5">
                        <div class="flex flex-col justify-center items-start lg:items-center w-[140px]">
                            <i
                                class="ri-file-reduce-line absolute flex items-center justify-center text-white w-8 h-8 bg-red-500 rounded-full -start-4 ring-2 ring-red-200"></i>
                            <h3 class="font-medium text-red-400">Rejected</h3>
                        </div>
                        @if (!$allData['rejected']->isEmpty())
                            {{-- Rejected --}}
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left rtl:text-right">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        @include('admin.partials.head-laporan')
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($allData['rejected'] as $data)
                                            @include('admin.partials.body-laporan', ['data' => $data])
                                            <?php $i++; ?>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    @include('admin.partials.head-laporan')
                                    </thead>
                                </table>
                                <div class="text-center my-5 text-xs w-full">Laporan belum tersedia.</div>
                            </div>
                        @endif
                    </div>
                </li>
            </ol>

            {{-- pagination --}}
            @if ($keyPagination == 'submitted')
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                        Showing <span class="font-semibold text-gray-900">{{ $allData['submitted']->firstItem() }}</span>
                        to <span class="font-semibold text-gray-900">{{ $allData['submitted']->lastItem() }}</span>
                        of <span
                            class="font-semibold text-gray-900">{{ $allData['submitted']->total() + $allData['rejected']->total() + $allData['progressing']->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            @if ($allData['submitted']->onFirstPage())
                                <span
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                            @else
                                <a href="{{ $allData['submitted']->previousPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                            @endif
                        </li>
                        @foreach ($allData['submitted']->getUrlRange($allData['submitted']->currentPage() - 1, $allData['submitted']->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $allData['submitted']->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            @if ($allData['submitted']->hasMorePages())
                                <a href="{{ $allData['submitted']->nextPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                            @else
                                <span
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg">Next</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @elseif ($keyPagination == 'progressing')
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                        Showing <span
                            class="font-semibold text-gray-900">{{ $allData['progressing']->firstItem() }}</span>
                        to <span class="font-semibold text-gray-900">{{ $allData['progressing']->lastItem() }}</span>
                        of <span
                            class="font-semibold text-gray-900">{{ $allData['submitted']->total() + $allData['rejected']->total() + $allData['progressing']->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            @if ($allData['progressing']->onFirstPage())
                                <span
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                            @else
                                <a href="{{ $allData['progressing']->previousPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                            @endif
                        </li>
                        @foreach ($allData['progressing']->getUrlRange($allData['progressing']->currentPage() - 1, $allData['progressing']->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $allData['progressing']->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            @if ($allData['progressing']->hasMorePages())
                                <a href="{{ $allData['progressing']->nextPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                            @else
                                <span
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-300 bg-white border border-gray-300 rounded-e-lg">Next</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @elseif ($keyPagination == 'rejected')
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                        Showing <span
                            class="font-semibold text-gray-900">{{ $allData['rejected']->firstItem() }}</span>
                        to <span class="font-semibold text-gray-900">{{ $allData['rejected']->lastItem() }}</span>
                        of <span
                            class="font-semibold text-gray-900">{{ $allData['submitted']->total() + $allData['rejected']->total() + $allData['progressing']->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            @if ($allData['rejected']->onFirstPage())
                                <span
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                            @else
                                <a href="{{ $allData['rejected']->previousPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                            @endif
                        </li>
                        @foreach ($allData['rejected']->getUrlRange($allData['rejected']->currentPage() - 1, $allData['rejected']->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $allData['rejected']->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            @if ($allData['rejected']->hasMorePages())
                                <a href="{{ $allData['rejected']->nextPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                            @else
                                <span
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-300 bg-white border border-gray-300 rounded-e-lg">Next</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endsection
