<?php

namespace Tests\Feature;

use App\Models\CpclRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CpclSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_only_sees_cpcl_in_their_assigned_region(): void
    {
        $reporter = $this->reporter('Jawa Barat', 'Subang');
        $local = CpclRecord::create($this->cpclData('Jawa Barat', 'Subang', 'DI Pamanukan'));
        CpclRecord::create($this->cpclData('Jawa Barat', 'Indramayu', 'DI Indramayu'));

        $this->actingAs($reporter)->get('/laporan/buat')
            ->assertOk()
            ->assertSee('DI Pamanukan')
            ->assertDontSee('DI Indramayu')
            ->assertSee(route('reports.package.create', $local), false);
    }

    public function test_reporter_without_assigned_region_is_denied_cpcl_selection(): void
    {
        $reporter = $this->reporter(null, null);
        CpclRecord::create($this->cpclData('Jawa Barat', 'Subang', 'DI Pamanukan'));

        $this->actingAs($reporter)->get('/laporan/buat')
            ->assertOk()
            ->assertSee('Wilayah akun belum ditetapkan')
            ->assertDontSee('DI Pamanukan');
    }

    public function test_empty_search_and_out_of_range_page_keep_filter_and_reset_controls(): void
    {
        $reporter = $this->reporter('Jawa Barat', 'Subang');
        CpclRecord::create($this->cpclData('Jawa Barat', 'Subang', 'DI Pamanukan'));
        foreach (['q=tidak-ada', 'page=999'] as $query) {
            $this->actingAs($reporter)->get('/laporan/buat?'.$query)->assertOk()
                ->assertSee('name="q"', false)->assertSee('Reset')
                ->assertDontSee('Super Admin perlu memasukkan data CPCL');
        }
    }

    private function reporter(?string $province, ?string $regency): User
    {
        $role = Role::firstOrCreate(['code' => 'pelapor_daerah'], ['name' => 'Pelapor Daerah']);

        return User::factory()->create(['role_id' => $role->id, 'provinsi' => $province, 'kabupaten' => $regency]);
    }

    private function cpclData(string $province, string $regency, string $di): array
    {
        return [
            'no' => '1', 'nama_di' => $di, 'provinsi' => $province, 'kabupaten' => $regency,
            'kecamatan' => 'Pamanukan', 'desa' => 'Mulyasari', 'kel_tani' => 'P3A Tirta',
        ];
    }
}
