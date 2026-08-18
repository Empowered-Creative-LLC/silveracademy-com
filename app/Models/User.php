<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\PortalPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * User role constants.
     */
    public const ROLE_USER = 'user';
    public const ROLE_PARENT = 'parent';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Extra values included when the user is serialized to the frontend.
     *
     * @var list<string>
     */
    protected $appends = [
        'has_linked_students',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the admin who approved this user.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if user is approved.
     */
    public function isApproved(): bool
    {
        // Default to true if column doesn't exist yet (migration not run)
        if (!array_key_exists('is_approved', $this->attributes) && $this->is_approved === null) {
            return true;
        }
        return (bool) ($this->is_approved ?? true);
    }

    /**
     * Approve this user and generate a password.
     *
     * @param User $approver The admin user approving
     * @return string The generated plain text password
     */
    public function approve(User $approver): string
    {
        $password = PortalPassword::generate();

        $this->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'password' => $password,
        ]);

        return $password;
    }

    /**
     * Reject this user (delete the account).
     */
    public function reject(): void
    {
        $this->delete();
    }

    /**
     * Scope to only pending (unapproved) users.
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope to only approved users.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Get the grade levels this teacher is assigned to.
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'grade_teacher', 'teacher_id', 'grade_id')
            ->withTimestamps();
    }

    /**
     * Get the classrooms this teacher is assigned to.
     * @deprecated Use grades() instead - classrooms are being removed
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * Get the children of this parent user.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withTimestamps();
    }

    /**
     * Get posts created by this user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get announcements created by this user.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get calendar events created by this user.
     */
    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is an admin (includes super admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    /**
     * Check if user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    /**
     * Check if user is a parent.
     */
    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    /**
     * Staff, admins, and super admins.
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_TEACHER, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    /**
     * Whether this account has at least one linked student.
     */
    public function hasLinkedStudents(): bool
    {
        if ($this->relationLoaded('children')) {
            return $this->children->isNotEmpty();
        }

        return $this->children()->exists();
    }

    public function getHasLinkedStudentsAttribute(): bool
    {
        return $this->hasLinkedStudents();
    }

    /**
     * Parents, or staff who also have children linked (dual-role testers).
     */
    public function canViewAsParent(): bool
    {
        return $this->isParent() || $this->hasLinkedStudents();
    }

    /**
     * Check if user can manage posts (news & events).
     */
    public function canManagePosts(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage a specific grade.
     */
    public function canManageGrade(Grade $grade): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isTeacher()) {
            return $this->grades->contains($grade);
        }

        return false;
    }

    /**
     * Check if user can manage a specific classroom.
     * @deprecated Use canManageGrade() instead - classrooms are being removed
     */
    public function canManageClassroom(Classroom $classroom): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isTeacher()) {
            return $this->classrooms->contains($classroom);
        }

        return false;
    }

    /**
     * Send the password reset notification.
     * Uses SendGrid when configured (same as other app emails) so reset emails are sent on production.
     */
    public function sendPasswordResetNotification($token): void
    {
        $apiKey = config('services.sendgrid.api_key');
        if ($apiKey) {
            $this->notify(new \App\Notifications\ResetPassword($token));
        } else {
            $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
        }
    }
}
