<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_table_has_the_four_initial_sitera_roles(): void
    {
        $this->seed();

        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertSame(
            [
                'pelapor_daerah',
                'verifikator_pusat',
                'pimpinan_kementan',
                'super_admin',
            ],
            \DB::table('roles')->orderBy('id')->pluck('code')->all(),
        );
    }

    public function test_users_reference_a_role(): void
    {
        $this->seed();

        $user = \App\Models\User::factory()->create([
            'role_id' => \DB::table('roles')->where('code', 'pelapor_daerah')->value('id'),
        ]);

        $this->assertNotNull($user->role);
        $this->assertSame('pelapor_daerah', $user->role->code);
    }
}
