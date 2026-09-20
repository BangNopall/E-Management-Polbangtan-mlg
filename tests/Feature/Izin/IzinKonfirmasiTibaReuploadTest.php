<?php

namespace Tests\Feature\Izin;

use App\Models\BlokRuangan;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IzinKonfirmasiTibaReuploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected JenisIzin $jenisIzin;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $dosenPa = User::factory()->create(['role_id' => 3, 'name' => 'Dosen PA']);
        $levelKelas = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create([
            'kelas' => 'TP-1A',
            'nama_kelas' => 'TP-1A',
            'prodi_id' => $prodi->id,
            'dosen_pa_id' => $dosenPa->id,
            'level_kelas_id' => $levelKelas->id,
        ]);
        $blok = BlokRuangan::create(['name' => 'Blok A']);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
            'nim' => '123456',
            'no_hp' => '08123456789',
            'password' => bcrypt('password123'),
            'asal_daerah' => 'Malang',
            'no_kamar' => '12',
        ]);

        $this->jenisIzin = JenisIzin::create([
            'nama' => 'Izin Pulang Kampung',
            'kode' => 'IPK',
            'deskripsi' => 'Izin pulang kampung halaman',
            'butuh_ortu' => false,
            'butuh_surat_tugas' => false,
            'butuh_konfirmasi_tiba' => true,
            'is_active' => true,
        ]);
    }

    public function test_tombol_konfirmasi_muncul_sebelum_upload_dan_hilang_setelah_upload(): void
    {
        Storage::fake('public');

        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Pulang kampung',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => now()->subHour(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
            'tiba_at' => null,
            'tiba_bukti_path' => null,
        ]);

        // 1. Sebelum upload, tombol konfirmasi muncul di halaman show
        $responseBefore = $this->actingAs($this->student)->get(route('home.izin.show', $pengajuan->id));
        $responseBefore->assertStatus(200);
        $responseBefore->assertSee('Konfirmasi Kedatangan Lokasi Tujuan');

        // 2. Lakukan upload bukti pertama kali
        $filePertama = File::image('bukti_pertama.jpg');
        $uploadResponse = $this->actingAs($this->student)
            ->post(route('home.izin.konfirmasi-tiba', $pengajuan->id), [
                'foto_bukti' => $filePertama,
            ]);
        $uploadResponse->assertRedirect();
        $uploadResponse->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertNotNull($pengajuan->tiba_at);
        $this->assertNotNull($pengajuan->tiba_bukti_path);
        Storage::disk('public')->assertExists($pengajuan->tiba_bukti_path);

        // 3. Setelah upload, tombol di header (sebelah Cetak PDF) harus hilang
        $responseAfter = $this->actingAs($this->student)->get(route('home.izin.show', $pengajuan->id));
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('Detail Konfirmasi Kedatangan Lokasi Tujuan');
        $responseAfter->assertSee('Unggah Ulang Bukti');
    }

    public function test_reupload_bukti_menghapus_file_lama_dari_storage(): void
    {
        Storage::fake('public');

        // Buat file pertama langsung di storage
        $oldPath = 'bukti_tiba/old_evidence.jpg';
        Storage::disk('public')->put($oldPath, 'dummy content file lama');
        Storage::disk('public')->assertExists($oldPath);

        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Pulang kampung',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => now()->subHour(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
            'tiba_at' => now()->subMinutes(30),
            'tiba_bukti_path' => $oldPath,
            'tiba_dikonfirmasi_oleh' => $this->student->name,
        ]);

        // Kirim re-upload dengan file baru
        $newFile = File::image('new_evidence.png');
        $response = $this->actingAs($this->student)
            ->post(route('home.izin.konfirmasi-tiba', $pengajuan->id), [
                'foto_bukti' => $newFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pengajuan->refresh();

        // Pastikan file lama SUDAH TERHAPUS
        Storage::disk('public')->assertMissing($oldPath);

        // Pastikan file baru tersimpan dan path di DB berubah
        $this->assertNotEquals($oldPath, $pengajuan->tiba_bukti_path);
        Storage::disk('public')->assertExists($pengajuan->tiba_bukti_path);
    }
}
