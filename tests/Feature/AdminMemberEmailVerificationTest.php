<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Halaman admin "Semua Member": menampilkan status verifikasi email tiap member
 * dan filter terverifikasi / belum verifikasi.
 */
class AdminMemberEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'referral_code' => 'A'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeMember(bool $verified): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'email_verified_at' => $verified ? now() : null,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
    }

    public function test_index_shows_verification_status_and_counts(): void
    {
        $admin = $this->makeAdmin();
        $verified = $this->makeMember(true);
        $unverified = $this->makeMember(false);

        $response = $this->actingAs($admin)
            ->get('/admin/members')
            ->assertOk();

        $response->assertSee('Verifikasi Email');
        $response->assertSee('Terverifikasi');
        $response->assertSee('Belum');
        $response->assertSee($verified->name);
        $response->assertSee($unverified->name);
    }

    public function test_verified_filter_only_shows_verified_members(): void
    {
        $admin = $this->makeAdmin();
        $verified = $this->makeMember(true);
        $unverified = $this->makeMember(false);

        $response = $this->actingAs($admin)
            ->get('/admin/members?verification=verified')
            ->assertOk();

        $response->assertSee($verified->name);
        $response->assertDontSee($unverified->name);
    }

    public function test_unverified_filter_only_shows_unverified_members(): void
    {
        $admin = $this->makeAdmin();
        $verified = $this->makeMember(true);
        $unverified = $this->makeMember(false);

        $response = $this->actingAs($admin)
            ->get('/admin/members?verification=unverified')
            ->assertOk();

        $response->assertSee($unverified->name);
        $response->assertDontSee($verified->name);
    }

    public function test_verification_filter_combines_with_search(): void
    {
        $admin = $this->makeAdmin();
        $targetVerified = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'name' => 'Zulkifli Target',
            'email_verified_at' => now(),
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
        $otherUnverified = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'name' => 'Zulkifli Other',
            'email_verified_at' => null,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/members?search=Zulkifli&verification=verified')
            ->assertOk();

        $response->assertSee($targetVerified->name);
        $response->assertDontSee($otherUnverified->name);
    }
}
