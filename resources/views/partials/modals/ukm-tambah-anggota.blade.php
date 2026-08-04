<div id="modal-tambah-anggota" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tambah Anggota / Staf UKM
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                    data-modal-hide="modal-tambah-anggota">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form action="{{ route('admin.ukm.anggota.store', $ukm->id) }}" method="post" class="p-4 md:p-5">
                @csrf
                <div class="mb-4">
                    <label for="peran-select" class="block mb-2 text-sm font-medium text-gray-900">Peran</label>
                    <select name="peran" id="peran-select" onchange="toggleUserOptions(this.value)"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" required>
                        <option value="anggota">Anggota (Mahasiswa)</option>
                        <option value="pelatih">Pelatih (Staf)</option>
                        <option value="pembina">Pembina (Staf)</option>
                    </select>
                </div>
                <div class="mb-4" id="user-mahasiswa-container">
                    <label for="user_id_mhs" class="block mb-2 text-sm font-medium text-gray-900">Pilih Mahasiswa</label>
                    <select name="user_id" id="user_id_mhs"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach ($mahasiswas as $mhs)
                            <option value="{{ $mhs->id }}">{{ $mhs->name }} ({{ $mhs->nim ?? $mhs->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="user-staf-container">
                    <label for="user_id_staf" class="block mb-2 text-sm font-medium text-gray-900">Pilih Staf</label>
                    <select id="user_id_staf"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="">-- Pilih Staf --</option>
                        @foreach ($staf as $s)
                            <option value="{{ $s->id }}" data-role-id="{{ $s->role_id }}">{{ $s->name }} ({{ $s->role->name ?? 'Staf' }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="text-white inline-flex items-center bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    <i class="ri-add-line mr-1"></i> Tambahkan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Peran#role_id UKM: pelatih => 4 (PELATIH_ROLE_ID), pembina => 5 (PEMBINA_ROLE_ID)
    // (lihat App\Models\User) — dipakai untuk memfilter opsi search-dropdown staf.
    const UKM_PERAN_ROLE_ID_MAP = { pelatih: 4, pembina: 5 };
    let stafChoicesInstance = null;
    let stafChoicesAllOptions = [];

    function getStafChoices() {
        if (!stafChoicesInstance) {
            const stafSelect = document.getElementById('user_id_staf');
            stafChoicesAllOptions = Array.from(stafSelect.options)
                .filter((opt) => opt.value !== '')
                .map((opt) => ({
                    value: opt.value,
                    label: opt.textContent,
                    roleId: Number(opt.dataset.roleId),
                }));

            stafChoicesInstance = new Choices(stafSelect, {
                searchEnabled: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: '-- Cari Staf --',
            });
        }
        return stafChoicesInstance;
    }

    function toggleUserOptions(peran) {
        const mhsContainer = document.getElementById('user-mahasiswa-container');
        const stafContainer = document.getElementById('user-staf-container');
        const mhsSelect = document.getElementById('user_id_mhs');
        const stafSelect = document.getElementById('user_id_staf');

        if (peran === 'anggota') {
            mhsContainer.classList.remove('hidden');
            stafContainer.classList.add('hidden');
            mhsSelect.name = 'user_id';
            stafSelect.removeAttribute('name');
            return;
        }

        mhsContainer.classList.add('hidden');
        stafContainer.classList.remove('hidden');
        stafSelect.name = 'user_id';
        mhsSelect.removeAttribute('name');

        const choices = getStafChoices();
        const filteredRoleId = UKM_PERAN_ROLE_ID_MAP[peran];
        const filtered = stafChoicesAllOptions.filter((opt) => opt.roleId === filteredRoleId);

        choices.clearStore();
        choices.setChoices(
            filtered.map((opt) => ({ value: opt.value, label: opt.label })),
            'value',
            'label',
            true
        );
    }
</script>
