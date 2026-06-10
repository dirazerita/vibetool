<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    private function activeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'email_verified_at' => null,
        ], $overrides));
    }

    public function test_verification_page_shows_unverified_status(): void
    {
        $user = $this->activeUser();

        $this->actingAs($user)
            ->get(route('dashboard.email-verification'))
            ->assertOk()
            ->assertSee('Belum Terverifikasi')
            ->assertSee($user->email);
    }

    public function test_verification_page_shows_verified_status_for_verified_user(): void
    {
        $user = $this->activeUser(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('dashboard.email-verification'))
            ->assertOk()
            ->assertSee('Terverifikasi');
    }

    public function test_send_code_dispatches_mailable_and_persists_hash(): void
    {
        Mail::fake();

        $user = $this->activeUser();

        $this->actingAs($user)
            ->post(route('dashboard.email-verification.send'))
            ->assertRedirect(route('dashboard.email-verification'));

        Mail::assertSent(EmailVerificationCodeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && strlen($mail->code) === 6;
        });

        $user->refresh();
        $this->assertNotNull($user->email_verification_code_hash);
        $this->assertNotNull($user->email_verification_expires_at);
        $this->assertNotNull($user->email_verification_last_sent_at);
        $this->assertSame(0, $user->email_verification_attempts);
    }

    public function test_verify_succeeds_with_correct_code(): void
    {
        $user = $this->activeUser([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_expires_at' => now()->addMinutes(10),
            'email_verification_attempts' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.email-verification.verify'), ['code' => '123456'])
            ->assertRedirect(route('dashboard.email-verification'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->email_verification_code_hash);
        $this->assertNull($user->email_verification_expires_at);
    }

    public function test_verify_fails_with_wrong_code_and_increments_attempts(): void
    {
        $user = $this->activeUser([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_expires_at' => now()->addMinutes(10),
            'email_verification_attempts' => 0,
        ]);

        $this->actingAs($user)
            ->from(route('dashboard.email-verification'))
            ->post(route('dashboard.email-verification.verify'), ['code' => '999999'])
            ->assertRedirect(route('dashboard.email-verification'));

        $user->refresh();
        $this->assertNull($user->email_verified_at);
        $this->assertSame(1, $user->email_verification_attempts);
    }

    public function test_verify_fails_when_code_expired(): void
    {
        $user = $this->activeUser([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_expires_at' => now()->subMinute(),
            'email_verification_attempts' => 0,
        ]);

        $this->actingAs($user)
            ->from(route('dashboard.email-verification'))
            ->post(route('dashboard.email-verification.verify'), ['code' => '123456']);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_verify_blocks_after_max_attempts(): void
    {
        $user = $this->activeUser([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_expires_at' => now()->addMinutes(10),
            'email_verification_attempts' => 5,
        ]);

        $this->actingAs($user)
            ->from(route('dashboard.email-verification'))
            ->post(route('dashboard.email-verification.verify'), ['code' => '123456']);

        $user->refresh();
        // Even though correct code, blocked because attempts >= MAX.
        $this->assertNull($user->email_verified_at);
    }
}
