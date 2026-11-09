<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginUiRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_redesigned_ui_with_all_assets(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('<title>SITERA — Masuk</title>', false);
        $response->assertSee(asset('sitera/login/global.css'), false);
        $response->assertSee(asset('sitera/login/style.css'), false);
        $response->assertSee(asset('sitera/login/assets/kementan.jpg'), false);
        $response->assertSee(asset('sitera/login/assets/logo_irigasi.webp'), false);
        $response->assertSee('SATU PINTU PELAPORAN');
        $response->assertSee('Alur Laporan');
        $response->assertSee('Lebih Tertata.');
        $response->assertSee('Informasi');
        $response->assertSee('Lebih Terlacak.');
        $response->assertSee('AKSES PEGAWAI');
        $response->assertSee('Selamat Datang');
        $response->assertSee('Masuk ke ruang kerja pemantauan kegiatan irigasi');
        $response->assertSee('name="_token"', false);
        $response->assertSee('action="'.route('login.store').'"', false);
        $response->assertSee('id="login"', false);
        $response->assertSee('name="login"', false);
        $response->assertSee('id="password"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('id="toggle-password"', false);
        $response->assertSee('id="remember"', false);
        $response->assertSee('name="remember"', false);
        $response->assertSee('Ingat perangkat ini');
        $response->assertSee('href="'.route('password.request').'"', false);
        $response->assertSee('Lupa Password?');
    }

    public function test_user_can_login_with_username_and_redirects(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'username' => 'pelapor_subang',
            'email' => 'subang@example.test',
            'password' => Hash::make('secret-password-123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'pelapor_subang',
            'password' => 'secret-password-123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_email_and_redirects(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'username' => 'pelapor_subang',
            'email' => 'subang@example.test',
            'password' => Hash::make('secret-password-123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'subang@example.test',
            'password' => 'secret-password-123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_credentials_render_styled_error(): void
    {
        User::factory()->create([
            'username' => 'pelapor_subang',
            'email' => 'subang@example.test',
            'password' => Hash::make('secret-password-123'),
        ]);

        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'login' => 'pelapor_subang',
            'password' => 'wrong-pass',
        ]);

        $response->assertOk();
        $response->assertSee('form-message--error', false);
        $response->assertSee('Username/email atau password tidak dapat digunakan.');
        $this->assertGuest();
    }

    public function test_empty_login_identifier_renders_validation_error(): void
    {
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'login' => '',
            'password' => 'some-password',
        ]);

        $response->assertOk();
        $response->assertSee('form-message--error', false);
        $response->assertSee('Masukkan username atau email.');
        $this->assertGuest();
    }

    public function test_remember_me_functionality_sets_remember_token(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'username' => 'pelapor_subang',
            'password' => Hash::make('secret-password-123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'pelapor_subang',
            'password' => 'secret-password-123',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertNotNull($user->getRememberToken());
    }

    public function test_logout_ends_session_and_redirects_to_login(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_role_redirect_on_dashboard_for_all_four_roles(): void
    {
        // 1. Pelapor Daerah
        $rolePelapor = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $pelapor = User::factory()->create(['role_id' => $rolePelapor->id]);
        $this->actingAs($pelapor)->get('/dashboard')
            ->assertOk()
            ->assertViewIs('reports.dashboard');

        // 2. Verifikator Pusat
        $roleVerifier = Role::firstOrCreate(['code' => 'verifikator_pusat'], ['name' => 'Verifikator Pusat']);
        $verifier = User::factory()->create(['role_id' => $roleVerifier->id]);
        $this->actingAs($verifier)->get('/dashboard')
            ->assertOk()
            ->assertViewIs('verifikasi.dashboard');

        // 3. Pimpinan Kementan
        $roleLeader = Role::firstOrCreate(['code' => 'pimpinan_kementan'], ['name' => 'Pimpinan Kementan']);
        $leader = User::factory()->create(['role_id' => $roleLeader->id]);
        $this->actingAs($leader)->get('/dashboard')
            ->assertOk()
            ->assertViewIs('monitoring.dashboard');

        // 4. Super Admin
        $roleAdmin = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $admin = User::factory()->create(['role_id' => $roleAdmin->id]);
        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertViewIs('admin.dashboard');
    }

    public function test_user_with_must_change_password_is_redirected_to_first_login_screen(): void
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'username' => 'new_user',
            'password' => Hash::make('default-pass-123'),
            'must_change_password' => true,
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'new_user',
            'password' => 'default-pass-123',
        ]);

        $response->assertRedirect('/password/first-login');
    }

    public function test_rate_limiting_throttles_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'username' => 'target_user',
            'password' => Hash::make('correct-pass-123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'login' => 'target_user',
                'password' => 'wrong-pass',
            ])->assertSessionHasErrors();
        }

        $sixthAttempt = $this->from('/login')->post('/login', [
            'login' => 'target_user',
            'password' => 'wrong-pass',
        ]);

        $sixthAttempt->assertSessionHasErrors('login');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('login'));
    }
}
