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

    public function test_verification_page_renders_when_code_already_sent(): void
    {
        // Regression: last_sent_at / expires_at must be cast to Carbon so the
        // view can call ->diffForHumans() / ->isFuture() on them. Without the
        // cast these are plain strings and the page 500s once a code has been
        // sent (reproduces the production-only error).
        $user = $this->activeUser([
            'email_verification_code_hash' => Hash::make('123456'),
            'email_verification_expires_at' => now()->addMinutes(10),
            'email_verification_last_sent_at' => now()->subSeconds(120),
            'email_verification_attempts' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.email-verification'))
            ->assertOk()
            ->assertSee('Belum Terverifikasi');
    }

    public function test_cooldown_is_zero_when_last_code_sent_long_ago(): void
    {
        // Regression: Carbon 3 returns a SIGNED diffInSeconds(), so the old
        // cooldown math produced an ever-growing positive number when
        // last_sent_at was in the past, permanently disabling the
        // "Kirim Kode" button. With a send 2 hours ago the cooldown must be 0.
        $user = $this->activeUser([
            'email_verification_last_sent_at' => now()->subHours(2),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.email-verification'))
            ->assertOk()
            ->assertSee('{ cooldown: 0 }');
    }

    public function test_cooldown_is_within_window_for_recent_send(): void
    {
        // Sent 10s ago → remaining cooldown must be between 1 and 60 (never
        // negative, never inflated).
        $user = $this->activeUser([
            'email_verification_last_sent_at' => now()->subSeconds(10),
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard.email-verification'))
            ->assertOk();

        $this->assertMatchesRegularExpression(
            '/\{ cooldown: (?:[1-9]|[1-5]\d|60) \}/',
            $response->getContent(),
        );
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
