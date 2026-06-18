<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin bisa upload bukti transfer saat/ setelah menyetujui penarikan, dan
 * member bisa melihat bukti tersebut di riwayat penarikannya.
 */
class WithdrawalTransferProofTest extends TestCase
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

    private function makeWithdrawal(User $member, string $status = 'pending'): Withdrawal
    {
        return Withdrawal::create([
            'user_id' => $member->id,
            'amount' => 100000,
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'status' => $status,
        ]);
    }

    public function test_admin_can_approve_with_transfer_proof(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $withdrawal = $this->makeWithdrawal($member);

        $this->actingAs($admin)
            ->post('/admin/withdrawals/'.$withdrawal->id.'/approve', [
                'transfer_proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();

        $withdrawal->refresh();
        $this->assertEquals('approved', $withdrawal->status);
        $this->assertNotNull($withdrawal->transfer_proof);
        Storage::disk('public')->assertExists($withdrawal->transfer_proof);
    }

    public function test_admin_can_approve_without_proof_then_upload_later(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $withdrawal = $this->makeWithdrawal($member);

        // Approve tanpa bukti.
        $this->actingAs($admin)
            ->post('/admin/withdrawals/'.$withdrawal->id.'/approve')
            ->assertRedirect();

        $withdrawal->refresh();
        $this->assertEquals('approved', $withdrawal->status);
        $this->assertNull($withdrawal->transfer_proof);

        // Upload bukti menyusul.
        $this->actingAs($admin)
            ->post('/admin/withdrawals/'.$withdrawal->id.'/upload-proof', [
                'transfer_proof' => UploadedFile::fake()->image('bukti.png'),
            ])
            ->assertRedirect();

        $withdrawal->refresh();
        $this->assertNotNull($withdrawal->transfer_proof);
        Storage::disk('public')->assertExists($withdrawal->transfer_proof);
    }

    public function test_upload_proof_rejected_for_non_approved_withdrawal(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $withdrawal = $this->makeWithdrawal($member, 'pending');

        $this->actingAs($admin)
            ->post('/admin/withdrawals/'.$withdrawal->id.'/upload-proof', [
                'transfer_proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($withdrawal->fresh()->transfer_proof);
    }

    public function test_approve_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $withdrawal = $this->makeWithdrawal($member);

        $this->actingAs($admin)
            ->post('/admin/withdrawals/'.$withdrawal->id.'/approve', [
                'transfer_proof' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('transfer_proof');

        // Tidak boleh ter-approve karena validasi gagal.
        $this->assertEquals('pending', $withdrawal->fresh()->status);
    }

    public function test_member_sees_transfer_proof_link_in_history(): void
    {
        Storage::fake('public');
        $member = $this->makeMember();
        $withdrawal = $this->makeWithdrawal($member, 'approved');
        $withdrawal->update(['transfer_proof' => 'transfer-proofs/bukti.jpg']);

        $response = $this->actingAs($member)
            ->get('/dashboard/withdrawals')
            ->assertOk();

        $response->assertSee('Lihat Bukti');
        $response->assertSee('storage/transfer-proofs/bukti.jpg');
    }
}
