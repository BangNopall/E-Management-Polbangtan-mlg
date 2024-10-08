<div
    class="fixed left-0 top-0 w-64 bg-teal-800 py-4 px-0 z-30 sidebar-menu transition-transform -translate-x-full md:-translate-x-0 h-full">
    <div class="px-3">
        <a href="/" class="flex items-center pb-4 border-b border-b-teal-900">
            <img src="{{ asset('img/logo-asrama2.jpeg') }}" alt="" class="w-8 h-8 rounded object-cover">
            <span class="text-md font-bold text-white ml-1.5">Management Asrama</span>
        </a>
    </div>
    <div class="inset-0 h-full overflow-y-visible lg:h-auto">
        <div class="overflow-y-auto h-[90vh]">
            <ul class="mt-1">
                @if (Auth::check() && Auth::user()->role_id == '3')
                    <li class="mb-1 group active">
                        <a href="{{ route('home.index') }}"
                            class="{{ Request::is('dashboard') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama' }} flex items-center px-3 py-1">
                            <i class="ri-home-2-line mr-3 text-lg"></i>
                            <span class="text-sm">Dashboard</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="{{ route('home.kodeqr') }}"
                            class="{{ Request::is('dashboard/kode-qr') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama' }} flex items-center px-3 py-1">
                            <i class="ri-qr-code-line mr-3 text-lg"></i>
                            <span class="text-sm">QR Absen</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="{{ route('home.profilshow', ['id' => auth()->id()]) }}"
                            class="{{ Request::is('dashboard/profil') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama' }} flex items-center px-3 py-1">
                            <i class="ri-user-line mr-3 text-lg"></i>
                            <span class="text-sm">Profil</span>
                        </a>
                    </li>
                    <li class="group mb-1">
                        <button type="button"
                            class="flex items-center w-full px-3 py-1 text-gray-300 transition duration-75 group hover:bg-utama text-md"
                            aria-controls="absen" data-collapse-toggle="absen">
                            <i class="ri-history-line text-lg font-medium"></i>
                            <span class="flex-1 ms-3 text-left text-sm whitespace-nowrap">Riwayat Absen</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <ul id="absen"
                            class="{{ Request::is('dashboard/riwayat-absen', 'dashboard/riwayat-aktivitas') ? 'block' : 'hidden' }} py-1 space-y-1">
                            <li>
                                <a href="{{ route('home.riwayatindex') }}"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('dashboard/riwayat-absen') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Keluar Asrama</a>
                            </li>
                            <li>
                                <a href="/dashboard/riwayat-aktivitas"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm {{ Request::is('dashboard/riwayat-aktivitas') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Kegiatan Wajib</a>
                            </li>
                        </ul>
                    </li>
                    <li class="group">
                        <button type="button"
                            class="flex items-center w-full px-3 py-1 text-gray-300 transition duration-75 group hover:bg-utama text-md"
                            aria-controls="pelanggaran" data-collapse-toggle="pelanggaran">
                            <i class="ri-file-warning-line text-lg font-medium"></i>
                            <span class="flex-1 ms-3 text-left text-sm whitespace-nowrap">Pelanggaran</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <ul id="pelanggaran"
                            class="{{ Request::is('dashboard/qr-hukum', 'dashboard/riwayat-pelanggaran', 'dashboard/riwayat-pelanggaran/*') ? 'block' : 'hidden' }} py-1 space-y-1">
                            <li>
                                <a href="{{ route('home.qrhukum') }}"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('dashboard/qr-hukum') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    QR Pelanggaran</a>
                            </li>
                            <li>
                                <a href="/dashboard/riwayat-pelanggaran"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm {{ Request::is('dashboard/riwayat-pelanggaran', 'dashboard/riwayat-pelanggaran/*') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Riwayat Pelanggaran</a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (Auth::check() && (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 4))
                    <li class="mb-1 group mt-1">
                        <a href="{{ route('admin.index') }}"
                            class="{{ Request::is('dashboard-admin') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama hover:text-gray-100' }} flex items-center px-3 py-1">
                            <i class="ri-home-2-line mr-3 text-lg"></i>
                            <span class="text-sm">Dashboard Admin</span>
                        </a>
                    </li>
                    {{-- <li class="mb-1 group">
                        <a href="{{ route('admin.absensiMahasiswa') }}"
                            class="{{ Request::is('absensi-mahasiswa') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama hover:text-gray-100' }} flex items-center px-3 py-1">
                            <i class="ri-survey-line mr-3 text-lg"></i>
                            <span class="text-sm">Absensi Mahasiswa</span>
                        </a>
                    </li> --}}
                    <li class="mb-1 group">
                        <a href="/data-absen-keluar"
                        class="{{ Request::is('data-absen-keluar', 'data-absen-keluar/*') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama hover:text-gray-100' }} flex items-center px-3 py-1">
                        <i class="ri-survey-line mr-3 text-lg"></i>
                            <span class="text-sm">Data Absen Keluar</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="{{ route('admin.dataPetugas') }}"
                            class="{{ Request::is('data-petugas', 'data-petugas/*') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama hover:text-gray-100' }} flex items-center px-3 py-1">
                            <i class="ri-admin-line mr-3 text-lg"></i>
                            <span class="text-sm">Data Petugas</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="{{ route('admin.piketPetugas') }}"
                            class="{{ Request::is('piket-petugas') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama hover:text-gray-100' }} flex items-center px-3 py-1">
                            <i class="ri-bubble-chart-line mr-3 text-lg"></i>
                            <span class="text-sm">Piket Petugas</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="{{ route('admin.kamera') }}"
                            class="{{ Request::is('kamera-scan') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama hover:text-gray-100' }} flex items-center px-3 py-1">
                            <i class="ri-camera-line mr-3 text-lg"></i>
                            <span class="text-sm">Scan Absen Keluar</span>
                        </a>
                    </li>
                    <li class="group mb-1 mt-1">
                        <button type="button"
                            class="flex items-center w-full px-3 py-1 text-gray-300 transition duration-75 group hover:bg-utama text-md"
                            aria-controls="admin-kegiatanwajib" data-collapse-toggle="admin-kegiatanwajib">
                            <i class="ri-star-line text-lg font-medium"></i>
                            <span class="flex-1 ms-3 text-left text-sm whitespace-nowrap">Kegiatan Wajib</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <ul id="admin-kegiatanwajib"
                            class="{{ Request::is('kamera-upacara', 'kamera-apel', 'kamera-senam', 'jadwal-kegiatan', 'data-kegiatan-wajib', 'data-kegiatan-wajib/*') ? 'block' : 'hidden' }} py-1 space-y-1">
                            <li>
                                <a href="/kamera-upacara"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('kamera-upacara') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Scan QR Upacara</a>
                            </li>
                            <li>
                                <a href="/kamera-apel"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('kamera-apel') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Scan QR Apel</a>
                            </li>
                            <li>
                                <a href="/kamera-senam"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('kamera-senam') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Scan QR Senam</a>
                            </li>
                            <li>
                                <a href="/jadwal-kegiatan"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('jadwal-kegiatan') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Jadwal Kegiatan</a>
                            </li>
                            <li>
                                <a href="/data-kegiatan-wajib"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('data-kegiatan-wajib', 'data-kegiatan-wajib/*') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Data Absen Kegiatan</a>
                            </li>
                        </ul>
                    </li>
                    <li class="group mb-1 mt-1">
                        <button type="button"
                            class="flex items-center w-full px-3 py-1 text-gray-300 transition duration-75 group hover:bg-utama text-md"
                            aria-controls="admin-pelanggaran" data-collapse-toggle="admin-pelanggaran">
                            <i class="ri-file-warning-line text-lg font-medium"></i>
                            <span class="flex-1 ms-3 text-left text-sm whitespace-nowrap">Pelanggaran</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <ul id="admin-pelanggaran"
                            class="{{ Request::is('kamera-pelatih', 'edit-pelanggaran', 'laporan-pelanggaran', 'laporan-pelanggaran/*', 'data-pelanggaran', 'data-pelanggaran/*', 'edit-pelanggaran/*') ? 'block' : 'hidden' }} py-1 space-y-1">
                            <li>
                                <a href="{{ route('admin.scanCamPelatih') }}"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm  {{ Request::is('kamera-pelatih') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Scan QR Pelanggaran</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.laporanPelanggaran') }}"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm {{ Request::is('laporan-pelanggaran', 'laporan-pelanggaran/*') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Laporan Pelanggaran</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.dataPelanggaran') }}"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm {{ Request::is('data-pelanggaran', 'data-pelanggaran/*') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Data Pelanggaran</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.editPelanggaranIdKategori', 1) }}"
                                    class="flex items-center w-full px-3 py-1 transition duration-75 pl-6 group text-sm {{ Request::is('edit-pelanggaran', 'edit-pelanggaran/*') ? 'bg-utama text-white' : 'text-gray-300 hover:bg-utama' }}">•
                                    Edit Pelanggaran</a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (Auth::check() && Auth::user()->role_id == 1)
                    <div class="mb-3 mt-1">
                        <h6
                            class="md:min-w-full text-white text-sm uppercase font-bold block pt-1 px-2 mt-3 no-underline">
                            Admin Privilege Pages
                        </h6>
                    </div>

                    <li class="mb-1 group">
                        <a href="/data-mahasiswa"
                            class="{{ Request::is('data-mahasiswa') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama' }} flex items-center px-3 py-1">
                            <i class="ri-database-2-line mr-3 text-lg"></i>
                            <span class="text-sm">Data Mahasiswa</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="/generate-laporan"
                            class="{{ Request::is('generate-laporan') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama' }} flex items-center px-3 py-1">
                            <i class="ri-file-list-3-line mr-3 text-lg"></i>
                            <span class="text-sm">Export Laporan</span>
                        </a>
                    </li>
                    <li class="mb-1 group">
                        <a href="/sistem-admin"
                            class="{{ Request::is('sistem-laporan') ? 'text-white bg-utama' : 'text-gray-300 hover:bg-utama' }} flex items-center px-3 py-1">
                            <i class="ri-settings-2-line mr-3 text-lg"></i>
                            <span class="text-sm">Sistem Admin</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<div class="fixed top-0 left-0 w-full h-full bg-black/50 z-20 hidden md:hidden sidebar-overlay"></div>
