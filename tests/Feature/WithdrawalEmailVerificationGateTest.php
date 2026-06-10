<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalEmailVerificationGateTest extends TestCase
{
    use RefreshDatabase;

    private function activeMember(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'balance' => 200000,
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'email_verified_at' => null,
        ], $overrides));
    }

    public function test_unverified_email_blocks_withdrawal(): void
    {
        $user = $this->activeMember(['email_verified_at' => null]);

        $this->actingAs($user)
            ->from(route('dashboard.withdrawals'))
            ->post(route('dashboard.withdrawals.store'), ['amount' => 50000])
            ->assertRedirect(route('dashboard.withdrawals'));

        $this->assertSame(0, Withdrawal::where('user_id', $user->id)->count());

        // Banner pada halaman withdrawal harus muncul.
        $this->actingAs($user)
            ->get(route('dashboard.withdrawals'))
            ->assertSee('Email belum terverifikasi');
    }

    public function test_verified_email_allows_withdrawal(): void
    {
        $user = $this->activeMember(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->from(route('dashboard.withdrawals'))
            ->post(route('dashboard.withdrawals.store'), ['amount' => 50000])
            ->assertRedirect(route('dashboard.withdrawals'));

        $this->assertSame(1, Withdrawal::where('user_id', $user->id)->count());
    }
}
