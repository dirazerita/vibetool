<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberRegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_pending_user_with_normalized_whatsapp_number(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Budi Tester',
            'email' => 'budi@example.com',
            'whatsapp_number' => '081234567890',
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('status', 'pending');
        $response->assertJsonPath('user.email', 'budi@example.com');
        $response->assertJsonPath('user.whatsapp_number', '6281234567890');

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('pending', $user->status);
        $this->assertTrue(Hash::check('Rahasia123!', $user->password));
    }

    public function test_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'existing@example.com',
            'whatsapp_number' => '081234567891',
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $response->assertJsonPath('error', 'validation_error');
        $response->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_rejects_password_mismatch(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'newuser@example.com',
            'whatsapp_number' => '081234567892',
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Berbeda123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'validation_error');
    }

    public function test_rejects_invalid_email_format(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'not-an-email',
            'whatsapp_number' => '081234567893',
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_allows_null_whatsapp_number(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Tanpa WA',
            'email' => 'no-wa@example.com',
            'password' => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('user.whatsapp_number', null);
    }
}
