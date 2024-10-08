<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QRController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AbsensiMahasiswa;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QRControllerHukum;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QRControllerKegiatan;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\kegiatanAsramaController;

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
    });
    // ROUTE SINGGLE END

    // ROUTE PIVOT START
    Route::middleware('role:admin,operator,pelatih')->name('admin.')->group(function () {
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

        Route::get('/piket-petugas/generate-jadwal-mingguan', [DashboardAdminController::class, 'piketPetugasGenerateJadwalMingguan'])->name('piketPetugasGenerateJadwalMingguan');
        Route::post('/piket-petugas/generate-jadwal', [DashboardAdminController::class, 'piketPetugasGenerateJadwal'])->name('piketPetugasGenerateJadwal');

        Route::get('/piket-petugas', [DashboardAdminController::class, 'showPiketPetugas'])->name('piketPetugas');
        Route::get('/piket-petugas/edit/{id}', [DashboardAdminController::class, 'showPiketPetugasSingle'])->name('showPiketPetugasSingle');
        Route::post('/piket-petugas/update/{id}', [DashboardAdminController::class, 'updatePiketPetugas'])->name('updatePiketPetugas');

        Route::get('/kamera-pelatih', [QRControllerHukum::class, 'scanCamPelatih'])->name('scanCamPelatih');  //Done Survey
        Route::post('/kamera-pelatih', [QRControllerHukum::class, 'scanCamPelatihStore'])->name('scanCamPelatihStore');  //Done Survey

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

        // ROUTE DEVELOPMENT FRONT END
        Route::view('/data-absen-keluar', 'admin.data-absenkeluar')->name('data-absen-keluar');
        Route::view('/data-absen-keluar/detail', 'admin.detail-absenkeluar')->name('detail-absen-keluar');
    });

    // LOGOUT ROUTE
    Route::delete('/logout', [AuthController::class, 'LogoutAccount'])->name('auth.logout');
});

require __DIR__ . '/api.php';
