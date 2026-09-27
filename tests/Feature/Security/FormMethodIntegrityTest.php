<?php

namespace Tests\Feature\Security;

use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use App\Models\Pelanggaran;
use App\Models\User;
use App\Models\Kelas;
use App\Models\BlokRuangan;
use Tests\TestCase;

class FormMethodIntegrityTest extends TestCase
{
    /**
     * Helper to detect nested <form> tags in rendered HTML.
     * Returns true if any <form> is opened before the preceding <form> is closed.
     */
    private function hasNestedForms(string $html): bool
    {
        // Remove script tags, svg, comments to avoid false positives
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $clean = preg_replace('/<!--(.*?)-->/is', '', $clean);

        // Match all <form and </form> in order of occurrence
        preg_match_all('/<\/?form\b[^>]*>/i', $clean, $matches);
        $tags = $matches[0] ?? [];

        $depth = 0;
        foreach ($tags as $tag) {
            if (str_starts_with(strtolower($tag), '</form')) {
                $depth--;
                if ($depth < 0) {
                    $depth = 0;
                }
            } else {
                if ($depth > 0) {
                    return true; // Nested form detected!
                }
                $depth++;
            }
        }

        return false;
    }

    /**
     * Helper to count open vs close form tags.
     */
    private function areFormTagsBalanced(string $html): bool
    {
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $clean = preg_replace('/<!--(.*?)-->/is', '', $clean);

        $openCount = preg_match_all('/<form\b[^>]*>/i', $clean);
        $closeCount = preg_match_all('/<\/form>/i', $clean);

        return $openCount === $closeCount;
    }

    public function test_edit_pelanggaran_view_has_no_nested_forms(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $admin->is_password_changed = true;
        $admin->save();

        $kategori = KategoriPelanggaran::first() ?? KategoriPelanggaran::create(['name' => 'Kategori Test']);
        $jenis = JenisPelanggaran::where('kategori_id', $kategori->id)->first() ?? JenisPelanggaran::create([
            'kategori_id' => $kategori->id,
            'jenis_pelanggaran' => 'Pelanggaran Uji Test',
            'poin' => 5,
            'sub_kategori' => 'Ringan'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.editPelanggaranIdKategori', $kategori->id));
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertFalse(
            $this->hasNestedForms($html),
            'Rendered view admin.edit-pelanggaran must NOT contain any nested <form> elements.'
        );
        $this->assertTrue(
            $this->areFormTagsBalanced($html),
            'Rendered view admin.edit-pelanggaran must have balanced <form> and </form> tags.'
        );
    }

    public function test_show_laporan_view_has_balanced_and_unnested_forms_across_all_statuses(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $admin->is_password_changed = true;
        $admin->save();

        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();
        $kategori = KategoriPelanggaran::first() ?? KategoriPelanggaran::create(['name' => 'Kategori Test']);
        $jenis = JenisPelanggaran::where('kategori_id', $kategori->id)->first() ?? JenisPelanggaran::create([
            'kategori_id' => $kategori->id,
            'jenis_pelanggaran' => 'Pelanggaran Uji Test',
            'poin' => 5,
            'sub_kategori' => 'Ringan'
        ]);

        $blok = BlokRuangan::first() ?? BlokRuangan::create(['name' => 'Blok A']);
        $kelas = Kelas::first() ?? Kelas::create(['nama_kelas' => 'Tingkat 1']);
        $student->blok_ruangan_id = $blok->id;
        $student->kelas_id = $kelas->id;
        $student->save();

        foreach (['submitted', 'progressing', 'rejected'] as $status) {
            $laporan = Pelanggaran::create([
                'user_id' => $student->id,
                'jenis_pelanggaran_id' => $jenis->id,
                'statusPelanggaran' => $status,
                'Hukuman' => $status === 'progressing' ? 'Membersihkan asrama' : null,
                'rejected_message' => $status === 'rejected' ? 'Bukti tidak cukup' : null,
            ]);

            $response = $this->actingAs($admin)->get(route('admin.laporanPelanggaranOpen', $laporan->id));
            $response->assertStatus(200);

            $html = $response->getContent();

            $this->assertFalse(
                $this->hasNestedForms($html),
                "View admin.admin-edit.show-laporan must not contain nested forms for status: {$status}."
            );
            $this->assertTrue(
                $this->areFormTagsBalanced($html),
                "View admin.admin-edit.show-laporan must have balanced form tags for status: {$status}."
            );
        }
    }

    public function test_detail_absenkeluar_has_no_stray_closing_forms(): void
    {
        $content = file_get_contents(resource_path('views/admin/detail-absenkeluar.blade.php'));
        $clean = preg_replace('/\{\{--.*?--\}\}/s', '', $content);

        $openCount = preg_match_all('/<form\b[^>]*>/i', $clean);
        $closeCount = preg_match_all('/<\/form>/i', $clean);

        $this->assertSame(
            $openCount,
            $closeCount,
            'detail-absenkeluar.blade.php must not have stray unclosed or orphan closing </form> tags.'
        );
    }

    public function test_ensure_profile_completed_middleware_returns_json_403_on_ajax_requests(): void
    {
        // User with default password and incomplete profile
        $user = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_password_changed' => false,
            'no_hp' => null,
        ]);

        $response = $this->actingAs($user)->getJson('/home');

        // Should receive 403 Forbidden JSON, NOT 302 HTML Redirect
        $response->assertStatus(403);
        $response->assertJsonStructure(['status', 'message']);
    }

    public function test_delete_jenis_pelanggaran_route_dispatches_delete_cleanly(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $admin->is_password_changed = true;
        $admin->save();

        $kategori = KategoriPelanggaran::first() ?? KategoriPelanggaran::create(['name' => 'Kategori Uji']);
        $jenis = JenisPelanggaran::create([
            'kategori_id' => $kategori->id,
            'jenis_pelanggaran' => 'Pelanggaran Khusus Hapus',
            'poin' => 10,
            'sub_kategori' => 'Berat'
        ]);

        $response = $this->actingAs($admin)->delete("/deletejenispelanggaran/{$jenis->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('jenis_pelanggarans', ['id' => $jenis->id]);
    }
}
