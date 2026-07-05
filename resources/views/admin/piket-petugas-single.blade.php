@extends('layouts.main')
@section('container')
<div class="px-3 py-6 md:p-6 mb-5">
    <h1 class="font-semibold text-2xl md:text-3xl mb-3">Edit Piket Petugas</h1>
    <div class="text-gray-600 text-sm">Form edit piket petugas tanggal <span class="text-md font-semibold">{{{ \Carbon\Carbon::parse($jadwal->date)->format('d F Y') }}}</span></div>
    <div class="border-b border-gray-300 my-5"></div>
    <div class="bg-white rounded-lg p-5 mt-5 border-2 w-full mx-auto sm:w-[400px]">
            <form action="{{ route('admin.updatePiketPetugas', ['id' => $jadwal->id]) }}" method="POST">
                @csrf
                <div class="relative z-0 w-full mb-6 group">
                    <label for="petugas1" class="block mb-2 text-sm font-medium text-gray-500">Petugas 1</label>
                    <select id="petugas1" name="petugas1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded focus:ring-teal-500 focus:border-teal-500 block w-full p-2 text-center" required>
                        <option value="{{ $jadwal->petugas1 ? $jadwal->petugas1->id : null }}" hidden selected>
                            {{ $jadwal->petugas1 ? $jadwal->petugas1->name : '-' }}
                        </option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ $jadwal->petugas1 && $jadwal->petugas1->id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="relative z-0 w-full mb-6 group">
                    <label for="petugas2" class="block mb-2 text-sm font-medium text-gray-500">Petugas 2</label>
                    <select id="petugas2" name="petugas2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded focus:ring-teal-500 focus:border-teal-500 block w-full p-2 text-center" required>
                        <option value="{{ $jadwal->petugas2 ? $jadwal->petugas2->id : null }}" hidden selected>
                            {{ $jadwal->petugas2 ? $jadwal->petugas2->name : '-' }}
                        </option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ $jadwal->petugas2 && $jadwal->petugas2->id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="text-white bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded text-sm w-full sm:w-auto px-5 py-2.5 text-center">Simpan</button>
                    <a href="{{ route('admin.piketPetugas') }}"
                    class="text-white mt-3 block sm:inline bg-utama hover:bg-teal-800 focus:ring-2 focus:outline-none focus:ring-teal-300 font-medium rounded text-sm w-full sm:w-auto px-5 py-2.5 text-center">Kembali</a>
            </form>
    </div>
</div>
@endsection
