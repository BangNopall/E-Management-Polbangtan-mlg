@extends('layouts.main')
@section('container')
    <div id="loadingAnimation"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 transition-all hidden">
        <div role="status">
            <svg aria-hidden="true" class="w-12 h-12 mr-2 text-gray-500 animate-spin fill-utama" viewBox="0 0 100 101"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor" />
                <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill" />
            </svg>
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Pengaturan Admin</h1>
        <div class="text-gray-600 text-sm">Pengaturan sistem Administrator E-Absen Polbangtan-mlg</div>
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
        <div class="bg-white rounded-lg p-0 sm:p-3 mt-5 border-2">
            <div class="w-full relative">
                <ul class="flex flex-col sm:flex-row relative row list-unstyled" id="tabButtons">
                    <li>
                        <button onclick="showTab('import', this)" id="tombol"
                            class="cursor-pointer font-semibold text-md block text-center w-full px-8 py-3 text-utama border-b-2 border-utama tab-button">
                            Import
                        </button>
                        <div class="border-b border-gray-300 -mt-[1px] block sm:hidden"></div>
                    </li>
                    <li>
                        <button onclick="showTab('kelas', this)" id="tombol"
                            class="cursor-pointer font-semibold w-full text-md tab-button block text-center px-8 py-3 text-gray-500 hover:text-utama hover:border-b hover:border-utama transition-all duration-500 ease-in-out">
                            Kelas
                        </button>
                    </li>
                </ul>
            </div>
            <div class="border-b border-gray-300 -mt-[1px]"></div>
            {{-- IMPORT --}}
            <div class="pt-6 pb-3 px-3 tab-content" id="importTab">
                <p class="text-sm text-gray-500">
                    Pilih opsi ini jika Anda ingin menginport data user atau data mahasiswa dari file excel / xlsx.
                </p>
                <form action="{{ route('admin.sistemAdminImportClass') }}" method="post" enctype="multipart/form-data"
                    id="importForm">
                    @csrf
                    <label class="block mb-2 text-sm font-medium text-utama mt-5" for="file_input">Upload file</label>
                    <input name="file"
                        class="block w-full sm:w-[300px] text-xs text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50"
                        aria-describedby="file_input_help" id="file_input" type="file" accept=".xls, .xlsx">
                    <p class="mt-1 text-sm text-gray-500" id="file_input_help">XLS, XLSX</p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button
                            class="bg-utama text-sm p-2 rounded mt-3 w-full sm:w-auto text-white hover:text-white hover:bg-teal-900"
                            type="submit">Import</button>
                </form>
                <form action="{{ route('admin.downloadExcelTemplate') }}" method="get">
                    @csrf
                    <button
                        class="bg-utama p-2 text-sm rounded mt-0 sm:mt-3 text-white hover:text-white w-full sm:w-auto hover:bg-teal-900"
                        type="submit">Template Excel</button>
                </form>
            </div>
        </div>
        {{-- KELAS --}}
        <div class="pt-6 pb-3 px-3 tab-content" id="kelasTab" style="display: none">
            <p class="text-sm text-gray-500">
                Pilih opsi ini jika Anda ingin melakukan sistem kenaikan kelas pada Aplikasi Management Asrama
                Polbangtan-mlg
            </p>
            <form action="{{ Route('admin.sistemAdminUpgradeClass') }}" method="post">
                @csrf
                @include('partials.modals.upgrade')
                <button id="deleteButton" data-modal-target="upgradeModal" data-modal-toggle="upgradeModal"
                    class="px-2 py-2 bg-utama rounded border text-white text-sm mt-5 hover:bg-teal-900 transition-all"
                    type="button">Upgrade Kelas
                </button>
            </form>
        </div>
    </div>
    <div class="tab-content" id="import">
        <div class="hidden"></div>
    </div>
    <div class="tab-content" id="kelas" style="display: none">
        <div class="hidden"></div>
    </div>
    <script src="{{ asset('js/library/flowbite.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sistem.js') }}"></script>
@endsection
