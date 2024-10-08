<div id="createJadwalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 transition-opacity">
    <div class="modal-dialog mt-20 p-3 bg-white rounded-md w-[90%] sm:w-[600px] mx-auto">
        <div class="modal-header">
            <h5 class="modal-title font-semibold">Buat Jadwal Piket</h5>
        </div>
        <div class="modal-body p-3">
            <form method="post" action="{{ route('admin.piketPetugasGenerateJadwal') }}">
                @csrf
                <div class="mb-3">
                    <label for="jadwalDate" class="block text-sm font-medium text-gray-700">Tanggal:</label>
                    <input type="date" id="jadwalDate" name="jadwalDate" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded focus:ring-utama focus:border-utama block w-full p-2">
                </div>
                <div class="mb-3">
                    <label for="petugas1" class="block text-sm font-medium text-gray-700">Pilih Petugas 1:</label>
                    <select id="petugas1" name="petugas1"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded focus:ring-utama focus:border-utama block w-full p-2">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="petugas2" class="block text-sm font-medium text-gray-700">Pilih Petugas 2:</label>
                    <select id="petugas2" name="petugas2"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded focus:ring-utama focus:border-utama block w-full p-2">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Tambahan elemen formulir atau input sesuai kebutuhan -->
                <div class="flex flex-col sm:flex-row justify-between mt-2">
                    <button type="submit"
                        class="bg-utama text-white rounded font-normal px-3 py-1.5 text-sm hover:bg-teal-800">
                        Buat
                    </button>
                    <button type="button" id="closeJadwalButton"
                        class="bg-red-500 text-white rounded font-normal px-3 py-1.5 mt-3 sm:mt-0 text-sm hover:bg-red-800">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
