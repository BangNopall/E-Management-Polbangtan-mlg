<?php

namespace Tests\Feature\Izin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinRoleGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelatih;
    protected User $pembina;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelatih = User::factory()->create([
            'role_id' => User::PELATIH_ROLE_ID, // 4
            'name' => 'Pelatih Kedisiplinan Test',
        ]);

        $this->pembina = User::factory()->create([
            'role_id' => User::PEMBINA_ROLE_ID, // 5
            'name' => 'Pembina UKM Test',
        ]);
    }

    public function test_pelatih_dan_pembina_bisa_akses_inbox_dan_monitor_perizinan(): void
    {
        // 1. Pelatih
        $resPelatihInbox = $this->actingAs($this->pelatih)->get(route('admin.izin.persetujuan.inbox'));
        $resPelatihInbox->assertStatus(200);

        $resPelatihMonitor = $this->actingAs($this->pelatih)->get(route('admin.izin.monitor'));
        $resPelatihMonitor->assertStatus(200);

        // 2. Pembina
        $resPembinaInbox = $this->actingAs($this->pembina)->get(route('admin.izin.persetujuan.inbox'));
        $resPembinaInbox->assertStatus(200);

        $resPembinaMonitor = $this->actingAs($this->pembina)->get(route('admin.izin.monitor'));
        $resPembinaMonitor->assertStatus(200);
    }

    public function test_pelatih_dan_pembina_ditolak_akses_kelola_jenis_izin_dan_data_perizinan(): void
    {
        // 1. Pelatih ditolak kelola jenis izin & data perizinan
        $resPelatihJenis = $this->actingAs($this->pelatih)->get(route('admin.jenis.index'));
        $resPelatihJenis->assertRedirect();
        $resPelatihJenis->assertSessionHas('error');

        $resPelatihData = $this->actingAs($this->pelatih)->get(route('admin.izin.data.index'));
        $resPelatihData->assertRedirect();
        $resPelatihData->assertSessionHas('error');

        // 2. Pembina ditolak kelola jenis izin & data perizinan
        $resPembinaJenis = $this->actingAs($this->pembina)->get(route('admin.jenis.index'));
        $resPembinaJenis->assertRedirect();
        $resPembinaJenis->assertSessionHas('error');

        $resPembinaData = $this->actingAs($this->pembina)->get(route('admin.izin.data.index'));
        $resPembinaData->assertRedirect();
        $resPembinaData->assertSessionHas('error');
    }

    public function test_navbar_merender_inbox_dan_monitor_untuk_pelatih_dan_pembina(): void
    {
        // Pelatih: melihat Monitor Asrama & Inbox Perizinan, tapi TIDAK melihat Kelola Jenis Izin & Data Perizinan
        $resPelatih = $this->actingAs($this->pelatih)->get(route('admin.izin.monitor'));
        $resPelatih->assertSee('Monitor Asrama');
        $resPelatih->assertSee('Inbox Perizinan');
        $resPelatih->assertDontSee('Kelola Jenis Izin');
        $resPelatih->assertDontSee('Data Perizinan');

        // Pembina: melihat Monitor Asrama & Inbox Perizinan, tapi TIDAK melihat Kelola Jenis Izin & Data Perizinan
        $resPembina = $this->actingAs($this->pembina)->get(route('admin.izin.monitor'));
        $resPembina->assertSee('Monitor Asrama');
        $resPembina->assertSee('Inbox Perizinan');
        $resPembina->assertDontSee('Kelola Jenis Izin');
        $resPembina->assertDontSee('Data Perizinan');
    }
}
