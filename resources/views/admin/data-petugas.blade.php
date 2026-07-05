@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Data Petugas</h1>
        <div class="text-gray-600 text-sm">Administrator petugas Management Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="bg-white rounded-lg p-3 mt-5 border-2">
            {{-- alert --}}
            @if (session()->has('success'))
                <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200" role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
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
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="">
                    <div class="text-gray-900 text-lg font-medium">Daftar Petugas</div>
                    <div class="text-gray-600 text-sm mt-1">Berikut daftar petugas Asrama Polbangtan-mlg</div>
                </div>
                @if (auth()->user()->role_id == 1)
                    <div class="mt-5 md:mt-0">
                        <a href="{{ route('admin.createPetugasShow') }}"
                            class="bg-utama hover:bg-teal-800 text-white rounded px-3 py-2 text-sm">
                            <i class="ri-add-line text-md mr-1"></i>Tambah Petugas
                        </a>
                    </div>
                @endif
            </div>
            <div class="border-b border-gray-300 my-3"></div>
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase  bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                No
                            </th>
                            <th>
                                Profile
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Nama
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($petugas as $index => $user)
                            <tr class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $index + 1 }}
                                </th>
                                <td>
                                    @if ($user->image)
                                        <img src="{{ asset('storage/images/' . $user->image) }}"
                                            class="rounded-full w-9 h-9 md:w-12 md:h-12" id="fotoProfil" alt="Foto Profil">
                                    @else
                                        <img src="https://placehold.co/36x36" class="rounded-full w-9 h-9 md:w-12 md:h-12"
                                            id="fotoProfil" alt="Foto Profil">
                                    @endif
                                </td>
                                <td class="px-6 py-4 min-w-[180px]">
                                    {{ $user->name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->roleId->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-1">
                                        @if (auth()->user()->role_id == 1 ||
                                                (auth()->user()->role_id == 2 && auth()->user()->id == $user->id) ||
                                                (auth()->user()->role_id == 4 && auth()->user()->id == $user->id))
                                            <a href="{{ route('admin.editDataPetugasShow', $user->id) }}"
                                                class="bg-utama text-white rounded p-2">Edit</a>
                                            <form action="{{ route('admin.destroyPetugas', $user->id) }}" method="post">
                                                @method('DELETE')
                                                @csrf
                                                @include('partials.modals.hapusdata')
                                                <button type="button" class="bg-red-500 text-white rounded p-2" id="deleteButton" data-modal-target="hapusdataModal{{ $user->id }}" data-modal-toggle="hapusdataModal{{ $user->id }}">Hapus</button>
                                            </form>
                                        @else
                                        <div class="bg-gray-400 text-gray-600 rounded p-2 cursor-not-allowed">Edit</div>
                                        <div class="bg-gray-400 text-gray-600 rounded p-2 cursor-not-allowed">Hapus</div>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
