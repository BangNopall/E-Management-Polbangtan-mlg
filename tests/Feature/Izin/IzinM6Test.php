<?php

namespace Tests\Feature\Izin;

use App\Models\BlokRuangan;
use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use App\Models\User;
use App\Services\Izin\PengajuanIzinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinM6Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected User $dosenPa;
    protected JenisIzin $jenisIzin;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $this->dosenPa = User::factory()->create(['role_id' => 1, 'name' => 'Dosen PA User']);
        $this->admin = User::factory()->create(['role_id' => 1, 'name' => 'Admin User']);
        $levelKelas = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create(['kelas' => 'TP-1A', 'nama_kelas' => 'TP-1A', 'prodi_id' => $prodi->id, 'dosen_pa_id' => $this->dosenPa->id, 'level_kelas_id' => $levelKelas->id]);
        $blok = BlokRuangan::create(['name' => 'Blok A']);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
            'nim' => '123456',
            'no_hp' => '08123456789',
        ]);

        $this->jenisIzin = JenisIzin::create([
            'kode' => 'IZIN_M6_' . rand(1000, 9999),
            'nama' => 'Izin M6 Test',
            'butuh_ukm' => false,
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'is_active' => true,
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisIzin->id,
            'urutan' => 1,
            'label' => 'Persetujuan Dosen PA',
            'resolver' => 'dosen_pa',
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);
    }

    public function test_admin_bisa_akses_dan_crud_jenis_izin(): void
    {
        // 1. Index Page
        $responseIndex = $this->actingAs($this->admin)->get(route('admin.jenis.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Kelola Jenis Izin');

        // 2. Store Jenis Izin Baru + Steps
        $payload = [
            'kode' => 'IZIN_BARU_' . rand(1000, 9999),
            'nama' => 'Izin Baru Test',
            'min_ajukan_jam' => 2,
            'maks_durasi_jam' => 12,
            'is_active' => 1,
            'steps' => [
                [
                    'urutan' => 1,
                    'label' => 'Langkah 1 Dosen PA',
                    'resolver' => 'dosen_pa',
                    'mode' => 'any',
                    'resolve_saat' => 'submit',
                ]
            ],
        ];

        $responseStore = $this->actingAs($this->admin)->post(route('admin.jenis.store'), $payload);
        $responseStore->assertRedirect(route('admin.jenis.index'));
        $this->assertDatabaseHas('jenis_izins', ['nama' => 'Izin Baru Test']);
    }

    public function test_editor_langkah_menolak_urutan_bolong_atau_duplikat(): void
    {
        $payload = [
            'kode' => 'IZIN_BOLONG_' . rand(1000, 9999),
            'nama' => 'Izin Urutan Bolong',
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'steps' => [
                [
                    'urutan' => 1,
                    'label' => 'Langkah 1',
                    'resolver' => 'dosen_pa',
                    'mode' => 'any',
                    'resolve_saat' => 'submit',
                ],
                [
                    'urutan' => 3, // Bolong! (harusnya 2)
                    'label' => 'Langkah 3',
                    'resolver' => 'petugas_jaga',
                    'mode' => 'any',
                    'resolve_saat' => 'submit',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.jenis.store'), $payload);
        $response->assertSessionHasErrors('steps');
    }

    public function test_editor_langkah_menolak_resolver_pejabat_tanpa_jabatan(): void
    {
        $payload = [
            'kode' => 'IZIN_PEJABAT_' . rand(1000, 9999),
            'nama' => 'Izin Resolver Pejabat Tanpa Jabatan',
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'steps' => [
                [
                    'urutan' => 1,
                    'label' => 'Persetujuan Kaprodi',
                    'resolver' => 'pejabat',
                    'jabatan' => [], // Kosong!
                    'mode' => 'any',
                    'resolve_saat' => 'submit',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.jenis.store'), $payload);
        $response->assertSessionHasErrors('steps.0.jabatan');
    }

    /**
     * TEST CRITICAL ATURAN MUTLAK:
     * Menyimpan/memperbarui konfigurasi JenisIzin TIDAK BOLEH mengubah pengajuan yang sedang berjalan.
     */
    public function test_memperbarui_jenis_izin_tidak_mengubah_pengajuan_berjalan(): void
    {
        // 1. Submit pengajuan izin dengan konfigurasi awal (1 langkah Dosen PA)
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Pemeriksaan Kesehatan',
            'tujuan_lokasi' => 'Rumah Sakit',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        $this->assertCount(1, $pengajuan->approvals);

        // 2. Admin memperbarui JenisIzin (menambahkan langkah 2 Pejabat Kaprodi)
        $updatePayload = [
            'kode' => $this->jenisIzin->kode,
            'nama' => $this->jenisIzin->nama,
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'is_active' => 1,
            'steps' => [
                [
                    'urutan' => 1,
                    'label' => 'Langkah 1 Dosen PA Updated',
                    'resolver' => 'dosen_pa',
                    'mode' => 'any',
                    'resolve_saat' => 'submit',
                ],
                [
                    'urutan' => 2,
                    'label' => 'Langkah 2 Kaprodi Added',
                    'resolver' => 'pejabat',
                    'jabatan' => ['kaprodi'],
                    'mode' => 'any',
                    'resolve_saat' => 'submit',
                ],
            ],
        ];

        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.jenis.update', $this->jenisIzin->id), $updatePayload);
        $responseUpdate->assertRedirect(route('admin.jenis.index'));

        // 3. Verifikasi pengajuan yang sedang berjalan TETAP berisi 1 langkah frozen semula (Keterisolasian ADR-006)
        $pengajuan->refresh();
        $this->assertCount(1, $pengajuan->approvals);
        $this->assertEquals('Persetujuan Dosen PA', $pengajuan->approvals->first()->label_snapshot);
    }

    public function test_admin_bisa_export_laporan_pdf_dan_excel_perizinan(): void
    {
        // 1. Export PDF
        $responsePdf = $this->actingAs($this->admin)->get(route('admin.izin.data.pdf'));
        $responsePdf->assertStatus(200);
        $responsePdf->assertHeader('content-type', 'application/pdf');

        // 2. Export Excel
        $responseExcel = $this->actingAs($this->admin)->get(route('admin.izin.data.excel'));
        $responseExcel->assertStatus(200);
        $responseExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_detail_data_perizinan_menampilkan_audit_trail_lengkap(): void
    {
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Audit Trail Check',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        $approval = $pengajuan->approvals->first();
        $service->setujui($approval, $this->dosenPa);

        $responseDetail = $this->actingAs($this->admin)->get(route('admin.izin.data.show', $pengajuan->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Audit Trail');
        $responseDetail->assertSee($this->dosenPa->name);
    }
}
