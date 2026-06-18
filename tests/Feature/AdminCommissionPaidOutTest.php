<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Halaman komisi admin menampilkan komisi yang sudah DIBAYARKAN ke member
 * (penarikan disetujui), beserta detail riwayat pembayaran per member.
 */
class AdminCommissionPaidOutTest extends TestCase
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

    private function makeMember(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'balance' => 0,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'title' => 'Produk Komisi '.Str::random(5),
            'slug' => 'produk-komisi-'.Str::lower(Str::random(6)),
            'description' => 'desc',
            'price' => 100000,
            'commission_percent' => 30,
            'commission_percent_non_owner' => 30,
            'upline_percent' => 10,
            'upline_percent_non_owner' => 10,
            'creator_share_percent' => 0,
            'product_type' => 'digital',
            'license_duration' => 'lifetime',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
    }

    private function giveCommission(User $member): void
    {
        $buyer = $this->makeMember();
        $product = $this->makeProduct();
        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);
        Commission::create([
            'user_id' => $member->id,
            'order_id' => $order->id,
            'type' => 'direct',
            'amount' => 30000,
            'status' => 'approved',
        ]);
    }

    public function test_index_shows_total_paid_out_from_approved_withdrawals(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $this->giveCommission($member);

        // Penarikan disetujui (sudah dibayarkan) + pending (belum).
        Withdrawal::create(['user_id' => $member->id, 'amount' => 20000, 'bank_name' => 'BCA', 'bank_account' => '123', 'status' => 'approved']);
        Withdrawal::create(['user_id' => $member->id, 'amount' => 5000, 'bank_name' => 'BCA', 'bank_account' => '123', 'status' => 'pending']);
        // Penarikan ditolak tidak boleh terhitung sebagai dibayarkan.
        Withdrawal::create(['user_id' => $member->id, 'amount' => 99000, 'bank_name' => 'BCA', 'bank_account' => '123', 'status' => 'rejected']);

        $response = $this->actingAs($admin)
            ->get('/admin/commissions')
            ->assertOk();

        $response->assertSee('Komisi Dibayarkan');
        // Total dibayarkan = 20.000 (hanya approved).
        $response->assertSee('Rp 20.000');
    }

    public function test_show_lists_payout_history_and_paid_out_stat(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $this->giveCommission($member);

        Withdrawal::create(['user_id' => $member->id, 'amount' => 20000, 'bank_name' => 'Mandiri', 'bank_account' => '999', 'status' => 'approved']);

        $response = $this->actingAs($admin)
            ->get('/admin/commissions/'.$member->id)
            ->assertOk();

        $response->assertSee('Riwayat Pembayaran Komisi');
        $response->assertSee('Disetujui (Dibayarkan)');
        $response->assertSee('Mandiri');
        $response->assertSee('Rp 20.000');
    }

    public function test_rejected_withdrawal_not_counted_as_paid_in_show(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $this->giveCommission($member);

        Withdrawal::create(['user_id' => $member->id, 'amount' => 50000, 'bank_name' => 'BNI', 'bank_account' => '777', 'status' => 'rejected']);

        $response = $this->actingAs($admin)
            ->get('/admin/commissions/'.$member->id)
            ->assertOk();

        // Tampil di riwayat sebagai Ditolak, tapi tidak terhitung dibayarkan.
        $response->assertSee('Ditolak');
        $response->assertSee('Riwayat Pembayaran Komisi');
    }
}
