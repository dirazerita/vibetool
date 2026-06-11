<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Privasi per-member: kartu "Upline kamu" di /dashboard/products hanya tampil
 * kalau UPLINE dari member yang melihat mengizinkan (users.show_upline_info).
 * Jadi tiap member bisa menyembunyikan kontaknya sendiri dari para downline-nya.
 */
class UplinePrivacyToggleTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ], $overrides));
    }

    public function test_card_shown_when_upline_allows(): void
    {
        $upline = $this->makeMember(['show_upline_info' => true]);
        $member = $this->makeMember(['upline_id' => $upline->id]);

        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertSee('Upline kamu');
    }

    public function test_card_hidden_when_upline_opts_out(): void
    {
        $upline = $this->makeMember(['show_upline_info' => false]);
        $member = $this->makeMember(['upline_id' => $upline->id]);

        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertDontSee('Upline kamu');
    }

    public function test_card_shown_by_default_for_new_users(): void
    {
        // Default kolom true → perilaku lama (selalu tampil) tetap.
        $upline = $this->makeMember();
        $member = $this->makeMember(['upline_id' => $upline->id]);

        $this->assertTrue((bool) $upline->fresh()->show_upline_info);

        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertSee('Upline kamu');
    }

    public function test_member_without_upline_sees_no_card(): void
    {
        $member = $this->makeMember(['upline_id' => null]);

        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertDontSee('Upline kamu');
    }

    public function test_member_can_disable_sharing_via_settings(): void
    {
        $member = $this->makeMember(['show_upline_info' => true]);

        // Submit form pengaturan tanpa checkbox show_upline_info → tersimpan false.
        $this->actingAs($member)
            ->put('/dashboard/settings', [
                'name' => $member->name,
                'referral_code' => $member->referral_code,
            ])
            ->assertRedirect();

        $this->assertFalse((bool) $member->fresh()->show_upline_info);
    }

    public function test_member_can_enable_sharing_via_settings(): void
    {
        $member = $this->makeMember(['show_upline_info' => false]);

        $this->actingAs($member)
            ->put('/dashboard/settings', [
                'name' => $member->name,
                'referral_code' => $member->referral_code,
                'show_upline_info' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue((bool) $member->fresh()->show_upline_info);
    }
}
