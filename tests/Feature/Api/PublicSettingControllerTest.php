<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_normalized_whatsapp_admin_number(): void
    {
        Setting::set('whatsapp_admin', '081234567890');

        $response = $this->getJson('/api/setting/whatsapp-admin');

        $response->assertOk();
        $response->assertJson(['number' => '6281234567890']);
    }

    public function test_returns_404_when_admin_number_not_configured(): void
    {
        Setting::query()->where('key', 'whatsapp_admin')->delete();

        $response = $this->getJson('/api/setting/whatsapp-admin');

        $response->assertStatus(404);
        $response->assertJsonPath('number', null);
    }

    public function test_returns_404_when_admin_number_is_empty_string(): void
    {
        Setting::set('whatsapp_admin', '');

        $response = $this->getJson('/api/setting/whatsapp-admin');

        $response->assertStatus(404);
    }
}
