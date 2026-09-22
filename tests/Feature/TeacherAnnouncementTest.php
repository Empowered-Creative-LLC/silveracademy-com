<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\Post;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_includes_announcements_sent_directly_to_them(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_approved' => true,
            'name' => 'Kastin Krupinski',
        ]);
        $otherTeacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_approved' => true,
        ]);
        $grade = Grade::create([
            'name' => 'Kindergarten',
            'sort_order' => 3,
        ]);
        $teacher->grades()->attach($grade);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);

        $this->announcement($admin, 'All staff note', 'teachers_only');
        $this->announcement($admin, 'Kindergarten note', 'grade_teachers', [
            'target_grade_id' => $grade->id,
        ]);
        $this->announcement($admin, 'Note for Kastin', 'specific_teacher', [
            'target_teacher_id' => $teacher->id,
        ]);
        $this->announcement($admin, 'Note for someone else', 'specific_teacher', [
            'target_teacher_id' => $otherTeacher->id,
        ]);

        $titles = collect($this->inertiaProp($teacher, '/portal/dashboard', 'teacherAnnouncements'))
            ->pluck('title')
            ->all();

        $this->assertEqualsCanonicalizing(
            ['All staff note', 'Kindergarten note', 'Note for Kastin'],
            $titles,
        );
    }

    public function test_admin_grade_level_post_reaches_parents_and_the_teacher_dashboard(): void
    {
        $grade = Grade::create([
            'name' => 'Kindergarten Family',
            'sort_order' => 20,
        ]);
        $otherGrade = Grade::create([
            'name' => 'Other Grade',
            'sort_order' => 21,
        ]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
            'name' => 'School Admin',
        ]);
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'is_approved' => true,
        ]);
        $teacher->grades()->attach($grade);
        $parent = User::factory()->create([
            'role' => User::ROLE_PARENT,
            'is_approved' => true,
        ]);
        $student = Student::create([
            'name' => 'Kindergarten Student',
            'grade_id' => $grade->id,
            'status' => Student::STATUS_ACTIVE,
        ]);
        $parent->children()->attach($student);

        $this->announcement($admin, 'Admin kindergarten note', 'grade_teachers', [
            'target_grade_id' => $grade->id,
        ]);
        $this->announcement($teacher, 'Teacher kindergarten note', 'grade', [
            'target_grade_id' => $grade->id,
        ]);
        $this->announcement($admin, 'Other grade note', 'grade_teachers', [
            'target_grade_id' => $otherGrade->id,
        ]);
        $this->announcement($admin, 'Private staff note', 'specific_teacher', [
            'target_teacher_id' => $teacher->id,
        ]);

        $parentTitles = collect($this->inertiaProp($parent, '/portal/dashboard', 'gradeNews'))
            ->pluck('title')
            ->all();
        $teacherTitles = collect($this->inertiaProp($teacher, '/portal/dashboard', 'familyMessages'))
            ->pluck('title')
            ->all();

        $this->assertEqualsCanonicalizing(
            ['Admin kindergarten note', 'Teacher kindergarten note'],
            $parentTitles,
        );
        $this->assertEqualsCanonicalizing(
            ['Admin kindergarten note', 'Teacher kindergarten note'],
            $teacherTitles,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function announcement(User $author, string $title, string $audience, array $overrides = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $author->id,
            'type' => 'news',
            'audience' => $audience,
            'title' => $title,
            'content' => '<p>'.$title.'</p>',
            'published_at' => now(),
            'is_public' => false,
        ], $overrides));
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
