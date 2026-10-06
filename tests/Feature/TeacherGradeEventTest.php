<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Post;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherGradeEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_teacher_assigned_to_a_grade_can_add_an_event_families_see_with_their_name(): void
    {
        $grade = Grade::create(['name' => '1st Grade Family', 'sort_order' => 30]);
        $otherGrade = Grade::create(['name' => '2nd Grade Family', 'sort_order' => 31]);

        $firstTeacher = $this->teacher('Ava Cohen');
        $secondTeacher = $this->teacher('Jonah Levi');
        $outsider = $this->teacher('Unassigned Teacher');
        $firstTeacher->grades()->attach($grade);
        $secondTeacher->grades()->attach($grade);

        $parent = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);
        $otherParent = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);
        $student = Student::create([
            'name' => '1st Grade Student',
            'grade_id' => $grade->id,
            'status' => Student::STATUS_ACTIVE,
        ]);
        $otherStudent = Student::create([
            'name' => '2nd Grade Student',
            'grade_id' => $otherGrade->id,
            'status' => Student::STATUS_ACTIVE,
        ]);
        $parent->children()->attach($student);
        $otherParent->children()->attach($otherStudent);

        $this->actingAs($firstTeacher)
            ->post('/portal/teacher-events', $this->payload($grade->id, 'Field trip with Ava'))
            ->assertRedirect(route('portal.calendar'));

        $this->actingAs($secondTeacher)
            ->post('/portal/teacher-events', $this->payload($grade->id, 'Assembly with Jonah'))
            ->assertRedirect(route('portal.calendar'));

        $this->actingAs($outsider)
            ->post('/portal/teacher-events', $this->payload($grade->id, 'Should not save'))
            ->assertSessionHasErrors('target_grade_id');

        $this->assertNull(Post::where('title', 'Should not save')->first());

        $parentEvents = collect($this->inertiaProp($parent, '/portal/calendar', 'events'));
        $otherEvents = collect($this->inertiaProp($otherParent, '/portal/calendar', 'events'));

        $this->assertEqualsCanonicalizing(
            ['Field trip with Ava', 'Assembly with Jonah'],
            $parentEvents->pluck('title')->all(),
        );
        $this->assertEqualsCanonicalizing(
            ['Ava Cohen', 'Jonah Levi'],
            $parentEvents->pluck('author_name')->all(),
        );
        $this->assertSame([], $otherEvents->pluck('title')->all());

        $this->get('/news-events')->assertDontSee('Field trip with Ava');
    }

    public function test_all_day_grade_event_stores_the_date_without_a_time_and_stays_portal_only(): void
    {
        $grade = Grade::create(['name' => '3rd Grade', 'sort_order' => 33]);
        $teacher = $this->teacher('Maya Stein');
        $teacher->grades()->attach($grade);

        $this->actingAs($teacher)
            ->post('/portal/teacher-events', [
                'title' => 'Picture day',
                'content' => '<p>Wear blue</p>',
                'target_grade_id' => $grade->id,
                'event_start_date' => '2026-11-02',
                'event_end_date' => '',
                'is_all_day' => '1',
            ])
            ->assertRedirect(route('portal.calendar'));

        $post = Post::where('title', 'Picture day')->first();

        $this->assertNotNull($post);
        $this->assertTrue($post->is_all_day);
        $this->assertFalse($post->is_public);
        $this->assertSame('grade', $post->audience);
        $this->assertSame($grade->id, (int) $post->target_grade_id);
        $this->assertSame(
            '2026-11-02 00:00:00',
            $post->event_start_date->timezone('America/New_York')->format('Y-m-d H:i:s')
        );

        $pictureDay = collect($this->inertiaProp($teacher, '/portal/calendar', 'events'))
            ->firstWhere('title', 'Picture day');

        $this->assertNotNull($pictureDay);
        $this->assertTrue($pictureDay['is_all_day']);
        $this->get('/news-events')->assertDontSee('Picture day');
    }

    private function teacher(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => User::ROLE_TEACHER,
            'is_approved' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(int $gradeId, string $title): array
    {
        return [
            'title' => $title,
            'content' => '<p>'.$title.'</p>',
            'target_grade_id' => $gradeId,
            'event_start_date' => '2026-10-15T09:00',
            'event_end_date' => '',
        ];
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
