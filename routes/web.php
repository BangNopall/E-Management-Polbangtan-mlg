<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QRController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KonselingHandoffController;
use App\Http\Controllers\AbsensiMahasiswa;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QRControllerHukum;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QRControllerKegiatan;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\kegiatanAsramaController;
use App\Http\Controllers\UkmController;
use App\Http\Controllers\UkmMemberController;
use App\Http\Controllers\UkmJadwalController;
use App\Http\Controllers\UkmScanController;
use App\Http\Controllers\UkmVerifikasiController;
use App\Http\Controllers\UkmLaporanController;
use App\Http\Controllers\Admin\PejabatController;

use App\Http\Controllers\UkmMahasiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// PUBLIC VERIFICATION ROUTE (EPIC 03 - ADR-008 LARAVEL SIGNED URL)
Route::get('/verifikasi-izin/{qr_token}', [\App\Http\Controllers\VerifikasiIzinController::class, 'show'])
    ->middleware('signed')
    ->name('publik.verifikasi.izin');
Route::get('/verifikasi-izin/{qr_token}/konfirmasi-tiba', [\App\Http\Controllers\KonfirmasiTibaController::class, 'show'])
    ->middleware('signed')
    ->name('publik.konfirmasi.tiba.show');
Route::post('/verifikasi-izin/{qr_token}/konfirmasi-tiba', [\App\Http\Controllers\KonfirmasiTibaController::class, 'store'])
    ->middleware('signed')
    ->name('publik.konfirmasi.tiba.store');

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    // LOGIN ROUTE
    Route::get('/login', [AuthController::class, 'index'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', [AuthController::class, 'authDashboard'])->name('auth.dashboard');
    Route::get('/home', [HomeController::class, 'index'])->name('rumah');
    Route::get('/profil', [ProfileController::class, 'index'])->name('user.profil');

    // ROUTE SINGGLE START
    Route::middleware('role:user')->name('home.')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('index');
        Route::get('/dashboard/profil', [ProfileController::class, 'profil'])->name('profilshow');
        Route::get('/get-presence-date', [HomeController::class, 'getPresenceDate'])->name('get-presence-date');
        Route::post('/dashboard/profil/{id}', [ProfileController::class, 'editProfile'])->name('Editprofil');
        Route::post('/dashboard/profil-gmail/{id}', [ProfileController::class, 'editProfileGmail'])->name('EditprofilGmail');
        Route::get('/dashboard/kode-qr', [QRController::class, 'kodeqr'])->name('kodeqr');
        Route::get('/dashboard/riwayat-absen', [HomeController::class, 'riwayat'])->name('riwayatindex');
        Route::get('/dashboard/qr-hukum', [QRControllerHukum::class, 'qrhukum'])->name('qrhukum');
        Route::get('/dashboard/formhukum/{kategori_id}', [PelanggaranController::class, 'formHukumShow'])->name('formHukumShow');
        Route::post('/dashboard/formsubmit/{user_id}', [PelanggaranController::class, 'formHukumKategoriSubmit'])->name('formHukumKategoriSubmit');
        Route::get('/dashboard/riwayat-pelanggaran', [PelanggaranController::class, 'riwayatPelanggaran'])->name('riwayatPelanggaran');
        Route::get('/dashboard/riwayat-pelanggaran/detail/{id}', [PelanggaranController::class, 'riwayatPelanggaranDetail'])->name('riwayatPelanggaranDetail');
        Route::get('/dashboard/riwayat-aktivitas', [kegiatanAsramaController::class, 'riwayatAktivitasShow'])->name('riwayatAktivitasShow');
        Route::post('/dashboard/delete-foto/{user_id}', [ProfileController::class, 'deleteFotoProfile'])->name('deleteFotoProfile');
        Route::get('/handoff/konseling', [KonselingHandoffController::class, 'redirect'])->name('konseling');

        // EPIC 01: MODUL UKM DINAMIS — Student Routes (US 1.3)
        Route::get('/dashboard/ukm', [UkmMahasiswaController::class, 'index'])->name('ukm.index');
        Route::get('/dashboard/ukm/riwayat', [UkmMahasiswaController::class, 'riwayat'])->name('ukm.riwayat');
        Route::get('/dashboard/riwayat-ukm', [UkmMahasiswaController::class, 'riwayatUkm'])->name('ukm.riwayatAbsen');

        // EPIC 03: MODUL PERIZINAN — Student Routes (M2a)
        Route::get('/dashboard/izin', [\App\Http\Controllers\IzinMahasiswaController::class, 'index'])->name('izin.index');
        Route::get('/dashboard/izin/create', [\App\Http\Controllers\IzinMahasiswaController::class, 'create'])->name('izin.create');
        Route::get('/dashboard/izin/pratinjau-alur', [\App\Http\Controllers\IzinMahasiswaController::class, 'pratinjauAlur'])->name('izin.pratinjau-alur');
        Route::post('/dashboard/izin', [\App\Http\Controllers\IzinMahasiswaController::class, 'store'])->name('izin.store');
        Route::get('/dashboard/izin/{pengajuan}', [\App\Http\Controllers\IzinMahasiswaController::class, 'show'])->name('izin.show');
        Route::get('/dashboard/izin/{pengajuan}/pdf', [\App\Http\Controllers\IzinMahasiswaController::class, 'downloadPdf'])->name('izin.pdf');
        Route::post('/dashboard/izin/{pengajuan}/batal', [\App\Http\Controllers\IzinMahasiswaController::class, 'batal'])->name('izin.batal');
    });

    Route::middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/data-mahasiswa', [DashboardController::class, 'dataMahasiswa'])->name('dataMahasiswa');
        Route::post('/data-mahasiswa/search-blokruangan', [DashboardController::class, 'searchMahasiswaByBlokRuangan'])->name('searchMahasiswaByBlokRuangan');
        Route::post('/data-mahasiswa/get-nomor-ruangan', [DashboardController::class, 'getNomorRuangan'])->name('getNomorRuangan');
        Route::post('/data-mahasiswa/search-mahasiswa-by-data', [DashboardController::class, 'searchMahasiswaByData'])->name('searchMahasiswaByData');
        Route::post('/data-mahasiswa/searchbar-mahasiswa', [DashboardController::class, 'searchMahasiswa'])->name('searchMahasiswa');
        Route::get('/data-mahasiswa/edit/{id}', [DashboardController::class, 'dataMahasiswaEdit'])->name('dataMahasiswaEdit');
        Route::post('/data-mahasiswa/edit/{id}', [DashboardController::class, 'dataMahasiswaEditData'])->name('dataMahasiswaEditData');
        Route::post('/data-mahasiswa/edit/resetstatus/{id}', [DashboardController::class, 'dataMahasiswaEditDataResetStatus'])->name('dataMahasiswaEditDataResetStatus');
        Route::post('/data-mahasiswa/edit/resetlogin/{id}', [DashboardController::class, 'dataMahasiswaEditDataResetLogin'])->name('dataMahasiswaEditDataResetLogin');
        Route::delete('/data-mahasiswa/{id}', [DashboardController::class, 'dataMahasiswaDestroy'])->name('dataMahasiswaDestroy');
        Route::post('/delete-foto-mahasiswa/{user_id}', [ProfileController::class, 'deleteFotoProfile'])->name('deleteFotoProfileMahasiswa');

        Route::get('sistem-admin', [DashboardAdminController::class, 'showSistemAdmin'])->name('sistem-admin');
        Route::get('template-excel', [DashboardAdminController::class, 'downloadExcelTemplate'])->name('downloadExcelTemplate');
        Route::post('sistem-admin/importclass', [DashboardAdminController::class, 'importClassSistemAdmin'])->name('sistemAdminImportClass');
        Route::post('sistem-admin/upgradeclass', [DashboardAdminController::class, 'upgradeClassSistemAdmin'])->name('sistemAdminUpgradeClass');

        Route::get('/generate-laporan', [GenerateReportController::class, 'index'])->name('generate');
        Route::post('/generate-laporan', [GenerateReportController::class, 'pdfReport'])->name('generatepPdf');
        Route::post('/generate-laporan-pelanggaran', [GenerateReportController::class, 'generateLaporanPelanggaran'])->name('generateLaporanPelanggaran');
        Route::get('/generate-laporan-kegiatan', [GenerateReportController::class, 'generateLaporanKegiatan'])->name('generateLaporanKegiatan');
        Route::post('/generate-laporan-kegiatan-asrama', [GenerateReportController::class, 'generateLaporanPelanggaranKegiatanAsrama'])->name('generateLaporanPelanggaranKegiatanAsrama');
        Route::post('/generate-laporan-ukm', [GenerateReportController::class, 'generateLaporanUkm'])->name('generateLaporanUkm');

        // EPIC 01: MODUL UKM DINAMIS — Admin Routes (US 1.1 & US 1.0)
        // 'show' is intentionally excluded here and registered instead in the
        // broader role:admin,operator,pelatih,pembina group below — Pelatih
        // and Pembina both need to reach the UKM detail page (jadwal form,
        // "kembali ke detail" link from verifikasi) but the create/update/
        // destroy/index actions stay admin-only. See §4 of the frontend
        // design doc for the intended role matrix.
        Route::resource('ukm', UkmController::class)->except(['show']);
        // EPIC 03: MODUL WORKFLOW PERIZINAN — Milestone 0 (Admin Pejabat Routes)
        Route::resource('pejabat', PejabatController::class);
    });
    // ROUTE SINGGLE END

    // ROUTE PIVOT START
    // 'pembina' ditambahkan supaya EnsureUserHasRole tidak redirect-loop:
    // authDashboard() mengarahkan role pembina ke admin.index — kalau grup ini
    // menolaknya, middleware akan redirect balik ke admin.index tanpa akhir.
    // Lihat Konflik 1 di .claude/plans/epic-01-ukm-dinamis.md.
    Route::middleware('role:admin,operator,pelatih,pembina')->name('admin.')->group(function () {
        Route::get('/dashboard-admin', [DashboardController::class, 'index'])->name('index');
        Route::get('/getDataPresenceUserLast7Days', [DashboardController::class, 'getDataPresenceUserLast7Days'])->name('getDataPresenceUserLast7Days');

        Route::get('/profil-admin', [ProfileController::class, 'profil'])->name('profil');
        Route::post('/profil-admin/{id}', [ProfileController::class, 'editProfile'])->name('editProfile');
        Route::post('/profil-admin-gmail/{id}', [ProfileController::class, 'editProfileGmail'])->name('editProfileGmail');

        // Route::get('/absensi-mahasiswa', [AbsensiMahasiswa::class, 'index'])->name('absensiMahasiswa');
        // Route::get('/absensi-mahasiswa/getNomorRuangan', [AbsensiMahasiswa::class, 'getNomorRuangan'])->name('absensiMahasiswaGetNomorRuangan');
        // Route::get('/absensi-mahasiswa/getDataAbsen', [AbsensiMahasiswa::class, 'getDataAbsen'])->name('absensiMahasiswaGetDataAbsen');

        Route::get('/data-petugas', [DashboardAdminController::class, 'showDataPetugas'])->name('dataPetugas');
        Route::get('/data-petugas/create-show', [DashboardAdminController::class, 'createDataPetugasShow'])->name('createPetugasShow');
        Route::post('/data-petugas/create', [DashboardAdminController::class, 'createDataPetugas'])->name('createPetugas');
        Route::get('/data-petugas/{id}', [DashboardAdminController::class, 'editDataPetugasShow'])->name('editDataPetugasShow');
        Route::post('/data-petugas/edit/{id}', [DashboardAdminController::class, 'editDataPetugas'])->name('editDataPetugas');
        Route::delete('/data-petugas/destroy/{id}', [DashboardAdminController::class, 'destroyDataPetugas'])->name('destroyPetugas');

        // EPIC 03: MODUL PERIZINAN — Admin Management Routes (M6)
        Route::resource('izin/jenis', \App\Http\Controllers\AdminJenisIzinController::class)->names([
            'index' => 'jenis.index',
            'create' => 'jenis.create',
            'store' => 'jenis.store',
            'show' => 'jenis.show',
            'edit' => 'jenis.edit',
            'update' => 'jenis.update',
            'destroy' => 'jenis.destroy',
        ]);
        Route::get('/izin/data', [\App\Http\Controllers\AdminIzinDataController::class, 'index'])->name('izin.data.index');
        Route::get('/izin/data-export/pdf', [\App\Http\Controllers\AdminIzinDataController::class, 'exportPdf'])->name('izin.data.pdf');
        Route::get('/izin/data-export/excel', [\App\Http\Controllers\AdminIzinDataController::class, 'exportExcel'])->name('izin.data.excel');
        Route::get('/izin/data/{pengajuan}', [\App\Http\Controllers\AdminIzinDataController::class, 'show'])->name('izin.data.show');

        Route::get('/piket-petugas/generate-jadwal-bulanan', [DashboardAdminController::class, 'piketPetugasGenerateJadwalBulanan'])->name('piketPetugasGenerateJadwalBulanan');
        Route::get('/piket-petugas/generate-jadwal-mingguan', [DashboardAdminController::class, 'piketPetugasGenerateJadwalMingguan'])->name('piketPetugasGenerateJadwalMingguan');
        Route::post('/piket-petugas/generate-jadwal', [DashboardAdminController::class, 'piketPetugasGenerateJadwal'])->name('piketPetugasGenerateJadwal');

        Route::get('/piket-petugas', [DashboardAdminController::class, 'showPiketPetugas'])->name('piketPetugas');
        Route::get('/piket-petugas/edit/{id}', [DashboardAdminController::class, 'showPiketPetugasSingle'])->name('showPiketPetugasSingle');
        Route::post('/piket-petugas/update/{id}', [DashboardAdminController::class, 'updatePiketPetugas'])->name('updatePiketPetugas');

        Route::get('/kamera-pelatih', [QRControllerHukum::class, 'scanCamPelatih'])->name('scanCamPelatih');  //Done Survey
        Route::post('/kamera-pelatih', [QRControllerHukum::class, 'scanCamPelatihStore'])->name('scanCamPelatihStore');  //Done Survey

        // EPIC 01: MODUL UKM DINAMIS — Pelatih & Admin Schedule Routes (US 1.2 & US 1.3)
        // 'show' lives here (not in the admin-only resource group above) so
        // Pelatih/Pembina can reach the UKM detail page (jadwal form, "Buka
        // Scanner" links, "kembali ke detail" from verifikasi). Scoped
        // in-controller: non-admin staff only see UKMs they are an active
        // member of.
        Route::get('ukm/{ukm}', [UkmController::class, 'show'])->name('ukm.show');
        Route::post('ukm/{ukm}/jadwal', [UkmJadwalController::class, 'store'])->name('ukm.jadwal.store');
        Route::get('ukm/{ukm}/jadwal/events', [UkmJadwalController::class, 'events'])->name('ukm.jadwal.events');
        Route::patch('ukm/jadwal/{jadwal}/ajukan-verifikasi', [UkmJadwalController::class, 'ajukanVerifikasi'])->name('ukm.jadwal.ajukanVerifikasi');
        Route::delete('ukm/jadwal/{jadwal}', [UkmJadwalController::class, 'destroy'])->name('ukm.jadwal.destroy');
        Route::get('kamera-ukm/{jadwal}', [UkmScanController::class, 'show'])->name('ukm.scan.show');
        Route::post('api/kamera-ukm/{jadwal}', [UkmScanController::class, 'store'])->name('ukm.scan.store');
        Route::post('ukm/{ukm}/anggota', [UkmMemberController::class, 'store'])->name('ukm.anggota.store');
        Route::patch('ukm/{ukm}/anggota/aktifkan-semua', [UkmMemberController::class, 'aktifkanSemua'])->name('ukm.anggota.aktifkanSemua');
        Route::patch('ukm/{ukm}/anggota/{member}/aktifkan', [UkmMemberController::class, 'aktifkan'])->name('ukm.anggota.aktifkan');
        Route::delete('ukm/{ukm}/anggota/{member}', [UkmMemberController::class, 'destroy'])->name('ukm.anggota.destroy');

        // EPIC 01: MODUL UKM DINAMIS — Pembina & Admin Verification & Report Routes (US 1.4)
        Route::get('ukm/{ukm}/verifikasi', [UkmVerifikasiController::class, 'index'])->name('ukm.verifikasi.index');
        Route::patch('ukm/jadwal/{jadwal}/verifikasi', [UkmVerifikasiController::class, 'update'])->name('ukm.verifikasi.update');
        Route::post('ukm/{ukm}/laporan/pdf', [UkmLaporanController::class, 'pdfReport'])->name('ukm.laporan.pdf');

        Route::get('/data-pelanggaran', [PelanggaranController::class, 'dataPelanggaran'])->name('dataPelanggaran');
        Route::post('/data-pelanggaran/searchdatapelanggaran', [PelanggaranController::class, 'searchDataPelanggaran'])->name('searchDataPelanggaran');
        Route::get('/data-pelanggaran/detail/{id}', [PelanggaranController::class, 'dataPelanggaranDetail'])->name('dataPelanggaranDetail');

        Route::get('/laporan-pelanggaran', [PelanggaranController::class, 'laporanPelanggaran'])->name('laporanPelanggaran');
        Route::get('/laporan-pelanggaran/{id}', [PelanggaranController::class, 'laporanPelanggaranOpen'])->name('laporanPelanggaranOpen');
        Route::post('/laporan-pelanggaran-rejected/{id}', [PelanggaranController::class, 'laporanPelanggaranRejected'])->name('laporanPelanggaranRejected');
        Route::get('/laporan-pelanggaran-deleted/{id}', [PelanggaranController::class, 'laporanPelanggaranDeleted'])->name('laporanPelanggaranDeleted');
        Route::get('/laporan-pelanggaran-done/{id}', [PelanggaranController::class, 'laporanPelanggaranDone'])->name('laporanPelanggaranDone');
        Route::post('/laporan-pelanggaran-confirm/{id}', [PelanggaranController::class, 'laporanPelanggaranConfirm'])->name('laporanPelanggaranConfirm');

        Route::get('/edit-pelanggaran/{id}', [PelanggaranController::class, 'editPelanggaranIdKategori'])->name('editPelanggaranIdKategori');
        Route::post('/editkategori', [PelanggaranController::class, 'editKategoriStore'])->name('editKategoriStore');
        Route::post('/createkategori', [PelanggaranController::class, 'createKategori'])->name('createKategori');
        Route::post('/edit-jenis-pelanggaran/{id_kategori}', [PelanggaranController::class, 'editPelanggaranStore'])->name('editPelanggaranStore');
        Route::post('/createjenispelanggaran/{id}', [PelanggaranController::class, 'createJenisPelanggaran'])->name('createJenisPelanggaran');
        Route::get('/deletejenispelanggaran/{id}', [PelanggaranController::class, 'deleteJenisPelanggaran'])->name('deleteJenisPelanggaran');
        Route::get('/deletekategori/{id}', [PelanggaranController::class, 'deleteKategori'])->name('deleteKategori');

        Route::get('/kamera-scan', [DashboardAdminController::class, 'showKamera'])->name('kamera'); 
        Route::post('/presense/api', [QRController::class, 'presense'])->name('presense.api');

        Route::get('/kamera-upacara', [QRControllerKegiatan::class, 'kameraKegiatanUpacaraShow'])->name('kameraKegiatanUpacaraShow'); 
        Route::get('/kamera-apel', [QRControllerKegiatan::class, 'kameraKegiatanApelShow'])->name('kameraKegiatanApelShow');
        Route::get('/kamera-senam', [QRControllerKegiatan::class, 'kameraKegiatanSenamShow'])->name('kameraKegiatanSenamShow');
        Route::post('/api/kamera-upacara', [QRControllerKegiatan::class, 'kameraKegiatanUpacaraApi'])->name('kameraKegiatanUpacaraApi');
        Route::post('/api/kamera-apel', [QRControllerKegiatan::class, 'kameraKegiatanApelApi'])->name('kameraKegiatanApelApi');
        Route::post('/api/kamera-senam', [QRControllerKegiatan::class, 'kameraKegiatanSenamApi'])->name('kameraKegiatanSenamApi');

        Route::get('/jadwal-kegiatan', [kegiatanAsramaController::class, 'jadwalKegiatanShow'])->name('jadwalKegiatanShow');
        Route::get('/jadwal-kegiatan-filter', [KegiatanAsramaController::class, 'jadwalKegiatanFilter'])->name('jadwalKegiatanFilter');
        Route::post('/create-jadwal-store', [kegiatanAsramaController::class, 'createJadwalKegiatanStore'])->name('createJadwalKegiatanStore');
        Route::post('/jadwal-kegiatan-edit/{id}', [kegiatanAsramaController::class, 'editJadwalKegiatanAsrama'])->name('editJadwalKegiatanAsrama');
        Route::post('/jadwal-kegiatan-delete/{id}', [kegiatanAsramaController::class, 'deleteJadwalKegiatanAsrama'])->name('deleteJadwalKegiatanAsrama');
        Route::post('/edit-jadwal-kegiatan-by-blok', [kegiatanAsramaController::class, 'editJadwalKegiatanByBlok'])->name('editJadwalKegiatanByBlok');
        Route::get('/filtering-jadwal-kegiatan-by-blok', [kegiatanAsramaController::class, 'filteringEditJadwalKegiatanByBlok'])->name('filteringEditJadwalKegiatanByBlok');

        Route::get('/data-kegiatan-wajib', [kegiatanAsramaController::class, 'dataKegiatanWajibShow'])->name('dataKegiatanWajibShow');
        Route::post('/data-kegiatan-wajib/search', [kegiatanAsramaController::class, 'dataKegiatanWajibSearch'])->name('dataKegiatanWajibSearch');
        Route::get('/data-kegiatan-wajib/{id}', [kegiatanAsramaController::class, 'dataKegiatanWajibDetail'])->name('dataKegiatanWajibDetail');
        Route::post('/data-kegiatan-wajib-filter', [kegiatanAsramaController::class, 'dataKegiatanWajibDetailFilter'])->name('dataKegiatanWajibDetailFilter');
        Route::post('/edit-data-kegiatan-wajib/{id}/upacara', [kegiatanAsramaController::class, 'editDataKegiatanWajib'])->name('editDataKegiatanWajib');

        Route::post('/delete-foto/{user_id}', [ProfileController::class, 'deleteFotoProfile'])->name('deleteFotoProfile');

        // ROUTE DEVELOPMENT BACKEND
        Route::get('/data-absen-keluar', [AbsensiMahasiswa::class, 'dataAbsenKeluarShow'])->name('data-absen-keluar');
        Route::post ('/data-absen-keluar/search', [AbsensiMahasiswa::class, 'dataAbsenKeluarSearch'])->name('dataAbsenKeluarSearch');
        Route::get('/data-absen-keluar/detail/{id}', [AbsensiMahasiswa::class, 'dataAbsenKeluarDetail'])->name('detailAbsenKeluarDetail');

        // EPIC 03: PERSETUJUAN PERIZINAN (M2b Approver Routes)
        Route::get('/admin/izin/persetujuan', [\App\Http\Controllers\IzinPersetujuanController::class, 'inbox'])->name('izin.persetujuan.inbox');
        Route::get('/admin/izin/persetujuan/{pengajuan}', [\App\Http\Controllers\IzinPersetujuanController::class, 'review'])->name('izin.persetujuan.review');
        Route::get('/admin/izin/persetujuan/{pengajuan}/pdf', [\App\Http\Controllers\IzinPersetujuanController::class, 'downloadPdf'])->name('izin.persetujuan.pdf');
        Route::post('/admin/izin/persetujuan/{pengajuan}', [\App\Http\Controllers\IzinPersetujuanController::class, 'putuskan'])->name('izin.persetujuan.putuskan');

        // EPIC 03: MONITOR ASRAMA (M5 Dashboard Staff Routes)
        Route::get('/admin/izin/monitor', [\App\Http\Controllers\IzinMonitorController::class, 'index'])->name('izin.monitor');
        Route::get('/admin/izin/monitor/data', [\App\Http\Controllers\IzinMonitorController::class, 'data'])->name('izin.monitor.data');
    });

    // LOGOUT ROUTE
    Route::delete('/logout', [AuthController::class, 'LogoutAccount'])->name('auth.logout');
});

require __DIR__ . '/api.php';
