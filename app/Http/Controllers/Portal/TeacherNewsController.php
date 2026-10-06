<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherNewsController extends Controller
{
    /**
     * Ensure only teachers and admins can access.
     */
    protected function authorizeStaffAccess(): void
    {
        $user = auth()->user();
        if (!$user->isTeacher() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Only staff members can access this feature.');
        }
    }

    /**
     * Show the form for creating a new grade-specific news post.
     */
    public function create(): Response
    {
        $this->authorizeStaffAccess();

        return Inertia::render('Portal/TeacherNews/Create', [
            'grades' => $this->gradesForCurrentUser(),
        ]);
    }

    /**
     * Store a new grade-specific news post.
     */
    public function store(Request $request)
    {
        $this->authorizeStaffAccess();
        
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_grade_id' => 'required|exists:grades,id',
        ]);

        // Admins can post to any grade, teachers only to their assigned grades
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $teacherGradeIds = $user->grades()->pluck('grades.id')->toArray();
            if (!in_array($validated['target_grade_id'], $teacherGradeIds)) {
                return back()->withErrors(['target_grade_id' => 'You are not assigned to this grade.']);
            }
        }

        // Create the news post with grade-specific audience
        Post::create([
            'user_id' => $user->id,
            'type' => 'news',
            'audience' => 'grade', // This is for parents of this grade
            'target_grade_id' => $validated['target_grade_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_public' => false, // Portal only, not public website
            'published_at' => now(),
        ]);

        return redirect()->route('portal.dashboard')->with('success', 'News posted successfully to parents!');
    }

    /**
     * Show the form for a grade-specific calendar event.
     */
    public function createEvent(): Response
    {
        $this->authorizeStaffAccess();

        return Inertia::render('Portal/TeacherEvents/Create', [
            'grades' => $this->gradesForCurrentUser(),
        ]);
    }

    /**
     * Store a calendar event for one grade's families.
     */
    public function storeEvent(Request $request)
    {
        $this->authorizeStaffAccess();

        $user = auth()->user();

        if ($request->input('event_end_date') === '') {
            $request->merge(['event_end_date' => null]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_grade_id' => 'required|exists:grades,id',
            'is_all_day' => 'boolean',
            'event_start_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
        ]);

        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $teacherGradeIds = $user->grades()->pluck('grades.id')->all();
            if (! in_array((int) $validated['target_grade_id'], array_map('intval', $teacherGradeIds), true)) {
                return back()->withErrors(['target_grade_id' => 'You are not assigned to this grade.']);
            }
        }

        $isAllDay = $request->boolean('is_all_day');
        $eventStartDate = Carbon::parse($validated['event_start_date'], 'America/New_York');
        $eventEndDate = ! empty($validated['event_end_date'])
            ? Carbon::parse($validated['event_end_date'], 'America/New_York')
            : null;

        if ($isAllDay) {
            $eventStartDate = $eventStartDate->startOfDay();
            $eventEndDate = $eventEndDate?->startOfDay();
        }

        Post::create([
            'user_id' => $user->id,
            'type' => 'event',
            'audience' => 'grade',
            'target_grade_id' => $validated['target_grade_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_public' => false,
            'is_all_day' => $isAllDay,
            'event_start_date' => $eventStartDate,
            'event_end_date' => $eventEndDate,
            'published_at' => now(),
        ]);

        return redirect()->route('portal.calendar')->with('success', 'Event added for this grade\'s families.');
    }

    /**
     * Grades this user may post to. Admins can post to every grade.
     */
    protected function gradesForCurrentUser()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return Grade::orderBy('sort_order')->get(['id', 'name']);
        }

        return $user->grades()->orderBy('grades.sort_order')->get(['grades.id', 'grades.name']);
    }

    /**
     * List teacher's posted news.
     */
    public function index(): Response
    {
        $this->authorizeStaffAccess();
        
        $user = auth()->user();

        $query = Post::with(['targetGrade', 'author'])
            ->where('type', 'news')
            ->whereIn('audience', ['grade', 'grade_teachers'])
            ->orderBy('created_at', 'desc');

        // Admins see every grade message. Teachers see messages for their grades.
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $gradeIds = $user->grades()->pluck('grades.id')->all();
            $query->where(function ($gradeQuery) use ($user, $gradeIds) {
                $gradeQuery->where('user_id', $user->id);
                if (! empty($gradeIds)) {
                    $gradeQuery->orWhereIn('target_grade_id', $gradeIds);
                }
            });
        }

        $posts = $query->get();

        return Inertia::render('Portal/TeacherNews/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Delete a teacher's news post.
     */
    public function destroy(Post $post)
    {
        $this->authorizeStaffAccess();
        
        $user = auth()->user();

        // Admins can delete any grade news, teachers can only delete their own
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $post->user_id !== $user->id) {
            abort(403, 'You can only delete your own posts.');
        }

        $post->delete();

        return back()->with('success', 'News post deleted.');
    }
}
