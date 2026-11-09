<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AuditLogController;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware(['web', 'auth', 'active', 'role:super_admin'])
            ->get('/_test/audit', [AuditLogController::class, 'index']);
    }

    public function test_admin_can_search_and_combine_filters_with_inclusive_dates(): void
    {
        $admin = $this->admin();
        $match = app(AuditLogger::class)->log($admin, 'user.created', 'success', 'user', 42);
        $match->forceFill(['created_at' => '2026-11-05 23:59:59'])->save();
        app(AuditLogger::class)->log($admin, 'user.deleted', 'success', 'user', 43);
        $response = $this->actingAs($admin)->get('/_test/audit?q=42&action=user.created&result=success&date_from=2026-11-05&date_to=2026-11-05&per_page=10');
        $response->assertOk()->assertViewHas('auditLogs', fn ($logs) => $logs->total() === 1 && $logs->first()->id === $match->id);
        $response->assertSee('user.created')->assertSee('42');
    }

    public function test_guest_and_every_non_admin_role_are_denied(): void
    {
        $this->get('/_test/audit')->assertRedirect(route('login'));
        foreach (['pelapor_daerah', 'verifikator_pusat', 'pimpinan_kementan'] as $role) {
            $user = User::factory()->create(['role_id' => Role::firstOrCreate(['code' => $role], ['name' => $role])->id]);
            $this->actingAs($user)->get('/_test/audit')->assertForbidden();
        }
    }

    public function test_invalid_filters_and_unbounded_pagination_are_rejected(): void
    {
        $this->actingAs($this->admin());
        foreach ([['q' => ['x']], ['q' => str_repeat('x', 201)], ['action' => 'unknown'], ['result' => 'unknown'], ['date_from' => 'invalid'], ['date_from' => '2026-11-06', 'date_to' => '2026-11-05'], ['per_page' => 101], ['per_page' => 0], ['page' => 0], ['page' => 'abc']] as $filters) {
            $this->getJson('/_test/audit?'.http_build_query($filters))->assertUnprocessable();
        }
    }

    public function test_pagination_is_stable_preserves_filters_and_is_read_only(): void
    {
        $admin = $this->admin();
        for ($i = 1; $i <= 12; $i++) {
            app(AuditLogger::class)->log($admin, 'user.created', 'success', 'user', $i);
        }
        $this->actingAs($admin)->get('/_test/audit?action=user.created&per_page=10&page=2')
            ->assertOk()->assertViewHas('auditLogs', fn ($logs) => $logs->count() === 2 && $logs->total() === 12)
            ->assertSee('action=user.created', false);
        $this->assertDatabaseCount('audit_logs', 12);
        $this->actingAs($admin)->post('/_test/audit')->assertStatus(405);
    }

    public function test_partial_escapes_actor_names(): void
    {
        $admin = $this->admin();
        $admin->update(['name' => '<script>alert(1)</script>']);
        app(AuditLogger::class)->log($admin, 'user.created', 'success');
        $this->actingAs($admin)->get('/_test/audit')->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_upper_date_bound_can_be_used_alone_and_search_treats_wildcards_literally(): void
    {
        $admin = $this->admin();
        app(AuditLogger::class)->log($admin, 'user.created', 'success', 'user', 42);
        $this->actingAs($admin)->get('/_test/audit?date_to=2099-01-01')->assertOk();
        $this->get('/_test/audit?q=%25')->assertOk()->assertViewHas('auditLogs', fn ($logs) => $logs->total() === 0);
    }

    public function test_logger_rejects_unapproved_actions_without_writing(): void
    {
        try {
            app(AuditLogger::class)->log(null, 'password=secret', 'success');
            $this->fail('An unapproved action must be rejected.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('audit_logs', 0);
        }
    }

    private function admin(): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin'])->id, 'is_active' => true, 'must_change_password' => false]);
    }

    public function test_logger_persists_only_explicit_safe_metadata(): void
    {
        $actor = User::factory()->create();
        $log = app(AuditLogger::class)->log($actor, 'user.created', 'success', 'user', 42, [
            'count' => 2, 'password' => 'secret-value', 'token' => 'secret-token',
            'request' => ['email' => 'private@example.test'], 'status' => ['password' => 'nested-secret'],
        ]);

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'actor_id' => $actor->id, 'subject_id' => '42']);
        $this->assertSame(['count' => 2], $log->fresh()->metadata);
        $this->assertSame('user.created', $log->action);
        $actor->delete();
        $this->assertSame($actor->id, $log->fresh()->actor->id);
    }
}
