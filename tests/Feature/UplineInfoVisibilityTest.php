<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Memastikan kartu "Upline kamu" di /dashboard/products mengikuti setting
 * admin `show_upline_info`, dan admin bisa mengubah setting tersebut.
 */
class UplineInfoVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(?User $upline = null): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'referral_code' => 'U'.Str::upper(Str::random(6)),
            'upline_id' => $upline?->id,
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'referral_code' => 'A'.Str::upper(Str::random(6)),
        ]);
    }

    public function test_upline_card_shown_by_default(): void
    {
        $upline = $this->makeMember();
        $member = $this->makeMember($upline);

        // Tanpa setting tersimpan → default tampil.
        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertSee('Upline kamu');
    }

    public function test_upline_card_shown_when_setting_enabled(): void
    {
        Setting::set('show_upline_info', '1');
        $upline = $this->makeMember();
        $member = $this->makeMember($upline);

        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertSee('Upline kamu');
    }

    public function test_upline_card_hidden_when_setting_disabled(): void
    {
        Setting::set('show_upline_info', '0');
        $upline = $this->makeMember();
        $member = $this->makeMember($upline);

        $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk()
            ->assertDontSee('Upline kamu');
    }

    public function test_admin_can_disable_upline_info_setting(): void
    {
        $admin = $this->makeAdmin();
        Setting::set('whatsapp_admin', '082312181216');

        // Submit form settings tanpa checkbox show_upline_info → tersimpan '0'.
        $this->actingAs($admin)
            ->put('/admin/settings', [
                'whatsapp_admin' => '082312181216',
            ])
            ->assertRedirect(route('admin.settings'));

        $this->assertSame('0', Setting::get('show_upline_info'));
    }

    public function test_admin_can_enable_upline_info_setting(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'whatsapp_admin' => '082312181216',
                'show_upline_info' => '1',
            ])
            ->assertRedirect(route('admin.settings'));

        $this->assertSame('1', Setting::get('show_upline_info'));
    }
}
