<?php

namespace Tests\Feature;

use App\Models\SoftwareRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SoftwareRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Aplikasi Kasir Warung',
            'purpose' => 'Untuk catat pesanan kopi.',
            'target_users' => 'Saya sendiri',
            'problem_to_solve' => 'Sekarang masih manual di buku.',
            'similar_apps' => null,
            'platforms' => ['android', 'web'],
            'key_features' => '1. Input pesanan; 2. Print struk',
            'budget_range' => '1m-5m',
            'urgency' => '1-3-months',
            'additional_notes' => null,
        ], $overrides);
    }

    public function test_member_can_view_empty_index(): void
    {
        $member = $this->makeMember();
        $this->actingAs($member)
            ->get('/dashboard/software-requests')
            ->assertOk()
            ->assertSee('Belum ada request');
    }

    public function test_member_can_submit_request(): void
    {
        $member = $this->makeMember();
        $this->actingAs($member)
            ->post('/dashboard/software-requests', $this->basePayload())
            ->assertRedirect();

        $this->assertDatabaseHas('software_requests', [
            'user_id' => $member->id,
            'title' => 'Aplikasi Kasir Warung',
            'status' => 'pending',
        ]);

        $sr = SoftwareRequest::where('user_id', $member->id)->first();
        $this->assertEquals(['android', 'web'], $sr->platforms);
    }

    public function test_member_can_submit_with_attachment(): void
    {
        Storage::fake('local');
        $member = $this->makeMember();

        $this->actingAs($member)
            ->post('/dashboard/software-requests', $this->basePayload([
                'attachment' => UploadedFile::fake()->image('mockup.png', 800, 600),
            ]))
            ->assertRedirect();

        $sr = SoftwareRequest::where('user_id', $member->id)->first();
        $this->assertNotNull($sr->attachment_path);
        Storage::disk('local')->assertExists($sr->attachment_path);
    }

    public function test_member_cannot_submit_with_missing_required_fields(): void
    {
        $member = $this->makeMember();
        $this->actingAs($member)
            ->post('/dashboard/software-requests', [
                'title' => '',
                'platforms' => [],
            ])
            ->assertSessionHasErrors(['title', 'purpose', 'target_users', 'problem_to_solve', 'platforms', 'key_features']);
    }

    public function test_member_cannot_view_other_members_request(): void
    {
        $owner = $this->makeMember();
        $intruder = $this->makeMember();
        $sr = SoftwareRequest::create($this->basePayload([
            'user_id' => $owner->id,
        ]));

        $this->actingAs($intruder)
            ->get('/dashboard/software-requests/'.$sr->id)
            ->assertForbidden();
    }

    public function test_member_seeing_response_marks_user_seen_response_at(): void
    {
        $member = $this->makeMember();
        $sr = SoftwareRequest::create($this->basePayload([
            'user_id' => $member->id,
            'admin_response' => 'Diterima!',
            'admin_responded_at' => now(),
        ]));
        $this->assertNull($sr->user_seen_response_at);

        $this->actingAs($member)
            ->get('/dashboard/software-requests/'.$sr->id)
            ->assertOk()
            ->assertSee('Diterima!');

        $this->assertNotNull($sr->fresh()->user_seen_response_at);
    }

    public function test_admin_can_list_all_requests(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        SoftwareRequest::create($this->basePayload(['user_id' => $member->id]));
        SoftwareRequest::create($this->basePayload(['user_id' => $member->id, 'title' => 'Aplikasi Lain']));

        $this->actingAs($admin)
            ->get('/admin/software-requests')
            ->assertOk()
            ->assertSee('Aplikasi Kasir Warung')
            ->assertSee('Aplikasi Lain');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        SoftwareRequest::create($this->basePayload(['user_id' => $member->id, 'title' => 'Pending One']));
        SoftwareRequest::create($this->basePayload(['user_id' => $member->id, 'title' => 'Accepted One', 'status' => 'accepted']));

        $this->actingAs($admin)
            ->get('/admin/software-requests?status=accepted')
            ->assertOk()
            ->assertSee('Accepted One')
            ->assertDontSee('Pending One');
    }

    public function test_admin_can_update_status_and_response(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $sr = SoftwareRequest::create($this->basePayload(['user_id' => $member->id]));

        $this->actingAs($admin)
            ->put('/admin/software-requests/'.$sr->id, [
                'status' => 'accepted',
                'admin_response' => 'Diterima, kerjakan dalam 2 minggu.',
                'admin_notes' => 'Estimasi 80 jam, assign ke John.',
            ])
            ->assertRedirect();

        $sr->refresh();
        $this->assertEquals('accepted', $sr->status);
        $this->assertEquals('Diterima, kerjakan dalam 2 minggu.', $sr->admin_response);
        $this->assertEquals('Estimasi 80 jam, assign ke John.', $sr->admin_notes);
        $this->assertNotNull($sr->admin_responded_at);
    }

    public function test_admin_response_unchanged_does_not_reset_timestamp(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $original = now()->subDays(2);
        $sr = SoftwareRequest::create($this->basePayload([
            'user_id' => $member->id,
            'admin_response' => 'Same response',
            'admin_responded_at' => $original,
        ]));

        $this->actingAs($admin)
            ->put('/admin/software-requests/'.$sr->id, [
                'status' => 'reviewing',
                'admin_response' => 'Same response',
                'admin_notes' => 'Updated notes',
            ])
            ->assertRedirect();

        $this->assertEquals($original->format('Y-m-d H:i:s'), $sr->fresh()->admin_responded_at->format('Y-m-d H:i:s'));
    }

    public function test_admin_can_delete_request(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $sr = SoftwareRequest::create($this->basePayload(['user_id' => $member->id]));

        $this->actingAs($admin)
            ->delete('/admin/software-requests/'.$sr->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('software_requests', ['id' => $sr->id]);
    }

    public function test_non_admin_cannot_access_admin_index(): void
    {
        $member = $this->makeMember();
        $this->actingAs($member)
            ->get('/admin/software-requests')
            ->assertForbidden();
    }
}
