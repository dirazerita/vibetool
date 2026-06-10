<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailValidationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_disposable_email_domain_is_rejected(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'fake@mailinator.com',
            'whatsapp_number' => '081234567800',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(0, User::where('email', 'fake@mailinator.com')->count());
    }

    public function test_yopmail_disposable_domain_is_rejected(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'foo@yopmail.com',
            'whatsapp_number' => '081234567801',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_normal_email_passes_validation(): void
    {
        config(['mail.skip_dns_check' => true]);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Real User',
            'email' => 'real-user@example.com',
            'whatsapp_number' => '081234567802',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Tidak boleh ada error pada field email.
        $response->assertSessionDoesntHaveErrors('email');
        $this->assertSame(1, User::where('email', 'real-user@example.com')->count());
    }

    public function test_api_register_rejects_disposable_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'API User',
            'email' => 'spam@10minutemail.com',
            'whatsapp_number' => '081234567803',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->json('errors') ?? []);
    }
}
