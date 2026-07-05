@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Edit Mahasiswa</h1>
        <div class="text-gray-600 text-sm">Form edit mahasiswa {{ $user->name }}</div>
        <div class="border-b border-gray-300 my-5"></div>
        {{-- alertbox --}}
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
        {{-- ALAT CHECK FORM ERROR --}}

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- ALAT CHECK FORM ERROR --}}

        <div id="popup" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 z-50 hidden">
            <div class="flex items-center justify-center h-full">
                <div class="bg-white p-5 sm:p-8 rounded shadow-md w-[90%] sm:w-[300px]">
                    <form action="{{ route('admin.dataMahasiswaEditDataResetLogin', $user->id) }}" method="post">
                        @csrf
                        <h2 class="text-2xl font-bold mb-4">Reset login</h2>
                        <p>Alasan: </p>
                        <textarea name="message" id="message" rows="4"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Alasan"></textarea>

                        <button type="submit"
                            class="text-white bg-red-400 hover:bg-red-800 focus:ring-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full p-1.5 text-center mt-1">Reset
                            Login</button>
                    </form>
                    <button id="closePopup" class="mt-2 bg-gray-500 text-white py-1 px-4 rounded">Tutup</button>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-center items-center sm:items-start gap-8">
            <div class="bg-white px-2 py-3 rounded border border-gray-400 w-full sm:w-[340px] h-auto sm:h-[350px]">
                <div class="flex flex-col items-center justify-center mt-3 md:mt-0">
                    <label for="foto-profil">
                        @if ($user->image)
                            <img src="{{ asset('storage/images/' . $user->image) }}" class="w-24 h-24 rounded-full mb-2"
                                alt="Foto Profil">
                        @else
                            <img src="https://placehold.co/500x500" class="w-24 h-24 rounded-full mb-2" alt="Foto Profil">
                        @endif
                    </label>
                    <div class="text-center">
                        <div class="text-gray-900 font-medium">{{ $user->name }}</div>
                        @if ($user->blok_ruangan_id != null)
                            <div class="text-gray-500 text-sm">{{ $user->blok->name }}{{ $user->no_kamar }}</div>
                        @else
                            -
                        @endif
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 mt-1">
                        <form action="{{ route('admin.dataMahasiswaEditDataResetStatus', $user->id) }}" method="post">
                            @csrf
                            <button type="submit"
                                class="text-white bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto p-2 text-center">
                                Reset Status</button>
                        </form>
                        <form action="" method="post">
                            @csrf
                            <button type="button" id="showPopup"
                                class="text-white bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto p-2 text-center">Reset
                                Login</button>
                        </form>
                    </div>
                    <form action="{{ route('admin.deleteFotoProfileMahasiswa', $user->id) }}" method="post">
                        @csrf
                        @include('partials.modals.fotohapus')
                        <button type="button" class="bg-red-500 p-2 rounded-lg text-sm w-full text-white mt-1" id="deleteButton"
                        data-modal-target="hapusfotoModal"
                        data-modal-toggle="hapusfotoModal">Hapus Foto</button>
                    </form>
                </div>
            </div>
            <div class="bg-white p-4 rounded border border-gray-400 w-full sm:w-[75%]">
                <form action="{{ route('admin.dataMahasiswaEditData', $user->id) }}" method="post">
                    @csrf
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                            <input type="text" id="name" name="name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                                placeholder="Budi Santoso" value="{{ $user->name }}" required>
                        </div>
                        <div>
                            <label for="prodi_id" class="block mb-2 text-sm font-medium text-gray-900">Program
                                Studi</label>
                            <select id="prodi_id" name="prodi_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5">
                                <option value="" hidden>-- Pilih Prodi --</option>
                                @foreach ($prodiOptions as $prodiOption)
                                    <option value="{{ $prodiOption->id }}"
                                        {{ old('prodi_id', $user->prodi_id) == $prodiOption->id ? 'selected' : '' }}>
                                        {{ $prodiOption->prodi }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label for="nim" class="block mb-2 text-sm font-medium text-gray-900">NIM</label>
                            <input type="text" id="nim" name="nim" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                                placeholder="0123456" value="{{ $user->nim }}">
                        </div>
                        <div>
                            <label for="blok_ruangan_id" class="block mb-2 text-sm font-medium text-gray-900">Blok
                                Ruangan</label>
                            <select id="blok_ruangan_id" name="blok_ruangan_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5">
                                <option value="" hidden>-- Pilih Blok --</option>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}"
                                        {{ old('blok_ruangan_id', $user->blok_ruangan_id) == $block->id ? 'selected' : '' }}>
                                        {{ $block->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        <div>
                            <label for="asal_daerah" class="block mb-2 text-sm font-medium text-gray-900">Asal
                                Daerah</label>
                            <input type="text" id="asal_daerah" name="asal_daerah" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                                placeholder="Kota Malang" value="{{ $user->asal_daerah }}">
                        </div>
                        <div>
                            <label for="no_kamar" class="block mb-2 text-sm font-medium text-gray-900">Nomor
                                Ruangan</label>
                            <input type="number" id="no_kamar" name="no_kamar" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                                placeholder="10" value="{{ $user->no_kamar }}">
                        </div>
                        <div>
                            <label for="kelas_id" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                            <select id="kelas_id" name="kelas_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5">
                                <option value="" hidden>-- Pilih kelas --</option>
                                @foreach ($kelas as $kls)
                                    <option
                                        value="{{ $kls->id }}"{{ old('kelas_id', $user->kelas_id) == $kls->id ? 'selected' : '' }}>
                                        {{ $kls->nama_kelas }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="block max-w-lg">
                            <div>
                                <label for="set_Status" class="  mb-2 text-sm font-medium text-gray-900">Set
                                    Status</label>

                                @if ($user->status == 'didalam')
                                    <div
                                        class="text-white mt-3 block sm:inline bg-utama hover:bg-teal-800 font-medium rounded text-sm w-full sm:w-auto px-2 py-1.5 text-center">
                                        {{ $user->status }}</div>
                                @else
                                    <div
                                        class="text-white mt-3 block sm:inline bg-orange-400 hover:bg-orange-800 font-medium rounded text-sm w-full sm:w-auto px-2 py-1.5 text-center">
                                        {{ $user->status }}</div>
                                @endif
                            </div>
                            <br>
                            <div>
                                <label for="status_Login" class="block mb-2 text-sm font-medium text-gray-900">Status
                                    Login</label>

                                @if ($loginStatus == 'User Berhasil Akses Aplikasi')
                                    <div
                                        class="text-white mt-3 block sm:inline bg-utama hover:bg-teal-800 font-medium rounded text-sm w-full sm:w-auto px-2 py-1.5 text-center">
                                        {{ $loginStatus }}</div>
                                @else
                                    <div
                                        class="text-white mt-3 block sm:inline bg-orange-400 hover:bg-orange-800 font-medium rounded text-sm w-full sm:w-auto px-2 py-1.5 text-center">
                                        {{ $loginStatus }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email address</label>
                        <input type="email" id="email" required
                            class="bg-gray-50 border cursor-not-allowed border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                            placeholder="example@gmail.com" value="{{ $user->email }}" readonly>
                    </div>
                    <div class="mb-6">
                        <label for="number" class="block mb-2 text-sm font-medium text-gray-900">Nomor Handphone</label>
                        <input type="number" id="number" required
                            class="bg-gray-50 border cursor-not-allowed border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                            placeholder="08xxxxxxxx" value="{{ $user->no_hp }}" readonly>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Reset Password</label>
                        <div class="relative">
                            <input type="password" id="password" name ="password"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-utama focus:border-utama block w-full p-2.5"
                                placeholder="•••••••••">
                            <button id="togglePassword" type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-500 cursor-pointer">
                                <i id="showEye" class="ri-eye-line text-lg text-black"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit"
                        class="text-white bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Perbaruhi</button>
                    <a href="/data-mahasiswa"
                        class="text-white mt-3 block sm:inline bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Kembali</a>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/editDataMahasiswa.js') }}"></script>
@endsection
