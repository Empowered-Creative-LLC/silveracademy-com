<?php

namespace App\Http\Controllers\Portal\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentContactEmail;
use App\Models\User;
use App\Services\ParentCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    /**
     * Display a listing of all grades with student counts.
     */
    public function index(): Response
    {
        // Auto-seed grades if table is empty
        if (Grade::count() === 0) {
            $this->seedGrades();
        }

        $grades = Grade::withCount('students')
            ->with(['teachers' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }])
            ->orderBy('sort_order')
            ->get();

        // Count total students
        $totalStudents = Student::count();

        return Inertia::render('Portal/Admin/Grades/Index', [
            'grades' => $grades,
            'totalStudents' => $totalStudents,
        ]);
    }

    /**
     * Seed default grade levels.
     */
    protected function seedGrades(): void
    {
        $grades = [
            ['name' => 'Ganeinu (Preschool)', 'sort_order' => 1],
            ['name' => 'Early Learning Program', 'sort_order' => 2],
            ['name' => 'Kindergarten', 'sort_order' => 3],
            ['name' => '1st Grade', 'sort_order' => 4],
            ['name' => '2nd Grade', 'sort_order' => 5],
            ['name' => '3rd Grade', 'sort_order' => 6],
            ['name' => '4th Grade', 'sort_order' => 7],
            ['name' => '5th Grade', 'sort_order' => 8],
            ['name' => '6th Grade', 'sort_order' => 9],
            ['name' => '7th Grade', 'sort_order' => 10],
            ['name' => '8th Grade', 'sort_order' => 11],
        ];

        foreach ($grades as $grade) {
            Grade::updateOrCreate(
                ['name' => $grade['name']],
                $grade
            );
        }
    }

    /**
     * Display the detail view for a specific grade.
     */
    public function show(Grade $grade): Response
    {
        $grade->load(['teachers' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email');
        }]);
        
        // Get all staff members for potential assignment
        $allStaff = User::whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        // Get students in this grade with parent code status and contact emails
        $students = Student::where('grade_id', $grade->id)
            ->with(['parents:id,name,email', 'contactEmails', 'accessCodes' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get()
            ->map(function (Student $student) {
                $code = $student->activeAccessCode();
                $contactEmails = $student->contactEmails->pluck('email')->map(fn ($e) => trim($e))->filter()->values()->toArray();
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'grade_id' => $student->grade_id,
                    'parents' => $student->parents,
                    'contact_emails' => $contactEmails,
                    'can_send_code' => count($contactEmails) > 0 || $student->parents->isNotEmpty(),
                    'code_status' => $code ? $code->status : null,
                    'code_last4' => $code?->code_last4,
                    'code_link_count' => $student->currentLinkCount(),
                    'max_links' => $code?->max_links ?? \App\Services\ParentCodeService::DEFAULT_MAX_LINKS,
                ];
            });

        return Inertia::render('Portal/Admin/Grades/Show', [
            'grade' => $grade,
            'allStaff' => $allStaff,
            'students' => $students,
        ]);
    }

    /**
     * Update the teachers assigned to a grade.
     */
    public function updateTeachers(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:users,id',
        ]);

        $grade->teachers()->sync($validated['teacher_ids'] ?? []);

        return redirect()->back()
            ->with('success', 'Grade teachers updated successfully.');
    }

    /**
     * Store a new student in this grade.
     */
    public function storeStudent(Request $request, Grade $grade)
    {
        Log::info('Add student: request received', [
            'grade_id' => $grade->id,
            'name' => $request->input('name'),
            'user_id' => $request->user()?->id,
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'parent_email_1' => 'nullable|email',
                'parent_email_2' => 'nullable|email',
                'parent_email_3' => 'nullable|email',
                'parent_email_4' => 'nullable|email',
            ]);

            $result = DB::transaction(function () use ($validated, $grade) {
                $student = Student::create([
                    'name' => $validated['name'],
                    'grade_id' => $grade->id,
                    'status' => Student::STATUS_ACTIVE,
                ]);

                $codeResult = ParentCodeService::createCodeForStudent($student, ParentCodeService::DEFAULT_MAX_LINKS, false);
                $this->syncStudentContactEmails($student, $validated);

                return ['student' => $student, 'plain_code' => $codeResult['plain_code']];
            });

            return redirect()->route('admin.grades.show', $grade)
                ->with('success', 'Student added successfully.')
                ->with('new_student_code_plain', $result['plain_code'])
                ->with('new_student_name', $result['student']->name);
        } catch (\Throwable $e) {
            Log::error('Add student failed', [
                'grade_id' => $grade->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput($request->only('name', 'parent_email_1', 'parent_email_2', 'parent_email_3', 'parent_email_4'))
                ->withErrors(['name' => 'Failed to add student. Please try again. Check server logs for details.']);
        }
    }

    /**
     * Sync up to 4 contact emails for a student from validated request data.
     */
    protected function syncStudentContactEmails(Student $student, array $validated): void
    {
        $emails = [];
        for ($i = 1; $i <= 4; $i++) {
            $key = "parent_email_{$i}";
            $value = isset($validated[$key]) ? trim($validated[$key]) : '';
            if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $normalized = strtolower($value);
                if (!in_array($normalized, $emails, true)) {
                    $emails[] = $normalized;
                }
            }
        }
        $student->contactEmails()->delete();
        foreach (array_slice($emails, 0, StudentContactEmail::MAX_PER_STUDENT) as $email) {
            $student->contactEmails()->create(['email' => $email]);
        }
    }

    /**
     * Update a student.
     */
    public function updateStudent(Request $request, Grade $grade, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_id' => 'nullable|exists:grades,id',
            'parent_email_1' => 'nullable|email',
            'parent_email_2' => 'nullable|email',
            'parent_email_3' => 'nullable|email',
            'parent_email_4' => 'nullable|email',
        ]);

        $student->update([
            'name' => $validated['name'],
            'grade_id' => $validated['grade_id'] ?? $grade->id,
        ]);
        $this->syncStudentContactEmails($student, $validated);

        return redirect()->back()
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Delete a student.
     */
    public function destroyStudent(Grade $grade, Student $student)
    {
        // Detach from all parents first
        $student->parents()->detach();
        
        $student->delete();

        return redirect()->back()
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Move students to a different grade.
     */
    public function moveStudents(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'target_grade_id' => 'required|exists:grades,id',
        ]);

        Student::whereIn('id', $validated['student_ids'])
            ->update(['grade_id' => $validated['target_grade_id']]);

        return redirect()->back()
            ->with('success', count($validated['student_ids']) . ' student(s) moved successfully.');
    }
}
