<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfilePasswordTest extends TestCase
{
    protected function tearDown(): void
    {
        $admin = User::find(1);
        if ($admin) {
            $admin->is_password_changed = true;
            $admin->save();
        }
        parent::tearDown();
    }

    /**
     * Helper to find or create a user for a specific role.
     */
    private function getOrCreateUserForRole(int $roleId, string $prefix): User
    {
        $user = User::where('role_id', $roleId)->first();
        if (!$user) {
            $user = User::factory()->create([
                'role_id' => $roleId,
                'email' => "{$prefix}@polbangtanmalang.ac.id",
                'name' => ucfirst($prefix) . ' Test',
                'password' => Hash::make('password'),
                'is_password_changed' => false,
                'no_hp' => '08' . str_pad((string)$roleId, 10, '8', STR_PAD_RIGHT),
            ]);
        }
        return $user;
    }

    /**
     * Test 1: Admin with default password can successfully change password on profil-admin.
     */
    public function test_admin_with_default_password_can_successfully_change_password_on_profil_admin(): void
    {
        $admin = $this->getOrCreateUserForRole(User::ADMIN_ROLE_ID, 'admin_pwd_test');
        $admin->password = Hash::make('password');
        $admin->is_password_changed = false;
        $admin->save();

        $response = $this->actingAs($admin)->post(route('admin.editProfileGmail', $admin->id), [
            'email' => $admin->email,
            'no_hp' => $admin->no_hp ?? '081234567891',
            'password' => 'newadminpassword123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');

        $admin->refresh();
        $this->assertTrue((bool)$admin->is_password_changed, 'Admin is_password_changed flag must be true after password update.');
        $this->assertTrue(Hash::check('newadminpassword123', $admin->password), 'Admin password must match new password.');

        // Verify admin can now access dashboard-admin without being redirected to profile
        $dashResponse = $this->actingAs($admin)->get(route('admin.index'));
        $dashResponse->assertStatus(200);
    }

    /**
     * Test 2: All staff/admin panel roles render the correct admin.editProfileGmail form action.
     */
    public function test_all_staff_roles_render_correct_admin_edit_profile_gmail_form_action(): void
    {
        $staffRoles = [
            'admin' => User::ADMIN_ROLE_ID,
            'operator' => User::OPERATOR_ROLE_ID,
            'pelatih' => User::PELATIH_ROLE_ID,
            'pembina' => User::PEMBINA_ROLE_ID,
            'pelatih_ukm' => User::PELATIH_UKM_ROLE_ID,
            'security' => User::SECURITY_ROLE_ID,
            'dosen_pa' => User::DOSEN_PA_ROLE_ID,
            'pejabat' => User::PEJABAT_ROLE_ID,
        ];

        foreach ($staffRoles as $roleName => $roleId) {
            $user = $this->getOrCreateUserForRole($roleId, $roleName);
            $user->is_password_changed = true; // allow view rendering
            $user->save();

            $response = $this->actingAs($user)->get(route('admin.profil'));
            $response->assertStatus(200);

            $html = $response->getContent();

            // Expected admin route in form action
            $expectedAction = route('admin.editProfileGmail', $user->id);
            // Forbidden student route in form action
            $forbiddenAction = route('home.EditprofilGmail', $user->id);

            $this->assertStringContainsString(
                $expectedAction,
                $html,
                "Role {$roleName} (ID: {$roleId}) must render form action pointing to admin.editProfileGmail."
            );

            $this->assertStringNotContainsString(
                $forbiddenAction,
                $html,
                "Role {$roleName} (ID: {$roleId}) must NOT render form action pointing to student home.EditprofilGmail."
            );
        }
    }

    /**
     * Test 3: Pembina and Pejabat with default password can change password without redirect loop.
     */
    public function test_pembina_and_pejabat_with_default_password_can_change_password_without_redirect_loop(): void
    {
        $rolesToTest = [
            'pembina' => User::PEMBINA_ROLE_ID,
            'pejabat' => User::PEJABAT_ROLE_ID,
        ];

        foreach ($rolesToTest as $name => $roleId) {
            $staff = $this->getOrCreateUserForRole($roleId, "{$name}_loop_test");
            $staff->password = Hash::make('password');
            $staff->is_password_changed = false;
            $staff->save();

            // When user opens profile, they should see profile page
            $viewResp = $this->actingAs($staff)->get(route('admin.profil'));
            $viewResp->assertStatus(200);

            // Submitting the password update
            $updateResp = $this->actingAs($staff)->post(route('admin.editProfileGmail', $staff->id), [
                'email' => $staff->email,
                'no_hp' => $staff->no_hp ?? '081234567892',
                'password' => 'brandnewpassword123',
            ]);

            $updateResp->assertStatus(302);
            $updateResp->assertSessionHasNoErrors();
            $updateResp->assertSessionMissing('error');

            $staff->refresh();
            $this->assertTrue((bool)$staff->is_password_changed, "Role {$name} is_password_changed must be true.");
            $this->assertTrue(Hash::check('brandnewpassword123', $staff->password), "Role {$name} password must be updated.");
        }
    }

    /**
     * Test 4: Admin updating personal info does not fail on missing student fields.
     */
    public function test_admin_updating_personal_info_does_not_fail_on_missing_student_fields(): void
    {
        $admin = $this->getOrCreateUserForRole(User::ADMIN_ROLE_ID, 'admin_info_test');
        $admin->is_password_changed = true;
        $admin->save();

        // Admin form in Blade only has name and optional foto-profil (no kelas_id, blok_ruangan_id, no_kamar, asal_daerah, etc.)
        $response = $this->actingAs($admin)->post(route('admin.editProfile', $admin->id), [
            'name' => 'Nama Baru Admin',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $admin->refresh();
        $this->assertSame('Nama Baru Admin', $admin->name);
    }

    /**
     * Test 5: Updating petugas password sets is_password_changed flag.
     */
    public function test_updating_petugas_password_sets_is_password_changed_flag(): void
    {
        $admin = $this->getOrCreateUserForRole(User::ADMIN_ROLE_ID, 'admin_petugas_mgr');
        $admin->is_password_changed = true;
        $admin->save();

        $operator = $this->getOrCreateUserForRole(User::OPERATOR_ROLE_ID, 'operator_target');
        $operator->password = Hash::make('password');
        $operator->is_password_changed = false;
        $operator->save();

        $response = $this->actingAs($admin)->post(route('admin.editDataPetugas', $operator->id), [
            'email' => $operator->email,
            'name' => $operator->name,
            'role_id' => $operator->role_id,
            'reset_password' => 'password',
            'new_password' => 'operatornewpass123',
            'new_password_confirmation' => 'operatornewpass123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $operator->refresh();
        $this->assertTrue((bool)$operator->is_password_changed, 'Target petugas must have is_password_changed set to true.');
        $this->assertTrue(Hash::check('operatornewpass123', $operator->password), 'Target petugas password must be updated.');
    }
}
