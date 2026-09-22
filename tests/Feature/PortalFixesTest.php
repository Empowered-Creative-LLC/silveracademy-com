<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\PortalPassword;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_passwords_are_alphanumeric_and_can_log_in(): void
    {
        $password = PortalPassword::generate();

        $this->assertSame(12, strlen($password));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', $password);
        $this->assertSame($password, e($password));

        $user = User::factory()->create([
            'email' => 'parent-login@example.com',
            'password' => $password,
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);

        $this->assertTrue(Hash::check($password, $user->fresh()->password));

        $this->post('/login', [
            'email' => 'parent-login@example.com',
            'password' => $password,
        ])->assertRedirect(route('portal.dashboard', absolute: false));
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_published_evening_event_is_keyed_to_eastern_calendar_day(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        Post::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'audience' => 'all',
            'title' => 'Start of School',
            'content' => 'First day',
            'event_start_date' => Carbon::parse('2026-08-25 20:00:00', 'America/New_York'),
            'published_at' => now(),
            'is_public' => false,
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request()),
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get('/portal/calendar');

        $response->assertOk();

        $events = collect($response->json('props.events'));
        $event = $events->firstWhere('title', 'Start of School');

        $this->assertNotNull($event);
        $this->assertSame('2026-08-25', $event['event_date_key']);
        $this->assertStringStartsWith('2026-08-25', $event['event_date']);
    }
}
