<?php

namespace Tests\Feature\Izin;

use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JadwalPetugas;
use App\Models\JenisIzin;
use App\Models\User;
use App\Services\Izin\ApproverResolver;
use App\Services\Izin\PengajuanIzinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinOperatorVerifikatorPiketTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator1;
    protected User $operator2;
    protected User $security;
    protected User $pelatih;
    protected User $student;
    protected JenisIzin $jenisIzin;
    protected ApproverResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(ApproverResolver::class);

        $this->admin = User::factory()->create([
            'role_id' => User::ADMIN_ROLE_ID, // 1
            'name' => 'Admin Staff',
        ]);

        $this->operator1 = User::factory()->create([
            'role_id' => User::OPERATOR_ROLE_ID, // 2
            'name' => 'Operator Piket 1',
        ]);

        $this->operator2 = User::factory()->create([
            'role_id' => User::OPERATOR_ROLE_ID, // 2
            'name' => 'Operator Piket 2',
        ]);

        $this->security = User::factory()->create([
            'role_id' => User::SECURITY_ROLE_ID, // 7
            'name' => 'Security Officer',
        ]);

        $this->pelatih = User::factory()->create([
            'role_id' => User::PELATIH_ROLE_ID, // 4
            'name' => 'Pelatih Kedisiplinan',
        ]);

        $this->student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID, // 3
            'name' => 'Mahasiswa Test',
        ]);

        // Buat Jenis Izin sederhana 1 langkah: Petugas Piket Asrama
        $this->jenisIzin = JenisIzin::create([
            'kode' => 'IZIN_PIKET_' . rand(1000, 9999),
            'nama' => 'Izin Piket Test',
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'is_active' => true,
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisIzin->id,
            'urutan' => 1,
            'label' => 'Mengetahui, Petugas Piket Asrama',
            'resolver' => 'petugas_jaga',
            'mode' => 'any',
            'resolve_saat' => 'langkah_aktif',
        ]);
    }

    public function test_resolver_petugas_jaga_hanya_mengembalikan_user_ber_role_operator_dari_jadwal(): void
    {
        // Misalkan ada jadwal dengan petugas 1 = Operator dan petugas 2 = Security
        JadwalPetugas::create([
            'date' => '2026-09-20',
            'petugas1_id' => $this->operator1->id,
            'petugas2_id' => $this->security->id,
        ]);

        $step = $this->jenisIzin->steps()->first();
        $res = $this->resolver->resolve($step, [
            'user' => $this->student,
            'waktu_berangkat' => '2026-09-20 08:00:00',
        ]);

        $candidates = $res['candidates'];

        // Hanya operator1 yang lolos, security tidak boleh menjadi kandidat verifikator
        $this->assertTrue($candidates->contains('id', $this->operator1->id));
        $this->assertFalse($candidates->contains('id', $this->security->id));
        $this->assertCount(1, $candidates);
    }

    public function test_resolver_petugas_jaga_fallback_hanya_mengembalikan_role_operator(): void
    {
        // Tidak ada JadwalPetugas untuk tanggal ini
        $step = $this->jenisIzin->steps()->first();
        $res = $this->resolver->resolve($step, [
            'user' => $this->student,
            'waktu_berangkat' => '2026-09-25 08:00:00',
        ]);

        $candidates = $res['candidates'];

        $this->assertNotEmpty($candidates);
        // Seluruh kandidat wajib ber-role Operator (role_id = 2)
        foreach ($candidates as $candidate) {
            $this->assertEquals(User::OPERATOR_ROLE_ID, $candidate->role_id);
        }

        // Admin, Pelatih, Security tidak boleh ada di kandidat fallback
        $this->assertFalse($candidates->contains('id', $this->admin->id));
        $this->assertFalse($candidates->contains('id', $this->pelatih->id));
        $this->assertFalse($candidates->contains('id', $this->security->id));
    }

    public function test_daftar_petugas_piket_admin_hanya_memuat_role_operator(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.piketPetugas'));
        $response->assertStatus(200);

        $usersInView = $response->viewData('users');
        $this->assertTrue($usersInView->contains('id', $this->operator1->id));
        $this->assertTrue($usersInView->contains('id', $this->operator2->id));
        $this->assertFalse($usersInView->contains('id', $this->security->id));
        $this->assertFalse($usersInView->contains('id', $this->admin->id));
        $this->assertFalse($usersInView->contains('id', $this->pelatih->id));
    }

    public function test_operator_piket_dapat_melihat_inbox_dan_menyetujui_izin(): void
    {
        $date = Carbon::tomorrow()->toDateString();

        // Jadwalkan operator1 dan operator2
        JadwalPetugas::create([
            'date' => $date,
            'petugas1_id' => $this->operator1->id,
            'petugas2_id' => $this->operator2->id,
        ]);

        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Keperluan mendesak',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => $date . ' 08:00:00',
            'waktu_kembali' => $date . ' 17:00:00',
        ]);

        $this->assertEquals(1, $pengajuan->langkah_aktif);

        // Operator 1 yang piket harus bisa melihat di inbox
        $resInboxOp1 = $this->actingAs($this->operator1)->get(route('admin.izin.persetujuan.inbox'));
        $resInboxOp1->assertStatus(200);
        $resInboxOp1->assertSee('Keperluan mendesak');

        // Operator 2 yang juga piket di tanggal itu harus bisa melihat di inbox
        $resInboxOp2 = $this->actingAs($this->operator2)->get(route('admin.izin.persetujuan.inbox'));
        $resInboxOp2->assertStatus(200);
        $resInboxOp2->assertSee('Keperluan mendesak');

        // Security tidak boleh bisa melihat
        $resInboxSec = $this->actingAs($this->security)->get(route('admin.izin.persetujuan.inbox'));
        $resInboxSec->assertDontSee('Keperluan mendesak');

        // Operator 2 memutuskan izin
        $resPutuskan = $this->actingAs($this->operator2)->post(route('admin.izin.persetujuan.putuskan', $pengajuan->id), [
            'keputusan' => 'setujui',
            'catatan' => 'Disetujui oleh Petugas Piket',
        ]);
        $resPutuskan->assertRedirect(route('admin.izin.persetujuan.inbox'));

        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);

        $approval = $pengajuan->approvals()->where('urutan', 1)->first();
        $this->assertEquals('disetujui', $approval->status);
        $this->assertEquals($this->operator2->id, $approval->acted_by);
        $this->assertEquals($this->operator2->id, $approval->approver_user_id);
    }
}
