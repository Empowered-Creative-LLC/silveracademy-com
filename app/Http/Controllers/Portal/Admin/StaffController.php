<?php

namespace App\Http\Controllers\Portal\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\User;
use App\Notifications\AccountApproved;
use App\Support\PortalPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Display a listing of all staff members.
     */
    public function index(): Response
    {
        $staff = User::whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->with('grades')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => $this->getRoleLabel($user->role),
                    'grades' => $user->grades->map(fn($g) => [
                        'id' => $g->id,
                        'name' => $g->name,
                    ]),
                    'has_credentials' => $user->password !== null,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ];
            });

        // Get counts
        $counts = [
            'total' => $staff->count(),
            'teachers' => $staff->where('role', User::ROLE_TEACHER)->count(),
            'admins' => $staff->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->count(),
            'pending_credentials' => $staff->where('has_credentials', false)->count(),
        ];

        return Inertia::render('Portal/Admin/Staff/Index', [
            'staff' => $staff,
            'counts' => $counts,
        ]);
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(): Response
    {
        $grades = Grade::orderBy('sort_order')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
            ]);

        return Inertia::render('Portal/Admin/Staff/Create', [
            'grades' => $grades,
            'staffEmailDomain' => config('app.staff_email_domain'),
        ]);
    }

    /**
     * Store a newly created staff member.
     * Automatically generates password and sends welcome email.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:teacher,admin',
            'grade_ids' => 'nullable|array',
            'grade_ids.*' => 'exists:grades,id',
        ]);

        $validated['email'] = strtolower($validated['email']);

        // Generate password
        $password = PortalPassword::generate();

        // Build user data (plain password so User model's 'hashed' cast hashes it once)
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $password,
            'role' => $validated['role'],
        ];

        // Only set is_approved if the column exists
        if (Schema::hasColumn('users', 'is_approved')) {
            $userData['is_approved'] = true;
        }

        $user = User::create($userData);

        // Assign grades if provided
        if (!empty($validated['grade_ids'])) {
            $user->grades()->sync($validated['grade_ids']);
        }

        // Send welcome email with credentials (don't block account creation if mail fails).
        try {
            $user->notify(new AccountApproved($password));
            $successMessage = "Staff member created successfully. Welcome email sent to {$user->email}.";
        } catch (\Throwable $e) {
            Log::error('Staff welcome email failed after create', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);
            $successMessage = "Staff member created successfully. Welcome email could not be sent — use Send Email from Staff Management to deliver credentials.";
        }

        return redirect()->route('admin.staff.index')
            ->with('success', $successMessage)
            ->with('credentials', [[
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ]]);
    }

    /**
     * Show the form for editing a staff member.
     */
    public function edit(User $staff): Response
    {
        // Ensure we're editing a staff member
        if (!in_array($staff->role, [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            abort(404);
        }

        $grades = Grade::orderBy('sort_order')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
            ]);

        $staff->load('grades');

        return Inertia::render('Portal/Admin/Staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
                'grade_ids' => $staff->grades->pluck('id')->toArray(),
                'has_credentials' => $staff->password !== null,
                'email_verified_at' => $staff->email_verified_at,
            ],
            'grades' => $grades,
        ]);
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff)
    {
        // Ensure we're editing a staff member
        if (!in_array($staff->role, [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            abort(404);
        }

        // Prevent super admins from being demoted by non-super-admins
        if ($staff->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'You cannot modify a super admin account.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:teacher,admin',
            'grade_ids' => 'nullable|array',
            'grade_ids.*' => 'exists:grades,id',
        ]);

        $validated['email'] = strtolower($validated['email']);

        // Don't allow changing super admin role
        if ($staff->isSuperAdmin()) {
            unset($validated['role']);
        }

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? $staff->role,
        ]);

        // Update password if provided (plain so User model's 'hashed' cast hashes it once)
        if (!empty($validated['password'])) {
            $staff->update(['password' => $validated['password']]);
        }

        // Update grade assignments
        $staff->grades()->sync($validated['grade_ids'] ?? []);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        // Ensure we're deleting a staff member
        if (!in_array($staff->role, [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            abort(404);
        }

        // Prevent deleting super admins
        if ($staff->isSuperAdmin()) {
            return back()->with('error', 'Super admin accounts cannot be deleted.');
        }

        // Prevent self-deletion
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Remove grade assignments
        $staff->grades()->detach();

        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    /**
     * Toggle a staff member's role between teacher and admin.
     */
    public function toggleRole(User $staff)
    {
        // Prevent modifying super admins
        if ($staff->isSuperAdmin()) {
            return back()->with('error', 'Super admin role cannot be changed.');
        }

        $newRole = $staff->role === User::ROLE_TEACHER ? User::ROLE_ADMIN : User::ROLE_TEACHER;
        $staff->update(['role' => $newRole]);

        return back()->with('success', 'Role updated to ' . $this->getRoleLabel($newRole) . '.');
    }

    /**
     * Send welcome email to a single staff member.
     * Generates password and sends email with credentials.
     */
    public function sendWelcomeEmail(User $staff)
    {
        // Ensure we're working with a staff member
        if (!in_array($staff->role, [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            abort(404);
        }

        // Generate password (plain so User model's 'hashed' cast hashes it once)
        $password = PortalPassword::generate();
        $staff->update(['password' => $password]);

        // Send welcome email with credentials
        $staff->notify(new AccountApproved($password));

        return back()
            ->with('success', "Welcome email sent to {$staff->name}.")
            ->with('credentials', [[
                'name' => $staff->name,
                'email' => $staff->email,
                'password' => $password,
            ]]);
    }

    /**
     * Send welcome emails to multiple staff members.
     * Generates passwords and sends emails with credentials.
     */
    public function sendBulkWelcomeEmails(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->get();

        $credentials = [];
        $sent = 0;

        foreach ($users as $user) {
            // Generate password (plain so User model's 'hashed' cast hashes it once)
            $password = PortalPassword::generate();
            $user->update(['password' => $password]);

            // Send welcome email
            $user->notify(new AccountApproved($password));

            $credentials[] = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ];
            $sent++;
        }

        return back()
            ->with('success', "Welcome emails sent to {$sent} staff member(s).")
            ->with('credentials', $credentials);
    }

    /**
     * Send welcome emails to ALL staff members without credentials.
     */
    public function sendAllPendingWelcomeEmails()
    {
        $users = User::whereIn('role', [User::ROLE_TEACHER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->whereNull('password')
            ->get();

        if ($users->isEmpty()) {
            return back()->with('info', 'No staff members pending credentials.');
        }

        $credentials = [];

        foreach ($users as $user) {
            // Generate password (plain so User model's 'hashed' cast hashes it once)
            $password = PortalPassword::generate();
            $user->update(['password' => $password]);

            // Send welcome email
            $user->notify(new AccountApproved($password));

            $credentials[] = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ];
        }

        return back()
            ->with('success', "Welcome emails sent to {$users->count()} staff member(s).")
            ->with('credentials', $credentials);
    }

    /**
     * Get human-readable role label.
     */
    private function getRoleLabel(string $role): string
    {
        return match ($role) {
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_TEACHER => 'Staff',
            default => ucfirst($role),
        };
    }
}
