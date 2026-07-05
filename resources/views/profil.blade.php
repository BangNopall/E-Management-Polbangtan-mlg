@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Informasi Personal</h1>
        <div class="text-gray-600 text-sm">Perbarui foto dan detail personal</div>
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
        <div class="bg-white rounded-md border-2 border-gray-300 px-3 py-5 md:p-6">
            @if ($user->isAdmin() || $user->isOperator() || $user->isPelatih())
                <form action="{{ route('admin.editProfile', $user->id) }}" method="post" enctype="multipart/form-data">
                @else
                    <form action="{{ route('home.Editprofil', $user->id) }}" method="post" enctype="multipart/form-data">
            @endif
            @csrf
            <div class="flex justify-between items-center">
                <h1 class="font-semibold text-lg mdtext-xl text-gray-800 uppercase mb-6 mt-3">
                    <i class="ri-account-box-line text-xl md:text-2xl mr-1 md:mr-2"></i>Pengaturan Profil
                    @if ($user->role_id == 3)
                        <div class="text-red-500 text-xs font-medium">*Kesempatan merubah profil hanya berlaku 1x.</div>
                    @endif
                </h1>
                @include('partials.modals.profil')
                <button type="button"
                    class="text-white bg-utama hover:bg-teal-800 font-medium rounded-lg text-sm px-3 md:px-5 py-2.5 mr-2 mb-2"
                    id="profilModal" data-modal-target="profilModal" data-modal-toggle="profilModal">Simpan</button>
            </div>
            @error('foto_profil')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Foto Anda
                    <div class="text-sm font-normal text-gray-500">Akan ditampilkan di profil Anda</div>
                </div>
                <div class="flex flex-col items-center justify-center mt-3 md:mt-0">
                    <label for="foto-profil" class="cursor-pointer">
                        <div class="w-24 h-24 rounded-full">
                            @if ($user->image)
                                <img src="{{ asset('storage/images/' . $user->image) }}"
                                    class="w-full h-full object-cover rounded-full mb-2" id="fotoProfil" alt="Foto Profil">
                            @else
                                <img src="https://placehold.co/500x500" class="w-full h-full object-cover rounded-full mb-2"
                                    id="fotoProfil" alt="Foto Profil">
                            @endif
                        </div>
                        <input type="file" id="foto-profil" class="hidden" name="foto-profil" accept="image/*">
                    </label>
                    @include('partials.modals.fotohapus')
                    <button type="button" class="bg-red-500 p-1 rounded text-xs text-white mt-2 cursor-pointer" id="deleteButton"
                        data-modal-target="hapusfotoModal"
                        data-modal-toggle="hapusfotoModal">Hapus Foto</button>
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('nim')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Nomor Induk Mahasiswa
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <div class="text-text-md">ID</div>
                    </span>
                    <input type="number" id="nim" name="nim"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="012143" value="{{ $user->nim }}">
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('name')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Nama Lengkap
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-account-circle-fill text-md text-white"></i>
                    </span>
                    <input type="text" id="name" name="name"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="Suprianto Wijaya" value="{{ $user->name }}">
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('prodi_id')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Program Studi
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-bookmark-line text-md text-white"></i>
                    </span>
                    <select id="prodi_id" name="prodi_id"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5">
                        <option selected hidden>Pilih Program Studi</option>
                        @foreach ($prodis as $prodi)
                            <option value="{{ $prodi->id }}"
                                {{ old('prodi_id', $user->prodi_id) === $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->prodi }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('kelas_id')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Kelas
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-font-family text-md text-white"></i>
                    </span>
                    <select id="kelas_id" name="kelas_id"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5">
                        <option selected hidden>Pilih kelas</option>
                        @foreach ($kelas as $kelas)
                            <option value="{{ $kelas->id }}"
                                {{ old('kelas_id', $user->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('blok_ruangan_id')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Blok
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-font-family text-md text-white"></i>
                    </span>
                    <select id="blok_ruangan_id" name="blok_ruangan_id"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5">
                        <option selected hidden>Pilih Blok</option>
                        @foreach ($blocks as $block)
                            <option value="{{ $block->id }}"
                                {{ old('blok_ruangan_id', $user->blok_ruangan_id) == $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('no_kamar')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Nomor kamar
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-file-list-line text-md text-white"></i>
                    </span>
                    <input type="number" id="no_kamar" name="no_kamar"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="10" value="{{ $user->no_kamar }}">
                </div>
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('asal_daerah')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row mb-4">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Asal Daerah
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-home-3-line text-md text-white"></i>
                    </span>
                    <input type="text" id="asal_daerah" name="asal_daerah"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="Kota Malang" value="{{ $user->asal_daerah }}">
                </div>
            </div>
            </form>
        </div>

        <div class="bg-white rounded-md border-2 border-gray-300 px-3 py-5 md:p-6 mt-10">
            @if (session()->has('success-email'))
                <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200"
                    role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div class="ml-3 text-sm font-medium">
                        {{ session('success-email') }}
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
            @if (session()->has('error-email'))
                <div id="alert-2" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-200" role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div class="ml-3 text-sm font-medium">
                        {{ session('error-email') }}
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
            @if ($user->isAdmin() || $user->isOperator() || $user->isPelatih())
                <form action="{{ route('admin.editProfileGmail', $user->id) }}" method="post">
                @else
                    <form action="{{ route('home.EditprofilGmail', $user->id) }}" method="post">
            @endif
            @csrf
            <div class="flex justify-between items-center">
                <h1 class="font-semibold text-lg md:text-xl text-gray-800 uppercase mb-6 mt-3">
                    <i class="ri-account-box-line text-xl md:text-2xl mr-1 md:mr-2"></i>Pengaturan Akun
                </h1>
                <button type="submit"
                    class="text-white bg-utama hover:bg-teal-800 font-medium rounded-lg text-sm px-3 md:px-5 py-2.5 mr-2 mb-2">Simpan</button>
            </div>
            @error('no_hp')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Nomor Handphone
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-smartphone-line text-md text-white"></i>
                    </span>
                    <input type="number" id="no_hp" name="no_hp"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="08xxxx" value="{{ $user->no_hp }}">
                </div>

            </div>
            <div class="border-b border-gray-300 my-3"></div>
            @error('email')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Email
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-mail-line text-md text-white"></i>
                    </span>
                    <input type="email" id="email" name="email"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="example@gmail.com" value="{{ $user->email }}">
                </div>
            </div>

            <div class="border-b border-gray-300 my-3"></div>
            @error('password')
                <span class="text-red-500 text-sm mt-1 text-right">{{ $message }}</span>
            @enderror
            <div class="flex flex-col md:flex-row mb-4">
                <div class="text-md text-gray-600 font-medium w-auto md:w-[400px]">
                    Reset Password
                </div>
                <div class="flex flex-col md:flex-row w-auto md:w-[500px] mt-1 md:mt-0 relative">
                    <span
                        class="inline-flex items-center w-10 md:w-auto px-3 text-sm text-white bg-teal-900 border border-r-1 md:border-r-0 border-utama md:rounded-tl-md md:rounded-t-none rounded-t-md md:rounded-l-md">
                        <i class="ri-lock-2-line text-md text-white"></i>
                    </span>
                    <input type="password" name="password"
                        class="rounded-none rounded-r-lg rounded-bl-lg md:rounded-bl-none bg-utama border-teal-900 text-gray-100 focus:ring-teal-500 focus:border-teal-500 block flex-1 min-w-0 w-full text-sm p-2.5"
                        placeholder="New Password" id="passwordInput">
                    <button id="togglePassword" type="button"
                        class="absolute top-5 md:top-0 inset-y-0 right-0 pr-3 flex items-center text-white hover:text-gray-300 cursor-pointer">
                        <i id="showEye" class="ri-eye-line text-lg"></i>
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/profil.js') }}"></script>
    <script src="{{ asset('js/library/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            function deleteFoto() {
                var userId = {{ $user->id }};
                var roleId = {{ $user->role_id }};

                var formAction = (roleId === 3) ? "{{ route('home.deleteFotoProfile', ':id') }}" :
                    "{{ route('admin.deleteFotoProfile', ':id') }}";
                formAction = formAction.replace(':id', userId);

                // Create a form dynamically
                var form = document.createElement('form');
                form.id = 'deleteFotoForm';
                form.method = 'post';
                form.action = formAction;

                // Add CSRF token input
                var csrfTokenInput = document.createElement('input');
                csrfTokenInput.type = 'hidden';
                csrfTokenInput.name = '_token';
                csrfTokenInput.value = '{{ csrf_token() }}';

                // Append the CSRF token input to the form
                form.appendChild(csrfTokenInput);

                // Append the form to the document body
                document.body.appendChild(form);

                // Submit the form
                form.submit();
            }

            // Attach the click event to your HTML element
            $('#deleteFotoButton').on('click', function(event) {
                event.preventDefault();
                deleteFoto();
            });
        });
    </script>
@endsection
