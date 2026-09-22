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
            ->withHeaders($this->inertiaHeaders())
            ->get('/portal/calendar');

        $response->assertOk();

        $events = collect($response->json('props.events'));
        $event = $events->firstWhere('title', 'Start of School');

        $this->assertNotNull($event);
        $this->assertSame('2026-08-25', $event['event_date_key']);
        $this->assertStringStartsWith('2026-08-25', $event['event_date']);
    }

    public function test_portal_event_form_submission_is_saved_and_shown_on_calendar(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($admin)->post('/portal/posts', [
            'type' => 'event',
            'is_school_closure' => '0',
            'is_public' => '0',
            'audience' => 'all',
            'target_grade_id' => '',
            'target_teacher_id' => '',
            'title' => 'test',
            'content' => 'test',
            'image' => '',
            'event_start_date' => '2026-09-17T13:19',
            'event_end_date' => '',
            'button_text' => '',
            'button_url' => '',
            'recurrence_type' => 'none',
            'recurrence_end_date' => '',
            'publish_now' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('portal.posts.index'));

        $post = Post::where('title', 'test')->first();
        $this->assertNotNull($post);
        $this->assertSame('event', $post->type);
        $this->assertSame('all', $post->audience);
        $this->assertNotNull($post->published_at);
        $this->assertSame('2026-09-17 13:19:00', $post->event_start_date->format('Y-m-d H:i:s'));

        $calendar = $this->actingAs($admin)
            ->withHeaders($this->inertiaHeaders())
            ->get('/portal/calendar');

        $calendar->assertOk();
        $event = collect($calendar->json('props.events'))->firstWhere('title', 'test');
        $this->assertNotNull($event);
        $this->assertSame('2026-09-17', $event['event_date_key']);
    }

    public function test_event_form_accepts_empty_nullable_fields_sent_as_null_strings(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($admin)->post('/portal/posts', [
            'type' => 'event',
            'is_school_closure' => '0',
            'is_public' => '0',
            'audience' => '',
            'target_grade_id' => 'null',
            'target_teacher_id' => 'null',
            'title' => 'Open House Night',
            'content' => '<div>test</div>',
            'image' => 'null',
            'event_start_date' => '2026-09-17T13:19',
            'event_end_date' => 'null',
            'button_text' => '',
            'button_url' => 'null',
            'recurrence_type' => 'none',
            'recurrence_end_date' => '',
            'publish_now' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNotNull(Post::where('title', 'Open House Night')->first());
    }

    public function test_upcoming_scope_includes_events_later_the_same_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-10 09:00:00', 'America/New_York'));

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        Post::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'audience' => 'all',
            'title' => 'Afternoon Assembly',
            'content' => 'Gather in the gym',
            'event_start_date' => Carbon::parse('2026-09-10 12:30:00', 'America/New_York'),
            'published_at' => now(),
            'is_public' => true,
        ]);

        try {
            $this->assertTrue(
                Post::query()->upcoming()->where('title', 'Afternoon Assembly')->exists()
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    private function inertiaHeaders(): array
    {
        $version = app(\App\Http\Middleware\HandleInertiaRequests::class)
            ->version(request());

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) $version,
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }
}
