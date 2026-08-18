<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LunchMenu;
use App\Models\Post;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Get counts for admin dashboard
        $studentCount = Student::count();
        $staffCount = User::whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->count();
        $upcomingEventsCount = Post::where('type', 'event')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('event_start_date', '>=', now())
            ->count();

        // Get this week's lunch menus (Monday-Friday)
        $monday = Carbon::now()->startOfWeek();
        $friday = Carbon::now()->startOfWeek()->addDays(4);
        
        $thisWeekLunchMenus = LunchMenu::whereBetween('menu_date', [$monday, $friday])
            ->orderBy('menu_date', 'asc')
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'menu_date' => $menu->menu_date->format('Y-m-d'),
                    'content' => $menu->content,
                    'day_name' => $menu->day_name,
                    'short_day_name' => $menu->short_day_name,
                    'formatted_date' => $menu->formatted_date,
                ];
            });

        // Get upcoming events (from Posts) - show all events in portal (both public and private)
        $upcomingEvents = Post::where('type', 'event')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('event_start_date', '>=', now())
            ->orderBy('event_start_date', 'asc')
            ->take(5)
            ->get();

        // Get latest announcements (from Posts) - public only for parents
        $recentAnnouncements = Post::where('type', 'news')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->publicAudience()
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        // Get grade-specific news for parents (from teachers) – one message set per grade, based on ALL linked students' grades
        // Admins/Super Admins get ALL grade news for preview purposes
        $gradeNews = collect();
        $linkedStudentGradeNames = [];
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $gradeNews = Post::with(['targetGrade', 'author'])
                ->where('type', 'news')
                ->where('audience', 'grade')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->take(10)
                ->get();

            $children = $user->children()->with('grade:id,name')->get();
            $linkedStudentGradeNames = $children->map(fn ($s) => $s->grade?->name)->filter()->unique()->values()->toArray();
        } elseif ($user->canViewAsParent()) {
            $children = $user->children()->with('grade:id,name')->get();
            $childrenGradeIds = $children->pluck('grade_id')->unique()->filter()->toArray();
            $linkedStudentGradeNames = $children->map(fn ($s) => $s->grade?->name)->filter()->unique()->values()->toArray();

            if (!empty($childrenGradeIds)) {
                $gradeNews = Post::with(['targetGrade', 'author'])
                    ->where('type', 'news')
                    ->where('audience', 'grade')
                    ->whereIn('target_grade_id', $childrenGradeIds)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc')
                    ->take(10)
                    ->get();
            }
        }

        // Get teacher's grades for quick action (admins get all grades for preview)
        $teacherGrades = collect();
        if ($user->isTeacher()) {
            $teacherGrades = $user->grades()->get(['grades.id', 'grades.name']);
        } elseif ($user->isAdmin() || $user->isSuperAdmin()) {
            // Admins get all grades for preview purposes
            $teacherGrades = \App\Models\Grade::orderBy('name')->get(['id', 'name']);
        }

        // Get staff announcements (for teachers and admins)
        $teacherAnnouncements = collect();
        if ($user->isTeacher() || $user->isAdmin() || $user->isSuperAdmin()) {
            // Get the teacher's assigned grade (if any) from their grades relationship
            $teacherGradeIds = [];
            if ($user->isTeacher()) {
                $teacherGradeIds = $user->grades()->pluck('grades.id')->unique()->toArray();
            }

            $teacherAnnouncements = Post::with(['targetGrade', 'targetTeacher'])
                ->where('type', 'news')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($query) use ($user, $teacherGradeIds) {
                    // All staff announcements
                    $query->where('audience', 'teachers_only');
                    
                    // Grade-specific announcements (for admins: all, for teachers: only their grades)
                    if ($user->isAdmin() || $user->isSuperAdmin()) {
                        $query->orWhere('audience', 'grade_teachers');
                    } elseif (!empty($teacherGradeIds)) {
                        $query->orWhere(function ($q) use ($teacherGradeIds) {
                            $q->where('audience', 'grade_teachers')
                                ->whereIn('target_grade_id', $teacherGradeIds);
                        });
                    }
                    
                    // Teacher-specific announcements (for admins: all, for teachers: only theirs)
                    if ($user->isAdmin() || $user->isSuperAdmin()) {
                        $query->orWhere('audience', 'specific_teacher');
                    } else {
                        $query->orWhere(function ($q) use ($user) {
                            $q->where('audience', 'specific_teacher')
                                ->where('target_teacher_id', $user->id);
                        });
                    }
                })
                ->orderBy('published_at', 'desc')
                ->take(10)
                ->get();
        }

        return Inertia::render('Portal/Dashboard', [
            'user' => $user,
            'thisWeekLunchMenus' => $thisWeekLunchMenus,
            'upcomingEvents' => $upcomingEvents,
            'recentAnnouncements' => $recentAnnouncements,
            'teacherAnnouncements' => $teacherAnnouncements,
            'gradeNews' => $gradeNews,
            'linkedStudentGradeNames' => $linkedStudentGradeNames ?? [],
            'teacherGrades' => $teacherGrades,
            'studentCount' => $studentCount,
            'staffCount' => $staffCount,
            'upcomingEventsCount' => $upcomingEventsCount,
        ]);
    }
}
