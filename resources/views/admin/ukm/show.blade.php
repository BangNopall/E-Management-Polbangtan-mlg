@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="flex items-center gap-2 mb-3">
            <a href="{{ route('admin.ukm.index') }}" class="text-teal-700 hover:underline flex items-center text-sm">
                <i class="ri-arrow-left-line"></i> Kembali ke Manajemen UKM
            </a>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-3">
            <div>
                <h1 class="font-semibold text-2xl md:text-3xl">{{ $ukm->nama }}</h1>
                <div class="text-gray-600 text-sm mt-1">{{ $ukm->deskripsi ?? 'Tidak ada deskripsi' }}</div>
            </div>
            <div>
                @if ($ukm->is_active)
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded">Status: Aktif</span>
                @else
                    <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded">Status: Nonaktif</span>
                @endif
            </div>
        </div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        {{-- Tabs --}}
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="ukmTab" data-tabs-toggle="#ukmTabContent" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="anggota-tab" data-tabs-target="#anggota" type="button" role="tab" aria-controls="anggota" aria-selected="true">
                        <i class="ri-user-line mr-1"></i> Anggota ({{ $ukm->members->count() }})
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="jadwal-tab" data-tabs-target="#jadwal" type="button" role="tab" aria-controls="jadwal" aria-selected="false">
                        <i class="ri-calendar-event-line mr-1"></i> Jadwal ({{ $ukm->jadwals->count() }})
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="rekap-tab" data-tabs-target="#rekap" type="button" role="tab" aria-controls="rekap" aria-selected="false">
                        <i class="ri-checkbox-circle-line mr-1"></i> Rekap Presensi
                    </button>
                </li>
            </ul>
        </div>

        <div id="ukmTabContent">
            {{-- Tab 1: Anggota --}}
            <div class="p-4 rounded-lg bg-white border-2" id="anggota" role="tabpanel" aria-labelledby="anggota-tab">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold text-lg text-gray-800">Daftar Anggota UKM</h2>
                    <button data-modal-target="modal-tambah-anggota" data-modal-toggle="modal-tambah-anggota"
                        class="bg-utama text-white text-xs px-3 py-2 rounded hover:bg-teal-700 font-medium flex items-center gap-1">
                        <i class="ri-user-add-line"></i> Tambah Anggota / Staf
                    </button>
                </div>

                @include('admin.ukm.partials.anggota_table')
            </div>

            {{-- Tab 2: Jadwal --}}
            <div class="hidden p-4 rounded-lg bg-white border-2" id="jadwal" role="tabpanel" aria-labelledby="jadwal-tab">
                <div class="w-full bg-white border rounded-lg p-3 mb-5">
                    <div id="accordion-jadwal" data-accordion="collapse" data-active-classes="bg-white text-gray-900"
                        data-inactive-classes="text-gray-500">
                        <h2 id="accordion-jadwal-heading">
                            <button type="button"
                                class="flex items-center font-normal justify-between gap-5 py-2 text-gray-500 border-b border-gray-200 w-full"
                                data-accordion-target="#accordion-jadwal-body" aria-expanded="false"
                                aria-controls="accordion-jadwal-body">
                                <span class="font-medium text-gray-700">Buat Jadwal Kegiatan UKM</span>
                                <i data-accordion-icon aria-hidden="true" class="ri-add-box-line text-md shrink-0"></i>
                            </button>
                        </h2>
                        <div id="accordion-jadwal-body" class="hidden" aria-labelledby="accordion-jadwal-heading">
                            <div class="mt-3">
                                <form action="{{ route('admin.ukm.jadwal.store', $ukm->id) }}" method="post">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label for="judul" class="block mb-1 font-medium text-gray-700 text-sm">Judul Kegiatan <span class="text-red-500">*</span></label>
                                            <input type="text" name="judul" id="judul"
                                                class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm"
                                                placeholder="Contoh: Latihan Rutin Mingguan" required>
                                        </div>
                                        <div>
                                            <label for="jenis" class="block mb-1 font-medium text-gray-700 text-sm">Jenis Kegiatan <span class="text-red-500">*</span></label>
                                            <select name="jenis" id="jenis"
                                                class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm" required>
                                                <option value="latihan">Latihan</option>
                                                <option value="kegiatan_wajib">Kegiatan Wajib</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                        <div>
                                            <label for="tanggal" class="block mb-1 font-medium text-gray-700 text-sm">Tanggal <span class="text-red-500">*</span></label>
                                            <input type="date" name="tanggal" id="tanggal"
                                                class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm" required>
                                        </div>
                                        <div>
                                            <label for="mulai_acara" class="block mb-1 font-medium text-gray-700 text-sm">Mulai Acara <span class="text-red-500">*</span></label>
                                            <input type="time" name="mulai_acara" id="mulai_acara"
                                                class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm" required>
                                        </div>
                                        <div>
                                            <label for="selesai_acara" class="block mb-1 font-medium text-gray-700 text-sm">Selesai Acara <span class="text-red-500">*</span></label>
                                            <input type="time" name="selesai_acara" id="selesai_acara"
                                                class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="lokasi" class="block mb-1 font-medium text-gray-700 text-sm">Lokasi Kegiatan</label>
                                        <input type="text" name="lokasi" id="lokasi"
                                            class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm"
                                            placeholder="Contoh: Lapangan Utama / GOR Polbangtan">
                                    </div>
                                    <div>
                                        <button class="bg-utama rounded text-sm py-2 px-4 text-white hover:bg-teal-700 font-medium" type="submit">
                                            Simpan & Presensi Fan-Out Anggota
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-semibold text-lg text-gray-800">Daftar Jadwal Kegiatan UKM</h2>
                    <div class="flex items-center gap-2">
                        <button type="button" id="btn-toggle-tabel" onclick="toggleJadwalView('tabel')"
                            class="px-3 py-1.5 text-xs font-medium rounded border bg-teal-700 text-white">
                            <i class="ri-table-line"></i> Tabel
                        </button>
                        <button type="button" id="btn-toggle-kalender" onclick="toggleJadwalView('kalender')"
                            class="px-3 py-1.5 text-xs font-medium rounded border bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="ri-calendar-line"></i> Kalender
                        </button>
                    </div>
                </div>

                <div id="view-jadwal-tabel">
                    @include('admin.ukm.partials.jadwal_table')
                </div>

                <div id="view-jadwal-kalender" class="hidden">
                    <div id="calendar" class="p-2 border rounded bg-white min-h-[400px]"></div>
                </div>
            </div>

            {{-- Tab 3: Rekap Presensi --}}
            <div class="hidden p-4 rounded-lg bg-white border-2" id="rekap" role="tabpanel" aria-labelledby="rekap-tab">
                @include('admin.ukm.partials.rekap_presensi_table')
            </div>
        </div>
    </div>

    @include('partials.modals.ukm-tambah-anggota')
    @include('partials.modals.ukm-detail-kegiatan')

    <script src="{{ asset('js/library/index.global.min.js') }}" type="text/javascript"></script>
    <script>
        let ukmCalendarInstance = null;

        function toggleJadwalView(viewMode) {
            const tabelView = document.getElementById('view-jadwal-tabel');
            const kalenderView = document.getElementById('view-jadwal-kalender');
            const btnTabel = document.getElementById('btn-toggle-tabel');
            const btnKalender = document.getElementById('btn-toggle-kalender');

            if (viewMode === 'tabel') {
                tabelView.classList.remove('hidden');
                kalenderView.classList.add('hidden');
                btnTabel.className = 'px-3 py-1.5 text-xs font-medium rounded border bg-teal-700 text-white';
                btnKalender.className = 'px-3 py-1.5 text-xs font-medium rounded border bg-gray-100 text-gray-700 hover:bg-gray-200';
            } else {
                tabelView.classList.add('hidden');
                kalenderView.classList.remove('hidden');
                btnKalender.className = 'px-3 py-1.5 text-xs font-medium rounded border bg-teal-700 text-white';
                btnTabel.className = 'px-3 py-1.5 text-xs font-medium rounded border bg-gray-100 text-gray-700 hover:bg-gray-200';
                initUkmCalendar();
            }
        }

        function initUkmCalendar() {
            if (ukmCalendarInstance) {
                return;
            }

            fetch('{{ route('admin.ukm.jadwal.events', $ukm->id) }}')
                .then((res) => res.json())
                .then((events) => {
                    const el = document.getElementById('calendar');
                    ukmCalendarInstance = new FullCalendar.Calendar(el, {
                        height: 450,
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next',
                            center: 'title',
                            right: '',
                        },
                        events: events,
                        eventClick: function(info) {
                            if (info.event.extendedProps) {
                                openModalDetailKegiatan({
                                    judul: info.event.title,
                                    jenis: info.event.extendedProps.jenis,
                                    tanggal: info.event.extendedProps.tanggal,
                                    mulai_acara: info.event.extendedProps.mulai_acara,
                                    selesai_acara: info.event.extendedProps.selesai_acara,
                                    lokasi: info.event.extendedProps.lokasi,
                                    status_verifikasi: info.event.extendedProps.status_verifikasi,
                                    catatan_pembina: info.event.extendedProps.catatan_pembina
                                });
                            }
                        },
                        eventContent: function (arg) {
                            const div = document.createElement('div');
                            div.innerHTML = arg.event.title;
                            div.className = 'px-1 text-white text-center text-sm';
                            div.classList.add('bg-teal-600');
                            return { domNodes: [div] };
                        },
                    });
                    ukmCalendarInstance.render();
                });
        }
    </script>
@endsection
