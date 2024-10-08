@extends('layouts.main')
@section('container')
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
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Export Laporan</h1>
        <div class="text-gray-600 text-sm">Cetak hasil laporan siswa Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2">
            <div class="bg-white rounded-lg p-3 mt-5 border-2 w-full mx-auto sm:w-[90%]">
                <div class="text-lg font-medium">Kegiatan Wajib Siswa</div>
                <div class="border-b border-gray-300 my-2"></div>
                <form action="{{ route('admin.generateLaporanPelanggaranKegiatanAsrama') }}" method="post">
                    @csrf
                    <div class="mb-4 space-y-1 mt-1">
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Jenis Export :</label>
                            <select name="type" class="text-sm border rounded p-1 w-full text-center" id="kelasSelect"
                                required onchange="toggleDateInput()">
                                <option value="" hidden>-- Pilih --</option>
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                            </select>
                        </div>
                        <div class="space-y-1" id="weekly" style="display: none;">
                            <div class="w-full">
                                <label for="" class="text-gray-600">Pilih tanggal :</label>
                            </div>
                            <input type="week" name="weeklyInput" id="weeklyInput"
                                class="text-sm border w-full text-center rounded p-1" value="">
                        </div>
                        <div class="space-y-1" id="monthly" style="display: none;">
                            <div class="w-full">
                                <label for="" class="text-gray-600">Pilih tanggal :</label>
                            </div>
                            <input type="month" name="monthlyInput" id="monthlyInput"
                                class="text-sm border w-full text-center rounded p-1" value="">
                        </div>
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Pilih Jenis Kegiatan :</label>
                        </div>
                        <select name="jenis_kegiatan" class="text-sm border rounded p-1 w-full text-center" required>
                            <option value="" hidden>-- Pilih --</option>
                            @foreach ($kegiatanAsrama as $kegiatan)
                                <option value="{{ $kegiatan }}">{{ $kegiatan }}</option>
                            @endforeach
                        </select>
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Pilih Blok :</label>
                        </div>
                        <select name="blok_id" class="text-sm border rounded p-1 w-full text-center" required>
                            <option value="" hidden>-- Pilih --</option>
                            @foreach ($blok as $b)
                                <option value="{{ $b->id }}">Blok {{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" name="submit" value="pdf"
                        class="bg-teal-900 hover:bg-teal-800 text-white p-2 rounded flex items-center w-full justify-center mb-2 text-md text-center">
                        <i class="ri-printer-fill mr-2"></i>
                        Generate PDF
                    </button>
                </form>
            </div>
            <div class="bg-white rounded-lg p-3 mt-5 border-2 w-full mx-auto sm:w-[90%]">
                <div class="text-lg font-medium">Pelanggaran Siswa</div>
                <div class="border-b border-gray-300 my-2"></div>
                <form action="{{ route('admin.generateLaporanPelanggaran') }}" method="post">
                    @csrf
                    <div class="mb-4 space-y-1 mt-1">
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Pilih Kelas :</label>
                        </div>
                        <select name="kelas" class="text-sm border rounded p-1 w-full text-center" required>
                            <option value="" hidden>-- Pilih --</option>
                            @foreach ($kelas as $key => $kl)
                                <option value="{{ $key }}">Kelas - {{ $key }} </option>
                            @endforeach
                        </select>
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Pilih Prodi :</label>
                        </div>
                        <select name="prodi" class="text-sm border rounded p-1 w-full text-center" required>
                            <option value="" hidden>-- Pilih --</option>
                            @foreach ($prodi as $key => $prodi)
                                <option value="{{ $prodi->id }}">prodi - {{ $prodi->prodi }} </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" name="submit" value="pdf"
                        class="bg-teal-900 hover:bg-teal-800 text-white p-2 rounded flex items-center w-full justify-center mb-2 text-md text-center">
                        <i class="ri-printer-fill mr-2"></i>
                        Generate PDF
                    </button>
                </form>
            </div>
            <div class="bg-white rounded-lg p-3 mt-5 border-2 w-full mx-auto sm:w-[90%]">
                <div class="text-lg font-medium">Absen Keluar Asrama</div>
                <div class="border-b border-gray-300 my-2"></div>
                <form action="/generate-laporan" method="post">
                    @csrf
                    <div class="space-y-1">
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Pilih tanggal :</label>
                        </div>
                        <div class="w-full">
                            <input type="week" name="tanggalSiswa" id="tanggalSiswa"
                                class="text-sm border w-full text-center rounded p-1" value="" required>
                        </div>
                    </div>
                    <div class="mb-4 space-y-1 mt-1">
                        <div class="w-full">
                            <label for="tanggalSiswa" class="text-gray-600">Pilih Blok Ruangan :</label>
                        </div>
                        <select name="blok" class="text-sm border rounded p-1 w-full text-center" required>
                            <option value="" hidden>-- Pilih --</option>
                            @foreach ($blok as $b)
                                <option value="{{ $b->id }}">Blok {{ $b->name }}</option>
                            @endforeach
                            <!-- Add more options here -->
                        </select>
                    </div>
                    <button type="submit" name="submit" value="pdf"
                        class="bg-teal-900 hover:bg-teal-800 text-white p-2 rounded flex items-center w-full justify-center mb-2 text-md text-center">
                        <i class="ri-printer-fill mr-2"></i>
                        Generate PDF
                    </button>
                    <button type="submit" name="submit" value="excel"
                        class="bg-teal-900 hover:bg-teal-800 text-white p-2 rounded flex items-center mb-2 w-full text-md justify-center text-center">
                        <i class="ri-printer-fill mr-2"></i>
                        Generate EXCEL
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/generate.js') }}" type="text/javascript"></script>
@endsection
