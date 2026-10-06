<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_news_post_saves_when_optional_fields_are_blank_placeholders(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'news',
                'title' => 'Friday note',
                'content' => '<p>Hello families</p>',
                'audience' => 'all',
                'target_grade_id' => 'null',
                'target_teacher_id' => '',
                'image' => 'null',
                'event_start_date' => '',
                'event_end_date' => 'null',
                'button_text' => '',
                'button_url' => 'undefined',
                'recurrence_end_date' => '',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $post = Post::where('title', 'Friday note')->first();

        $this->assertNotNull($post);
        $this->assertSame('news', $post->type);
        $this->assertSame('all', $post->audience);
        $this->assertNotNull($post->published_at);
        $this->assertNull($post->target_grade_id);
        $this->assertNull($post->button_url);
    }

    public function test_grade_level_news_is_saved_for_the_selected_grade(): void
    {
        $admin = $this->admin();
        $grade = Grade::create([
            'name' => 'Kindergarten',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'news',
                'title' => 'Kindergarten note',
                'audience' => 'grade_teachers',
                'target_grade_id' => (string) $grade->id,
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $post = Post::where('title', 'Kindergarten note')->first();

        $this->assertNotNull($post);
        $this->assertSame('grade_teachers', $post->audience);
        $this->assertSame($grade->id, (int) $post->target_grade_id);
    }

    public function test_empty_news_content_is_rejected_and_not_saved(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/portal/posts/create')
            ->post('/portal/posts', $this->payload([
                'type' => 'news',
                'title' => 'Untitled draft',
                'content' => '',
            ]))
            ->assertRedirect('/portal/posts/create')
            ->assertSessionHasErrors('content');

        $this->assertDatabaseMissing('posts', ['title' => 'Untitled draft']);
    }

    public function test_internal_event_is_staff_only_and_external_event_is_public(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-22 09:00:00', 'America/New_York'));

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Staff meeting',
                'event_visibility' => 'internal',
                'event_start_date' => '2026-09-22T15:00',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Open house',
                'event_visibility' => 'external',
                'is_public' => '0',
                'audience' => 'teachers_only',
                'event_start_date' => '2026-09-23T18:00',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $internal = Post::where('title', 'Staff meeting')->first();
        $external = Post::where('title', 'Open house')->first();

        $this->assertFalse($internal->is_public);
        $this->assertSame('teachers_only', $internal->audience);
        $this->assertTrue($external->is_public);
        $this->assertSame('all', $external->audience);

        $parent = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_approved' => true,
        ]);

        $parentTitles = $this->calendarTitles($parent);
        $staffTitles = $this->calendarTitles($teacher);

        $this->assertNotContains('Staff meeting', $parentTitles);
        $this->assertContains('Open house', $parentTitles);
        $this->assertContains('Staff meeting', $staffTitles);
        $this->assertContains('Open house', $staffTitles);

        $parentDashboard = $this->inertiaProp($parent, '/portal/dashboard', 'upcomingEvents');
        $staffDashboard = $this->inertiaProp($teacher, '/portal/dashboard', 'upcomingEvents');

        $this->assertNotContains('Staff meeting', collect($parentDashboard)->pluck('title')->all());
        $this->assertContains('Open house', collect($parentDashboard)->pluck('title')->all());
        $this->assertContains('Staff meeting', collect($staffDashboard)->pluck('title')->all());

        $publicPage = $this->get('/news-events');
        $publicPage->assertOk();
        $publicPage->assertSee('Open house');
        $publicPage->assertDontSee('Staff meeting');
    }

    public function test_same_day_evening_event_stays_upcoming_in_eastern_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-22 21:00:00', 'America/New_York'));

        $admin = $this->admin();

        Post::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'audience' => 'all',
            'title' => 'Evening concert',
            'content' => 'Tonight',
            'event_start_date' => Carbon::parse('2026-09-22 22:00:00', 'America/New_York'),
            'published_at' => now(),
            'is_public' => true,
        ]);

        $this->assertTrue(
            Post::query()->published()->upcoming()->where('title', 'Evening concert')->exists()
        );
    }

    public function test_school_closure_stays_visible_to_families_when_marked_internal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-22 09:00:00', 'America/New_York'));

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Snow day',
                'event_visibility' => 'internal',
                'is_school_closure' => '1',
                'event_start_date' => '2026-09-24T08:00',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $closure = Post::where('title', 'Snow day')->first();

        $this->assertTrue($closure->is_school_closure);
        $this->assertFalse($closure->is_public);
        $this->assertSame('all', $closure->audience);

        $parent = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);

        $this->assertContains('Snow day', $this->calendarTitles($parent));
        $this->get('/news-events')->assertDontSee('Snow day');
    }

    public function test_families_event_stays_in_the_portal_and_off_the_public_site(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 09:00:00', 'America/New_York'));

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Family picnic',
                'event_visibility' => 'families',
                'event_start_date' => '2026-11-04T16:00',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Public gala',
                'event_visibility' => 'external',
                'event_start_date' => '2026-11-05T18:00',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Staff retreat',
                'event_visibility' => 'internal',
                'event_start_date' => '2026-11-06T15:00',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $families = Post::where('title', 'Family picnic')->first();

        $this->assertNotNull($families);
        $this->assertFalse($families->is_public);
        $this->assertSame('all', $families->audience);

        $parent = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_approved' => true,
        ]);

        $parentTitles = $this->calendarTitles($parent);
        $this->assertContains('Family picnic', $parentTitles);
        $this->assertContains('Public gala', $parentTitles);
        $this->assertNotContains('Staff retreat', $parentTitles);
        $this->assertContains('Family picnic', $this->calendarTitles($teacher));

        $parentDashboard = collect($this->inertiaProp($parent, '/portal/dashboard', 'upcomingEvents'))
            ->pluck('title')
            ->all();
        $this->assertContains('Family picnic', $parentDashboard);
        $this->assertNotContains('Staff retreat', $parentDashboard);

        $publicPage = $this->get('/news-events');
        $publicPage->assertOk();
        $publicPage->assertSee('Public gala');
        $publicPage->assertDontSee('Family picnic');
        $publicPage->assertDontSee('Staff retreat');
    }

    public function test_all_day_event_saves_the_calendar_day_without_a_clock_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 09:00:00', 'America/New_York'));

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/portal/posts', $this->payload([
                'type' => 'event',
                'title' => 'Winter break',
                'event_visibility' => 'families',
                'is_all_day' => '1',
                'event_start_date' => '2026-12-25',
                'publish_now' => '1',
            ]))
            ->assertRedirect(route('portal.posts.index'));

        $post = Post::where('title', 'Winter break')->first();

        $this->assertNotNull($post);
        $this->assertTrue($post->is_all_day);
        $this->assertSame(
            '2026-12-25 00:00:00',
            $post->event_start_date->timezone('America/New_York')->format('Y-m-d H:i:s')
        );

        $holiday = collect($this->inertiaProp($admin, '/portal/calendar', 'events'))
            ->firstWhere('title', 'Winter break');

        $this->assertNotNull($holiday);
        $this->assertTrue($holiday['is_all_day']);
    }

    public function test_posts_index_sorts_by_date_and_event_name(): void
    {
        $admin = $this->admin();

        Post::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'audience' => 'all',
            'title' => 'Band concert',
            'content' => 'Music',
            'event_start_date' => Carbon::parse('2026-12-01 15:00:00', 'America/New_York'),
            'is_public' => true,
        ]);
        Post::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'audience' => 'all',
            'title' => 'Assembly',
            'content' => 'Gather',
            'event_start_date' => Carbon::parse('2026-11-01 09:00:00', 'America/New_York'),
            'is_public' => true,
        ]);
        Post::create([
            'user_id' => $admin->id,
            'type' => 'news',
            'audience' => 'all',
            'title' => 'Newsletter',
            'content' => 'Note',
            'published_at' => Carbon::parse('2026-10-15 08:00:00', 'America/New_York'),
        ]);
        Post::create([
            'user_id' => $admin->id,
            'type' => 'event',
            'audience' => 'teachers_only',
            'title' => 'Undated rehearsal',
            'content' => 'TBD',
            'event_start_date' => null,
            'is_public' => false,
        ]);

        $byDate = collect($this->inertiaProp($admin, '/portal/posts?sort=date&direction=asc', 'posts')['data'])
            ->pluck('title')
            ->all();

        $this->assertSame(
            ['Newsletter', 'Assembly', 'Band concert', 'Undated rehearsal'],
            $byDate
        );

        $byDateDesc = collect($this->inertiaProp($admin, '/portal/posts?sort=date&direction=desc', 'posts')['data'])
            ->pluck('title')
            ->all();

        $this->assertSame(
            ['Band concert', 'Assembly', 'Newsletter', 'Undated rehearsal'],
            $byDateDesc
        );

        $byTitle = collect($this->inertiaProp($admin, '/portal/posts?sort=title&direction=asc', 'posts')['data'])
            ->pluck('title')
            ->all();

        $this->assertSame(
            ['Assembly', 'Band concert', 'Newsletter', 'Undated rehearsal'],
            $byTitle
        );

        $filtered = $this->inertiaProp($admin, '/portal/posts?sort=title&direction=asc&search=Assembly&type=event', 'posts');
        $this->assertSame(['Assembly'], collect($filtered['data'])->pluck('title')->all());

        $filters = $this->inertiaProp($admin, '/portal/posts?sort=title&direction=asc&search=Assembly&type=event', 'filters');
        $this->assertSame('Assembly', $filters['search']);
        $this->assertSame('event', $filters['type']);
        $this->assertSame('title', $filters['sort']);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides): array
    {
        return array_merge([
            'type' => 'news',
            'is_school_closure' => '0',
            'is_public' => '0',
            'event_visibility' => '',
            'audience' => 'all',
            'target_grade_id' => '',
            'target_teacher_id' => '',
            'title' => 'Post',
            'content' => '<p>Content</p>',
            'image' => '',
            'event_start_date' => '',
            'event_end_date' => '',
            'button_text' => '',
            'button_url' => '',
            'recurrence_type' => 'none',
            'recurrence_end_date' => '',
            'publish_now' => '1',
        ], $overrides);
    }

    /**
     * @return list<string>
     */
    private function calendarTitles(User $user): array
    {
        return collect($this->inertiaProp($user, '/portal/calendar', 'events'))
            ->pluck('title')
            ->all();
    }

    private function inertiaProp(User $user, string $url, string $prop): mixed
    {
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request()),
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get($url);

        $response->assertOk();

        return $response->json('props.'.$prop);
    }
}
